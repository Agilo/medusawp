"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.execute = exports.getCommand = exports.commandOptions = exports.commandDescription = exports.commandName = void 0;
const path_1 = __importDefault(require("path"));
const openapi_typescript_codegen_1 = require("@medusajs/openapi-typescript-codegen");
const lodash_1 = require("lodash");
const promises_1 = __importStar(require("fs/promises"));
const commander_1 = require("commander");
/**
 * CLI Command declaration
 */
exports.commandName = "client";
exports.commandDescription = "Generate API clients from OAS.";
exports.commandOptions = [
    new commander_1.Option("-t, --type <type>", "Namespace for the generated client. Usually `admin` or `store`.").makeOptionMandatory(),
    new commander_1.Option("-s, --src-file <srcFile>", "Path to source OAS JSON file.").makeOptionMandatory(),
    new commander_1.Option("-o, --out-dir <outDir>", "Output directory for generated client files.").default(path_1.default.resolve(process.cwd(), "client")),
    new commander_1.Option("-c, --component <component>", "Client component types to generate.")
        .choices(["all", "types", "client", "hooks"])
        .default("all"),
    new commander_1.Option("--types-package <name>", "Replace relative import statements by types package name."),
    new commander_1.Option("--client-package <name>", "Replace relative import statements by client package name."),
    new commander_1.Option("--clean", "Delete destination directory content before generating client."),
];
function getCommand() {
    const command = new commander_1.Command(exports.commandName);
    command.description(exports.commandDescription);
    for (const opt of exports.commandOptions) {
        command.addOption(opt);
    }
    command.action(async (options) => await execute(options));
    command.showHelpAfterError(true);
    return command;
}
exports.getCommand = getCommand;
/**
 * Main
 */
async function execute(cliParams) {
    /**
     * Process CLI options
     */
    if (["client", "hooks"].includes(cliParams.component) &&
        !cliParams.typesPackage) {
        throw new Error(`--types-package must be declared when using --component=${cliParams.component}`);
    }
    if (cliParams.component === "hooks" && !cliParams.clientPackage) {
        throw new Error(`--client-package must be declared when using --component=${cliParams.component}`);
    }
    const shouldClean = !!cliParams.clean;
    const srcFile = path_1.default.resolve(cliParams.srcFile);
    const outDir = path_1.default.resolve(cliParams.outDir);
    const apiName = cliParams.type;
    const packageNames = {
        models: cliParams.typesPackage,
        client: cliParams.clientPackage,
    };
    const exportComponent = cliParams.component;
    /**
     * Command execution
     */
    console.log(`🟣 Generating client - ${apiName} - ${exportComponent}`);
    if (shouldClean) {
        console.log(`🟠 Cleaning output directory`);
        await promises_1.default.rm(outDir, { recursive: true, force: true });
    }
    await (0, promises_1.mkdir)(outDir, { recursive: true });
    const oas = await getOASFromFile(srcFile);
    await generateClientSDK(oas, outDir, apiName, exportComponent, packageNames);
    console.log(`⚫️ Client generated - ${apiName} - ${exportComponent} - ${outDir}`);
}
exports.execute = execute;
/**
 * Methods
 */
const getOASFromFile = async (jsonFile) => {
    const jsonString = await (0, promises_1.readFile)(jsonFile, "utf8");
    return JSON.parse(jsonString);
};
const generateClientSDK = async (oas, targetDir, apiName, exportComponent, packageNames = {}) => {
    const exports = {
        exportCore: false,
        exportServices: false,
        exportModels: false,
        exportHooks: false,
    };
    switch (exportComponent) {
        case "types":
            exports.exportModels = true;
            break;
        case "client":
            exports.exportCore = true;
            exports.exportServices = true;
            break;
        case "hooks":
            exports.exportHooks = true;
            break;
        default:
            exports.exportCore = true;
            exports.exportServices = true;
            exports.exportModels = true;
            exports.exportHooks = true;
    }
    await (0, openapi_typescript_codegen_1.generate)({
        input: oas,
        output: targetDir,
        httpClient: openapi_typescript_codegen_1.HttpClient.AXIOS,
        useOptions: true,
        useUnionTypes: true,
        exportCore: exports.exportCore,
        exportServices: exports.exportServices,
        exportModels: exports.exportModels,
        exportHooks: exports.exportHooks,
        exportSchemas: false,
        indent: openapi_typescript_codegen_1.Indent.SPACE_2,
        postfixServices: "Service",
        postfixModels: "",
        clientName: `Medusa${(0, lodash_1.upperFirst)(apiName)}`,
        request: undefined,
        packageNames,
    });
};
//# sourceMappingURL=command-client.js.map