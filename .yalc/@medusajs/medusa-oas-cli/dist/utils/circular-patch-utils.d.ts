import { OpenAPIObject } from "openapi3-ts";
export declare const getCircularReferences: (srcFile: string) => Promise<{
    circularRefs: string[];
    oas: OpenAPIObject;
}>;
export declare const getCircularPatchRecommendation: (circularRefs: string[], oas: OpenAPIObject) => Record<string, string[]>;
export declare const formatHintRecommendation: (recommendation: Record<string, string[]>) => string;
