export declare const readYaml: (filePath: any) => Promise<unknown>;
export declare const writeYaml: (filePath: any, jsonObject: any) => Promise<void>;
export declare const jsonObjectToYamlString: (jsonObject: any) => string;
export declare const jsonFileToYamlFile: (inputJsonFile: any, outputYamlFile: any) => Promise<void>;
