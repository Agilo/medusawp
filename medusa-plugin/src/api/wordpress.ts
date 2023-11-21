import { Router, NextFunction, Request, Response } from "express";
import bodyParser from "body-parser";
import cors from "cors";
import authenticate from "@medusajs/medusa/dist/api/middlewares/authenticate";
import { getConfigFile, parseCorsOrigins } from "medusa-core-utils";

import { validateOriginHeader } from "../middlewares/validateOriginHeader";
import WordpressService from "../services/wordpress";

export default (rootDirectory: string, options: unknown) => {
  const routes = Router();
  const { configModule } = getConfigFile(rootDirectory, `medusa-config`);
  const projectConfig =
    (typeof configModule === "object" &&
      configModule &&
      "projectConfig" in configModule &&
      typeof configModule.projectConfig === "object" &&
      configModule.projectConfig) ||
    {};
  const corsOptions = {
    origin:
      "store_cors" in projectConfig &&
      typeof projectConfig.store_cors === "string"
        ? parseCorsOrigins(projectConfig.store_cors)
        : undefined,
    credentials: true,
  };

  /**
   * Connect endpoint
   */
  routes.options("/connect", cors(corsOptions));

  /**
   * @oas [post] /wordpress/connect
   * operationId: "MedusaWPConnect"
   * summary: "Connects Medusa to WordPress"
   * x-authenticated: true
   * security:
   *   - api_token: []
   *   - cookie_auth: []
   * parameters:
   *   - in: header
   *     name: origin
   *     schema:
   *       type: string
   *     required: true
   *     description: "WordPress instance origin"
   * requestBody:
   *   content:
   *     application/json:
   *       schema:
   *         type: object
   *         required:
   *           - email
   *           - password
   *         properties:
   *           email:
   *             type: string
   *             description: "Medusa admin user email"
   *           password:
   *             type: string
   *             description: "Medusa admin user password"
   * responses:
   *   "200":
   *     description: OK
   *     content:
   *       application/json:
   *         schema:
   *           type: object
   *           required:
   *             - user
   *             - wordpress
   *           properties:
   *             user:
   *               description: "User details"
   *               $ref: "#/components/schemas/User"
   *             wordpress:
   *               description: "WordPress details"
   *               $ref: "#/components/schemas/WordPress"
   */
  routes.post(
    "/connect",
    bodyParser.json(),
    cors(corsOptions),
    validateOriginHeader, // Make sure to send valid Origin header
    async (req: Request, res: Response, next: NextFunction) => {
      const wordpressService = req.scope.resolve(
        "wordpressService",
      ) as WordpressService;
      return wordpressService.connect(req, res);
    },
  );

  /**
   * Disconnect endpoint
   */
  routes.options("/disconnect", cors(corsOptions));

  /**
   * @oas [post] /wordpress/disconnect
   * operationId: "MedusaWPDisconnect"
   * summary: "Removes connection between Medusa and WordPress"
   * x-authenticated: true
   * security:
   *   - api_token: []
   *   - cookie_auth: []
   * parameters:
   *   - in: header
   *     name: origin
   *     schema:
   *       type: string
   *     required: true
   *     description: "WordPress instance origin"
   * responses:
   *   "200":
   *     description: OK
   */
  routes.post(
    "/disconnect",
    bodyParser.json(),
    cors(corsOptions),
    validateOriginHeader, // Make sure to send valid Origin header
    authenticate(), // Make sure to send Authorization Bearer token in Request header
    async (req: Request, res: Response, next: NextFunction) => {
      const wordpressService = req.scope.resolve(
        "wordpressService",
      ) as WordpressService;
      return wordpressService.disconnect(req, res);
    },
  );

  /**
   * Start bulk sync endpoint
   */
  routes.options("/sync", cors(corsOptions));

  /**
   * @oas [post] /wordpress/sync
   * operationId: "MedusaWPSync"
   * summary: "Starts a bulk sync of Medusa and WordPress"
   * x-authenticated: true
   * security:
   *   - api_token: []
   *   - cookie_auth: []
   * parameters:
   *   - in: header
   *     name: origin
   *     schema:
   *       type: string
   *     required: true
   *     description: "WordPress instance origin"
   * requestBody:
   *   content:
   *     application/json:
   *      schema:
   *        type: object
   *        required:
   *          - sync_timestamp
   *        properties:
   *          sync_timestamp:
   *            type: number
   *            description: "Timestamp of the sync. Will be send back in webhooks payloads"
   *          import_thumbnails:
   *            type: boolean
   *            description: "Whether to import thumbnails or not"
   *            default: false
   * responses:
   *  "200":
   *    description: OK
   *    content:
   *      application/json:
   *        schema:
   *          type: object
   *          required:
   *            - totals
   *          properties:
   *            totals:
   *              type: object
   *              required:
   *                - products
   *                - product_variants
   *                - collections
   *                - regions
   *              properties:
   *                products:
   *                  type: number
   *                  description: "Total number of products"
   *                product_variants:
   *                  type: number
   *                  description: "Total number of product variants"
   *                collections:
   *                  type: number
   *                  description: "Total number of collections"
   *                regions:
   *                  type: number
   *                  description: "Total number of regions"
   *                thumbnails:
   *                  type: number
   *                  description: "Total number of thumbnails"
   */
  routes.post(
    "/sync",
    bodyParser.json(),
    cors(corsOptions),
    validateOriginHeader, // Make sure to send valid Origin header
    authenticate(), // Make sure to send Authorization Bearer token in Request header
    async (req: Request, res: Response, next: NextFunction) => {
      const wordpressService = req.scope.resolve(
        "wordpressService",
      ) as WordpressService;
      return wordpressService.sync(req, res);
    },
  );

  return routes;
};
