"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.combineOAS = void 0;
const lodash_1 = require("lodash");
async function combineOAS(adminOAS, storeOAS) {
    prepareOASForCombine(adminOAS, "admin");
    prepareOASForCombine(storeOAS, "store");
    const combinedOAS = {
        openapi: "3.0.0",
        info: { title: "Medusa API", version: "1.0.0" },
        servers: [],
        paths: {},
        tags: [],
        components: {
            callbacks: {},
            examples: {},
            headers: {},
            links: {},
            parameters: {},
            requestBodies: {},
            responses: {},
            schemas: {},
            securitySchemes: {},
        },
    };
    for (const oas of [adminOAS, storeOAS]) {
        /**
         * Combine paths
         */
        Object.assign(combinedOAS.paths, oas.paths);
        /**
         * Combine tags
         */
        if (oas.tags) {
            combinedOAS.tags = [...combinedOAS.tags, ...oas.tags];
        }
        /**
         * Combine components
         */
        if (oas.components) {
            for (const componentGroup of [
                "callbacks",
                "examples",
                "headers",
                "links",
                "parameters",
                "requestBodies",
                "responses",
                "schemas",
                "securitySchemes",
            ]) {
                if (Object.keys(oas.components).includes(componentGroup)) {
                    Object.assign(combinedOAS.components[componentGroup], oas.components[componentGroup]);
                }
            }
        }
    }
    return combinedOAS;
}
exports.combineOAS = combineOAS;
function prepareOASForCombine(oas, apiType) {
    console.log(`🔵 Prefixing ${apiType} tags and operationId with ${(0, lodash_1.upperFirst)(apiType)}`);
    for (const pathKey in oas.paths) {
        for (const operationKey in oas.paths[pathKey]) {
            /**
             * Prefix tags declared on routes
             * e.g.: Admin Customer, Store Customer
             */
            if (oas.paths[pathKey][operationKey].tags) {
                oas.paths[pathKey][operationKey].tags = oas.paths[pathKey][operationKey].tags.map((tag) => getPrefixedTagName(tag, apiType));
            }
            /**
             * Prefix operationId
             * e.g.: AdminGetCustomers, StoreGetCustomers
             */
            if (oas.paths[pathKey][operationKey].operationId) {
                oas.paths[pathKey][operationKey].operationId = getPrefixedOperationId(oas.paths[pathKey][operationKey].operationId, apiType);
            }
        }
    }
    /**
     * Prefix tags globally
     * e.g.: Admin Customer, Store Customer
     */
    if (oas.tags) {
        for (const tag of oas.tags) {
            tag.name = getPrefixedTagName(tag.name, apiType);
        }
    }
    return oas;
}
function getPrefixedTagName(tagName, apiType) {
    return `${(0, lodash_1.upperFirst)(apiType)} ${tagName}`;
}
function getPrefixedOperationId(operationId, apiType) {
    return `${(0, lodash_1.upperFirst)(apiType)}${operationId}`;
}
//# sourceMappingURL=combine-oas.js.map