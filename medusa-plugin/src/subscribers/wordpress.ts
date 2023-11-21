import {
  EventBusService,
  PricingService,
  Product,
  ProductCollection,
  ProductCollectionService,
  ProductService,
  ProductVariant,
  ProductVariantService,
  Region,
  RegionService,
} from "@medusajs/medusa";
import { AwilixContainer } from "awilix";
import { MoreThan } from "typeorm";
import ProductRepository from "@medusajs/medusa/dist/repositories/product";
import ProductVariantRepository from "@medusajs/medusa/dist/repositories/product-variant";
import ProductCollectionRepository from "@medusajs/medusa/dist/repositories/product-collection";
import RegionRepository from "@medusajs/medusa/dist/repositories/region";

import { Wordpress } from "../models/wordpress";
import WordpressService from "../services/wordpress";

class WordpressSubscriber {
  protected readonly eventBusService_: EventBusService;
  protected readonly wordpressService_: WordpressService;
  protected readonly productService_: ProductService;
  protected readonly productVariantService_: ProductVariantService;
  protected readonly productCollectionService_: ProductCollectionService;
  protected readonly regionService_: RegionService;
  protected readonly pricingService_: PricingService;
  protected readonly productRepository_: typeof ProductRepository;
  protected readonly productVariantRepository_: typeof ProductVariantRepository;
  protected readonly productCollectionRepository_: typeof ProductCollectionRepository;
  protected readonly regionRepository_: typeof RegionRepository;

  protected readonly productRelations: string[] = ["images", "tags", "type"];
  protected readonly productVariantsRelations: string[] = ["options"]; //"prices" not supported, due to performance optimization
  protected readonly regionRelations: string[] = ["countries"];

