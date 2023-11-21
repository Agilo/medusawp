"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.formatHintRecommendation = exports.getCircularPatchRecommendation = exports.getCircularReferences = void 0;
const openapi_parser_1 = __importDefault(require("@readme/openapi-parser"));
const yaml_utils_1 = require("./yaml-utils");
const getCircularReferences = async (srcFile) => {
    const parser = new openapi_parser_1.default();
    const oas = (await parser.validate(srcFile, {
        dereference: {
            circular: "ignore",
        },
    }));
    if (parser.$refs.circular) {
        const $refs = parser.$refs;
        let circularRefs = $refs.circularRefs.map((ref) => ref.match(/#\/components\/schemas\/.*/)[0]);
        circularRefs = [...new Set(circularRefs)];
        circularRefs.sort();
        return { circularRefs, oas };
    }
    return { circularRefs: [], oas };
};
exports.getCircularReferences = getCircularReferences;
const getCircularPatchRecommendation = (circularRefs, oas) => {
    const matches = circularRefs
        .map((ref) => {
        var _a;
        let match = (_a = ref.match(/(?:.*)(?:#\/components\/schemas\/)(.*)(?:\/properties\/?)(.*)/)) !== null && _a !== void 0 ? _a : [];
        let schema = match[1];
        let property = match[2];
        let isArray = false;
        if (property.endsWith("/items")) {
            property = property.replace("/items", "");
            isArray = true;
        }
        return { schema, property, isArray };
    })
        .filter((match) => match.property !== "")
        .map((match) => {
        const baseSchema = oas.components.schemas[match.schema];
        const propertySpec = match.isArray
            ? baseSchema.properties[match.property].items
            : baseSchema.properties[match.property];
        const referencedSchema = propertySpec["$ref"].match(/(?:#\/components\/schemas\/)(.*)/)[1];
        return {
            schema: match.schema,
            property: match.property,
            isArray: match.isArray,
            referencedSchema,
        };
    });
    const schemas = {};
    for (const match of matches) {
        if (!schemas.hasOwnProperty(match.schema)) {
            schemas[match.schema] = [];
        }
        schemas[match.schema].push(match.referencedSchema);
    }
    return schemas;
};
exports.getCircularPatchRecommendation = getCircularPatchRecommendation;
const formatHintRecommendation = (recommendation) => {
    return (0, yaml_utils_1.jsonObjectToYamlString)({
        decorators: { "medusa/circular-patch": { schemas: recommendation } },
    });
};
exports.formatHintRecommendation = formatHintRecommendation;
//# sourceMappingURL=circular-patch-utils.js.map