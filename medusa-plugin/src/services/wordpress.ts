import axios from "axios";
import cryptoRandomString from "crypto-random-string";
import { EntityManager, Not, IsNull } from "typeorm";
import crypto from "crypto";
import { AwilixContainer } from "awilix";
import { validator } from "@medusajs/medusa/dist/utils/validator";
import {
  AdminPostAuthReq,
  ProductCollection,
  Region,
  TransactionBaseService,
  AuthService,
  EventBusService,
  Product,
  UserService,
} from "@medusajs/medusa";
import ProductRepository from "@medusajs/medusa/dist/repositories/product";
import ProductVariantRepository from "@medusajs/medusa/dist/repositories/product-variant";
import ProductCollectionRepository from "@medusajs/medusa/dist/repositories/product-collection";
import RegionRepository from "@medusajs/medusa/dist/repositories/region";
import { PricedVariant } from "@medusajs/medusa/dist/types/pricing";
import { Request, Response } from "express";

import { WordpressRepository } from "../repositories/wordpress";
import { Wordpress } from "../models/wordpress";

function isBackoffType(value: unknown): value is "fixed" | "exponential" {
  return typeof value === "string" && ["fixed", "exponential"].includes(value);
}

function isValidBackoffOption(value: unknown): value is {
  type: "fixed" | "exponential";
  delay: number;
} {
  return Boolean(
    typeof value === "object" &&
      value &&
      "type" in value &&
      isBackoffType(value.type) &&
      "delay" in value &&
      typeof value.delay === "number",
  );
}

class WordpressService extends TransactionBaseService {
  private options_: {
    sync_options: {
      delay?: number;
      attempts: number;
      backoff?: {
        type: "fixed" | "exponential";
        delay: number;
      };
    };
  } = {
    sync_options: {
      attempts: 3,
      backoff: {
        type: "exponential",
        delay: 2000,
      },
    },
  };

  private authService_: AuthService;
  private productRepository_: typeof ProductRepository;
  private productVariantRepository_: typeof ProductVariantRepository;
  private productCollectionRepository_: typeof ProductCollectionRepository;
  private regionRepository_: typeof RegionRepository;
  public wordpressRepository_: WordpressRepository;

  constructor(
    container: AwilixContainer<{
      authService: AuthService;
      productRepository: typeof ProductRepository;
      productVariantRepository: typeof ProductVariantRepository;
      productCollectionRepository: typeof ProductCollectionRepository;
      regionRepository: typeof RegionRepository;
      wordpressRepository: WordpressRepository;
    }>["cradle"] & { manager: EntityManager },
    options: unknown,
  ) {
    super(container);

    if (
      typeof options === "object" &&
      options &&
      "sync_options" in options &&
      typeof options.sync_options === "object" &&
      options.sync_options
    ) {
      if (
        "delay" in options.sync_options &&
        typeof options.sync_options.delay === "number"
      ) {
        this.options_.sync_options.delay = options.sync_options.delay;
      }

      if (
        "attempts" in options.sync_options &&
        typeof options.sync_options.attempts === "number"
      ) {
        this.options_.sync_options.attempts = options.sync_options.attempts;
      }

      if (
        "backoff" in options.sync_options &&
        isValidBackoffOption(options.sync_options.backoff)
      ) {
        this.options_.sync_options.backoff = options.sync_options.backoff;
      }
    }

    this.authService_ = container.authService;
    this.productRepository_ = this.manager_.withRepository(
      container.productRepository,
    );
    this.productVariantRepository_ = this.manager_.withRepository(
      container.productVariantRepository,
    );
    this.productCollectionRepository_ = this.manager_.withRepository(
      container.productCollectionRepository,
    );
    this.regionRepository_ = this.manager_.withRepository(
      container.regionRepository,
    );
    this.wordpressRepository_ = this.manager_.withRepository(
      container.wordpressRepository,
    );
  }

  async connect(req: Request, res: Response) {
    try {
      // Validate data in request body with AdminPostAuthReq rules
      const validated = await validator(AdminPostAuthReq, req.body);

      // If valid data exist after validation try to authenticate with the given data
      if (validated.email && validated.password) {
        const auth = await this.authService_.authenticate(
          validated.email,
          validated.password,
        );

        // If authentication fails throw error with message
        if (!auth.success || !auth.user) {
          throw Error(auth.error || "Authentication failed.");
        }

        // If authentication is success return user api_token
        const userService = req.scope.resolve("userService") as UserService;
        let user = await userService.retrieve(auth.user.id);

        // Make sure that user has api_token
        if (!user.api_token) {
          user.api_token = cryptoRandomString(36);

          user = await userService.update(user.id, {
            api_token: user.api_token,
          });
        }

        // Get or Create wordpress entity
        const wordpressEntity = await this.getWordpressEntity(req, true);

        if (!wordpressEntity) {
          return res.status(401).json({
            message: "WordPress entity does not exist.",
          });
        }

        return res.status(200).json({
          user: user,
          wordpress: wordpressEntity,
        });
      }
    } catch (err) {
      return res.status(401).json({
        message:
          typeof err === "object" && err && "message" in err
            ? err.message
            : "Authentication failed.",
      });
    }
  }

