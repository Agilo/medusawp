"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.mergePathsAndSchemasIntoOAS = exports.mergeBaseIntoOAS = void 0;
function mergeBaseIntoOAS(targetOAS, sourceOAS) {
    var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k, _l;
    /**
     * replace strategy for OpenAPIObject properties
     */
    targetOAS.openapi = (_a = sourceOAS.openapi) !== null && _a !== void 0 ? _a : targetOAS.openapi;
    targetOAS.info = (_b = sourceOAS.info) !== null && _b !== void 0 ? _b : targetOAS.info;
    targetOAS.servers = (_c = sourceOAS.servers) !== null && _c !== void 0 ? _c : targetOAS.servers;
    targetOAS.security = (_d = sourceOAS.security) !== null && _d !== void 0 ? _d : targetOAS.security;
    targetOAS.externalDocs = (_e = sourceOAS.externalDocs) !== null && _e !== void 0 ? _e : targetOAS.externalDocs;
    targetOAS.webhooks = (_f = sourceOAS.webhooks) !== null && _f !== void 0 ? _f : targetOAS.webhooks;
    /**
     * merge + concat strategy for tags
     */
    const targetTags = (_g = targetOAS.tags) !== null && _g !== void 0 ? _g : [];
    const sourceTags = (_h = sourceOAS.tags) !== null && _h !== void 0 ? _h : [];
    const combinedTags = [];
    const sourceIndexes = [];
    for (const targetTag of targetTags) {
        for (const [sourceTagIndex, sourceTag] of sourceTags.entries()) {
            if (targetTag.name === sourceTag.name) {
                combinedTags.push(sourceTag);
                sourceIndexes.push(sourceTagIndex);
                continue;
            }
            combinedTags.push(targetTag);
        }
    }
    for (const [sourceTagIndex, sourceTag] of sourceTags.entries()) {
        if (!sourceIndexes.includes(sourceTagIndex)) {
            combinedTags.push(sourceTag);
        }
    }
    targetOAS.tags = combinedTags;
    /**
     * merge strategy for paths
     */
    targetOAS.paths = Object.assign((_j = targetOAS.paths) !== null && _j !== void 0 ? _j : {}, (_k = sourceOAS.paths) !== null && _k !== void 0 ? _k : {});
    /**
     * merge strategy for components
     */
    if (!sourceOAS.components) {
        return;
    }
    if (!targetOAS.components) {
        targetOAS.components = {};
    }
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
        if (Object.keys(sourceOAS.components).includes(componentGroup)) {
            targetOAS.components[componentGroup] = Object.assign((_l = targetOAS.components[componentGroup]) !== null && _l !== void 0 ? _l : {}, sourceOAS.components[componentGroup]);
        }
    }
}
exports.mergeBaseIntoOAS = mergeBaseIntoOAS;
function mergePathsAndSchemasIntoOAS(targetOAS, sourceOAS) {
    var _a;
    /**
     * merge paths
     */
    Object.assign(targetOAS.paths, sourceOAS.paths);
    /**
     * merge components.schemas
     */
    if ((_a = sourceOAS.components) === null || _a === void 0 ? void 0 : _a.schemas) {
        if (!targetOAS.components) {
            targetOAS.components = {};
        }
        if (!targetOAS.components.schemas) {
            targetOAS.components.schemas = {};
        }
        Object.assign(targetOAS.components.schemas, sourceOAS.components.schemas);
    }
}
exports.mergePathsAndSchemasIntoOAS = mergePathsAndSchemasIntoOAS;
//# sourceMappingURL=merge-oas.js.map