  constructor(
    container: AwilixContainer<{
      productService: ProductService;
      wordpressService: WordpressService;
      eventBusService: EventBusService;
      productVariantService: ProductVariantService;
      productCollectionService: ProductCollectionService;
      regionService: RegionService;
      pricingService: PricingService;
      productRepository: typeof ProductRepository;
      productVariantRepository: typeof ProductVariantRepository;
      productCollectionRepository: typeof ProductCollectionRepository;
      regionRepository: typeof RegionRepository;
    }>["cradle"],
  ) {
    this.eventBusService_ = container.eventBusService;
    this.wordpressService_ = container.wordpressService;
    this.productService_ = container.productService;
    this.productVariantService_ = container.productVariantService;
    this.productCollectionService_ = container.productCollectionService;
    this.regionService_ = container.regionService;
    this.pricingService_ = container.pricingService;

    this.productRepository_ = container.productRepository;
    this.productVariantRepository_ = container.productVariantRepository;
    this.productCollectionRepository_ = container.productCollectionRepository;
    this.regionRepository_ = container.regionRepository;

    // Bulk sync
    this.eventBusService_.subscribe(
      "wordpress.bulk_sync",
      // @ts-ignore
      this.bulkSyncHandler,
      { subscriberId: "wordpress.sync.bulkSyncSubscriber" },
    );

    // Product event listeners
    this.eventBusService_.subscribe(
      "product.created",
      // @ts-ignore
      this.productEventHandler,
      { subscriberId: "wordpress.sync.productCreatedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "product.updated",
      // @ts-ignore
      this.productEventHandler,
      { subscriberId: "wordpress.sync.productUpdatedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "product.deleted",
      // @ts-ignore
      this.productEventHandler,
      { subscriberId: "wordpress.sync.productDeletedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "wordpress.product.sync",
      // @ts-ignore
      this.productForcedSyncHandler,
      { subscriberId: "wordpress.sync.productSyncSubscriber" },
    );

    // Product variant event listeners
    this.eventBusService_.subscribe(
      "product-variant.created",
      // @ts-ignore
      this.productVariantEventHandler,
      { subscriberId: "wordpress.sync.productVariantCreatedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "product-variant.updated",
      // @ts-ignore
      this.productVariantEventHandler,
      { subscriberId: "wordpress.sync.productVariantUpdatedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "product-variant.deleted",
      // @ts-ignore
      this.productVariantEventHandler,
      { subscriberId: "wordpress.sync.productVariantDeletedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "wordpress.product_variant.sync",
      // @ts-ignore
      this.productVariantForcedSyncHandler,
      { subscriberId: "wordpress.sync.productVariantSyncSubscriber" },
    );

    // Product collection event listeners
    this.eventBusService_.subscribe(
      "product-collection.created",
      // @ts-ignore
      this.productCollectionEventHandler,
      { subscriberId: "wordpress.sync.productCollectionCreatedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "product-collection.updated",
      // @ts-ignore
      this.productCollectionEventHandler,
      { subscriberId: "wordpress.sync.productCollectionUpdatedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "product-collection.deleted",
      // @ts-ignore
      this.productCollectionEventHandler,
      { subscriberId: "wordpress.sync.productCollectionDeletedSubscriber" },
    );
    this.eventBusService_.subscribe(
      "product-collection.products_added",
      // @ts-ignore
      this.productCollectionProductsRelationSyncEventHandler,
      {
        subscriberId: "wordpress.sync.productCollectionProductsAddedSubscriber",
      },
    );
    this.eventBusService_.subscribe(
      "product-collection.products_removed",
      // @ts-ignore
      this.productCollectionProductsRelationSyncEventHandler,
      {
        subscriberId:
          "wordpress.sync.productCollectionProductsRemovedSubscriber",
      },
    );
    this.eventBusService_.subscribe(
      "wordpress.product_collection.sync",
      // @ts-ignore
      this.productCollectionForcedSyncHandler,
      { subscriberId: "wordpress.sync.productCollectionSyncSubscriber" },
    );

    // Region event listeners
    this.eventBusService_.subscribe(
      "region.created",
      // @ts-ignore
      this.regionEventHandler,
      {
        subscriberId: "wordpress.sync.regionCreatedSubscriber",
      },
    );
    this.eventBusService_.subscribe(
      "region.updated",
      // @ts-ignore
      this.regionEventHandler,
      {
        subscriberId: "wordpress.sync.regionUpdatedSubscriber",
      },
    );
    this.eventBusService_.subscribe(
      "region.deleted",
      // @ts-ignore
      this.regionEventHandler,
      {
        subscriberId: "wordpress.sync.regionDeletedSubscriber",
      },
    );
    this.eventBusService_.subscribe(
      "wordpress.region.sync",
      // @ts-ignore
      this.regionForcedSyncHandler,
      { subscriberId: "wordpress.sync.regionSyncSubscriber" },
    );
  }

  bulkSyncHandler = async (data: unknown) => {
    if (
      typeof data !== "object" ||
      !data ||
      !("wordpressId" in data) ||
      typeof data.wordpressId !== "string" ||
      !data.wordpressId ||
      !("model" in data) ||
      typeof data.model !== "string" ||
      (data.model !== "product" &&
        data.model !== "product_variant" &&
        data.model !== "product_collection" &&
        data.model !== "region") ||
      !("syncTimestamp" in data) ||
      typeof data.syncTimestamp !== "number" ||
      !data.syncTimestamp
    ) {
      return;
    }

    const wordpress = await this.wordpressService_.wordpressRepository_.findOne(
      {
        where: {
          id: data.wordpressId,
        },
      },
    );

    if (!wordpress) {
      return;
    }

    const after =
      "after" in data && typeof data.after === "string"
        ? MoreThan(data.after)
        : undefined;

    if (data.model === "product") {
      const products = await this.productRepository_.find({
        select: {
          id: true,
        },
        where: {
          id: after,
        },
        order: {
          id: "ASC",
        },
        take: 100,
        withDeleted: false,
      });

      if (products.length) {
        products.forEach((product) => {
          this.eventBusService_.emit("wordpress.product.sync", {
            productId: product.id,
            wordpressId: wordpress.id,
            syncTimestamp: data.syncTimestamp,
          });
        });

        if (products.length === 100) {
          this.eventBusService_.emit("wordpress.bulk_sync", {
            wordpressId: wordpress.id,
            model: "product",
            syncTimestamp: data.syncTimestamp,
            after: products[products.length - 1].id,
          });
        }
      }

      return;
    }

    if (data.model === "product_variant") {
      const productVariants = await this.productVariantRepository_.find({
        select: {
          id: true,
        },
        where: {
          id: after,
        },
        order: {
          id: "ASC",
        },
        take: 100,
        withDeleted: false,
      });

      if (productVariants.length) {
        productVariants.forEach((productVariant) => {
          this.eventBusService_.emit("wordpress.product_variant.sync", {
            productVariantId: productVariant.id,
            wordpressId: wordpress.id,
            syncTimestamp: data.syncTimestamp,
          });
        });

        if (productVariants.length === 100) {
          this.eventBusService_.emit("wordpress.bulk_sync", {
            wordpressId: wordpress.id,
            model: "product_variant",
            syncTimestamp: data.syncTimestamp,
            after: productVariants[productVariants.length - 1].id,
          });
        }
      }

      return;
    }

    if (data.model === "product_collection") {
      const productCollections = await this.productCollectionRepository_.find({
        select: {
          id: true,
        },
        where: {
          id: after,
        },
        order: {
          id: "ASC",
        },
        take: 100,
        withDeleted: false,
      });

      if (productCollections.length) {
        productCollections.forEach((productCollection) => {
          this.eventBusService_.emit("wordpress.product_collection.sync", {
            productCollectionId: productCollection.id,
            wordpressId: wordpress.id,
            syncTimestamp: data.syncTimestamp,
          });
        });

        if (productCollections.length === 100) {
          this.eventBusService_.emit("wordpress.bulk_sync", {
            wordpressId: wordpress.id,
            model: "product_collection",
            syncTimestamp: data.syncTimestamp,
            after: productCollections[productCollections.length - 1].id,
          });
        }
      }

      return;
    }

    if (data.model === "region") {
      const regions = await this.regionRepository_.find({
        select: {
          id: true,
        },
        where: {
          id: after,
        },
        order: {
          id: "ASC",
        },
        take: 100,
        withDeleted: false,
      });

      if (regions.length) {
        regions.forEach((region) => {
          this.eventBusService_.emit("wordpress.region.sync", {
            regionId: region.id,
            wordpressId: wordpress.id,
            syncTimestamp: data.syncTimestamp,
          });
        });

        if (regions.length === 100) {
          this.eventBusService_.emit("wordpress.bulk_sync", {
            wordpressId: wordpress.id,
            model: "region",
            syncTimestamp: data.syncTimestamp,
            after: regions[regions.length - 1].id,
          });
        }
      }

      return;
    }
  };

  syncProduct = async (
    productId: Product["id"],
    wordpressEntities: Wordpress[],
    syncTimestamp?: number,
  ) => {
    if (!wordpressEntities.length) {
      return;
    }

    const product = await this.productService_.retrieve(productId, {
      relations: this.productRelations,
    });

    await Promise.all(
      wordpressEntities.map((wordpress) =>
        this.wordpressService_.webhook(
          "product",
          product,
          wordpress,
          syncTimestamp,
        ),
      ),
    );
  };

  productEventHandler = async (product: unknown) => {
    if (
      typeof product === "object" &&
      product &&
      "id" in product &&
      typeof product.id === "string" &&
      product.id
    ) {
      const feeds = await this.wordpressService_.wordpressRepository_.find();

      this.syncProduct(product.id, feeds);
    }
  };

  productForcedSyncHandler = async (data: unknown) => {
    if (
      typeof data === "object" &&
      data &&
      "productId" in data &&
      typeof data.productId === "string" &&
      data.productId
    ) {
      if (
        "wordpressId" in data &&
        typeof data.wordpressId === "string" &&
        data.wordpressId
      ) {
        const wordpress =
          await this.wordpressService_.wordpressRepository_.findOne({
            where: {
              id: data.wordpressId,
            },
          });

        if (wordpress) {
          this.syncProduct(
            data.productId,
            [wordpress],
            "syncTimestamp" in data && typeof data.syncTimestamp === "number"
              ? data.syncTimestamp
              : undefined,
          );
        }
      } else {
        const feeds = await this.wordpressService_.wordpressRepository_.find();

        this.syncProduct(
          data.productId,
          feeds,
          "syncTimestamp" in data && typeof data.syncTimestamp === "number"
            ? data.syncTimestamp
            : undefined,
        );
      }
    }
  };

  syncProductVariant = async (
    productVariantId: ProductVariant["id"],
    wordpressEntities: Wordpress[],
    syncTimestamp?: number,
  ) => {
    if (!wordpressEntities.length) {
      return;
    }

    const productVariants = await this.productVariantService_.retrieve(
      productVariantId,
      { relations: this.productVariantsRelations },
    );

    const pricedProductVariants = await this.pricingService_.setVariantPrices(
      [productVariants],
      {},
    );

    if (!pricedProductVariants.length) {
      return;
    }

    await Promise.all(
      wordpressEntities.map((wordpress) =>
        this.wordpressService_.webhook(
          "product-variant",
          pricedProductVariants[0],
          wordpress,
          syncTimestamp,
        ),
      ),
    );
  };

  productVariantEventHandler = async (productVariant: unknown) => {
    if (
      typeof productVariant === "object" &&
      productVariant &&
      "id" in productVariant &&
      typeof productVariant.id === "string" &&
      productVariant.id
    ) {
      const feeds = await this.wordpressService_.wordpressRepository_.find();

      this.syncProductVariant(productVariant.id, feeds);
    }
  };

  productVariantForcedSyncHandler = async (data: unknown) => {
    if (
      typeof data === "object" &&
      data &&
      "productVariantId" in data &&
      typeof data.productVariantId === "string" &&
      data.productVariantId &&
      "wordpressId" in data &&
      typeof data.wordpressId === "string" &&
      data.wordpressId
    ) {
      const wordpress =
        await this.wordpressService_.wordpressRepository_.findOne({
          where: {
            id: data.wordpressId,
          },
        });

      if (wordpress) {
        this.syncProductVariant(
          data.productVariantId,
          [wordpress],
          "syncTimestamp" in data && typeof data.syncTimestamp === "number"
            ? data.syncTimestamp
            : undefined,
        );
      }
    }
  };

  syncProductCollection = async (
    productCollectionId: ProductCollection["id"],
    wordpressEntities: Wordpress[],
    syncTimestamp?: number,
  ) => {
    if (!wordpressEntities.length) {
      return;
    }

    const productCollection =
      await this.productCollectionService_.retrieve(productCollectionId);

    await Promise.all(
      wordpressEntities.map((wordpress) =>
        this.wordpressService_.webhook(
          "product-collection",
          productCollection,
          wordpress,
          syncTimestamp,
        ),
      ),
    );
  };

  productCollectionEventHandler = async (data: unknown) => {
    if (
      typeof data === "object" &&
      data &&
      "id" in data &&
      typeof data.id === "string" &&
      data.id
    ) {
      const feeds = await this.wordpressService_.wordpressRepository_.find();

      this.syncProductCollection(data.id, feeds);
    }
  };

  productCollectionForcedSyncHandler = async (data: unknown) => {
    if (
      typeof data === "object" &&
      data &&
      "productCollectionId" in data &&
      typeof data.productCollectionId === "string" &&
      data.productCollectionId &&
      "wordpressId" in data &&
      typeof data.wordpressId === "string" &&
      data.wordpressId
    ) {
      const wordpress =
        await this.wordpressService_.wordpressRepository_.findOne({
          where: {
            id: data.wordpressId,
          },
        });

      if (wordpress) {
        this.syncProductCollection(
          data.productCollectionId,
          [wordpress],
          "syncTimestamp" in data && typeof data.syncTimestamp === "number"
            ? data.syncTimestamp
            : undefined,
        );
      }
    }
  };

  productCollectionProductsRelationSyncEventHandler = async (data: unknown) => {
    if (
      typeof data === "object" &&
      data &&
      "productIds" in data &&
      Array.isArray(data.productIds) &&
      data.productIds.length &&
      data.productIds.every((id) => typeof id === "string")
    ) {
      data.productIds.forEach((productId) => {
        this.eventBusService_.emit("wordpress.product.sync", {
          productId,
        });
      });
    }
  };

  syncRegion = async (
    regionId: Region["id"],
    wordpressEntities: Wordpress[],
    syncTimestamp?: number,
  ) => {
    if (!wordpressEntities.length) {
      return;
    }

    const region = await this.regionService_.retrieve(regionId, {
      relations: this.regionRelations,
    });

    await Promise.all(
      wordpressEntities.map((wordpress) =>
        this.wordpressService_.webhook(
          "region",
          region,
          wordpress,
          syncTimestamp,
        ),
      ),
    );
  };

  regionEventHandler = async (data: unknown) => {
    if (
      typeof data === "object" &&
      data &&
      "id" in data &&
      typeof data.id === "string" &&
      data.id
    ) {
      const feeds = await this.wordpressService_.wordpressRepository_.find();

      this.syncRegion(data.id, feeds);
    }
  };

  regionForcedSyncHandler = async (data: unknown) => {
    if (
      typeof data === "object" &&
      data &&
      "regionId" in data &&
      typeof data.regionId === "string" &&
      data.regionId &&
      "wordpressId" in data &&
      typeof data.wordpressId === "string" &&
      data.wordpressId
    ) {
      const wordpress =
        await this.wordpressService_.wordpressRepository_.findOne({
          where: {
            id: data.wordpressId,
          },
        });

      if (wordpress) {
        this.syncRegion(
          data.regionId,
          [wordpress],
          "syncTimestamp" in data && typeof data.syncTimestamp === "number"
            ? data.syncTimestamp
            : undefined,
        );
      }
    }
  };
}

export default WordpressSubscriber;