  async disconnect(req: Request, res: Response) {
    // Delete wordpress entity if exists
    const deletedWordPressEntity = await this.deleteWordpressEntity(req);

    if (!deletedWordPressEntity) {
      return res.status(401).json({
        message: "Entity does not exist.",
      });
    }

    // Destroy user session
    try {
      await new Promise<void>((resolve, reject) => {
        req.session.destroy((err) => {
          if (err) {
            reject(err);
          }
          resolve();
        });
      });
    } catch (err) {
      return res.status(401).json({
        message: "Error disconnecting user.",
      });
    }

    return res.status(200).end();
  }

  async sync(req: Request, res: Response, sendRes: Boolean = true) {
    const wordpressEntity = await this.getWordpressEntity(req);

    if (!wordpressEntity) {
      return res.status(401).json({
        code: 401,
        message: "Entity does not exist.",
      });
    }

    const importThumbnails = Boolean(req.body.import_thumbnails);
    const syncTimestamp = Number(req.body.sync_timestamp);
    let thumbnailsCount = 0;

    if (importThumbnails) {
      thumbnailsCount = await this.productRepository_.count({
        where: {
          thumbnail: Not(IsNull()),
        },
      });
    }

    const productsTotal = await this.productRepository_.count({
      withDeleted: false,
    });
    const productVariantsTotal = await this.productVariantRepository_.count({
      withDeleted: false,
    });
    const collectionsTotal = await this.productCollectionRepository_.count({
      withDeleted: false,
    });
    const regionsTotal = await this.regionRepository_.count({
      withDeleted: false,
    });

    const eventBusService = req.scope.resolve(
      "eventBusService",
    ) as EventBusService;

    ["product", "product_variant", "product_collection", "region"].forEach(
      (model) => {
        eventBusService.emit(
          "wordpress.bulk_sync",
          { wordpressId: wordpressEntity.id, model, syncTimestamp },
          this.options_.sync_options,
        );
      },
    );

    const totals: Record<string, number> = {
      products: productsTotal,
      product_variants: productVariantsTotal,
      collections: collectionsTotal,
      regions: regionsTotal,
    };

    if (importThumbnails) {
      totals["thumbnails"] = thumbnailsCount;
    }

    if (sendRes) {
      return res.status(200).json({
        totals,
      });
    }
  }

  async webhook(
    endpoint: "product",
    entity: Product,
    feed: Wordpress,
    syncTimestamp?: number,
  ): Promise<void>;
  async webhook(
    endpoint: "product-variant",
    entity: PricedVariant,
    feed: Wordpress,
    syncTimestamp?: number,
  ): Promise<void>;
  async webhook(
    endpoint: "product-collection",
    entity: ProductCollection,
    feed: Wordpress,
    syncTimestamp?: number,
  ): Promise<void>;
  async webhook(
    endpoint: "region",
    entity: Region,
    feed: Wordpress,
    syncTimestamp?: number,
  ): Promise<void>;
  async webhook(
    endpoint: "product" | "product-variant" | "product-collection" | "region",
    entity: Product | PricedVariant | ProductCollection | Region,
    feed: Wordpress,
    syncTimestamp?: number,
  ): Promise<void> {
    const timestamp = String(Date.now());
    const token = cryptoRandomString(36);
    const signature = crypto
      .createHmac("sha256", feed.secret)
      .update(timestamp + token)
      .digest("hex");

    await axios
      .create({
        baseURL: `${feed.host}/wp-json/wp/v2/medusa`,
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
      })
      .post(endpoint, {
        signature: {
          timestamp: timestamp,
          token: token,
          signature: signature,
        },
        data: entity,
        sync_timestamp: syncTimestamp,
      });
  }

  async getWordpressEntity(req: Request, createIfNotExists: Boolean = false) {
    const entity_host = req.get("origin");
    const existingWordpressEntity = await this.wordpressRepository_.findOne({
      where: {
        host: entity_host,
      },
      withDeleted: false,
    });

    if (existingWordpressEntity) {
      return existingWordpressEntity;
    }

    if (!createIfNotExists) {
      return null;
    }

    const entity_secret = cryptoRandomString(18);
    const newWordpressEntity = await this.wordpressRepository_.save(
      this.wordpressRepository_.create({
        host: entity_host,
        secret: entity_secret,
      }),
    );

    return newWordpressEntity;
  }

  async deleteWordpressEntity(req: Request) {
    const entity_host = req.get("origin");

    const deletedWordpressEntity = await this.wordpressRepository_.softDelete({
      host: entity_host,
    });

    return deletedWordpressEntity;
  }
}

export default WordpressService;
