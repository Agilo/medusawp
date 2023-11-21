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
const openapi_parser_1 = __importDefault(require("@readme/openapi-parser"));
const commander_1 = require("commander");
const promises_1 = require("fs/promises");
const path = __importStar(require("path"));
const swagger_inline_1 = __importDefault(require("swagger-inline"));
const combine_oas_1 = require("./utils/combine-oas");
const merge_oas_1 = require("./utils/merge-oas");
const fs_utils_1 = require("./utils/fs-utils");
/**
 * Constants
 */
// Medusa core package directory
const medusaPackagePath = path.dirname(require.resolve("@medusajs/medusa/package.json"));
// Types package directory
const medusaTypesPath = path.dirname(require.resolve("@medusajs/types/package.json"));
// Utils package directory
const medusaUtilsPath = path.dirname(require.resolve("@medusajs/utils/package.json"));
const basePath = path.resolve(__dirname, "../");
/**
 * CLI Command declaration
 */
exports.commandName = "oas";
exports.commandDescription = "Compile full OAS from swagger-inline compliant JSDoc.";
exports.commandOptions = [
    new commander_1.Option("-t, --type <type>", "API type to compile.")
        .choices(["admin", "store", "combined"])
        .makeOptionMandatory(),
    new commander_1.Option("-o, --out-dir <outDir>", "Destination directory to output generated OAS files.").default(process.cwd()),
    new commander_1.Option("-D, --dry-run", "Do not output files."),
    new commander_1.Option("-p, --paths <paths...>", "Additional paths to crawl for OAS JSDoc."),
    new commander_1.Option("-b, --base <base>", "Custom base OAS file to use for swagger-inline."),
    new commander_1.Option("-F, --force", "Ignore OAS validation and output OAS files."),
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
    var _a;
    /**
     * Process CLI options
     */
    const dryRun = !!cliParams.dryRun;
    const force = !!cliParams.force;
    const apiType = cliParams.type;
    const outDir = path.resolve(cliParams.outDir);
    const additionalPaths = ((_a = cliParams.paths) !== null && _a !== void 0 ? _a : []).map((additionalPath) => path.resolve(additionalPath));
    for (const additionalPath of additionalPaths) {
        if (!(await isDirectory(additionalPath))) {
            throw new Error(`--paths must be a directory - ${additionalPath}`);
        }
    }
    const baseFile = cliParams.base ? path.resolve(cliParams.base) : undefined;
    if (baseFile) {
        if (!(await (0, fs_utils_1.isFile)(cliParams.base))) {
            throw new Error(`--base must be a file - ${baseFile}`);
        }
    }
    /**
     * Command execution
     */
    if (!dryRun) {
        await (0, promises_1.mkdir)(outDir, { recursive: true });
    }
    let oas;
    console.log(`🟣 Generating OAS - ${apiType}`);
    if (apiType === "combined") {
        const adminOAS = await getOASFromCodebase("admin");
        const storeOAS = await getOASFromCodebase("store");
        oas = await (0, combine_oas_1.combineOAS)(adminOAS, storeOAS);
    }
    else {
        oas = await getOASFromCodebase(apiType);
    }
    if (additionalPaths.length || baseFile) {
        const customOAS = await getOASFromPaths(additionalPaths, baseFile);
        if (baseFile) {
            (0, merge_oas_1.mergeBaseIntoOAS)(oas, customOAS);
        }
        if (additionalPaths.length) {
            (0, merge_oas_1.mergePathsAndSchemasIntoOAS)(oas, customOAS);
        }
    }
    await validateOAS(oas, apiType, force);
    if (dryRun) {
        console.log(`⚫️ Dry run - no files generated`);
        return;
    }
    await exportOASToJSON(oas, apiType, outDir);
}
exports.execute = execute;
/**
 * Methods
 */
async function getOASFromCodebase(apiType, customBaseFile) {
    const gen = await (0, swagger_inline_1.default)([
        path.resolve(medusaTypesPath, "dist"),
        path.resolve(medusaUtilsPath, "dist"),
        path.resolve(medusaPackagePath, "dist", "models"),
        path.resolve(medusaPackagePath, "dist", "types"),
        path.resolve(medusaPackagePath, "dist", "api/middlewares"),
        path.resolve(medusaPackagePath, "dist", `api/routes/${apiType}`),
    ], {
        base: customBaseFile !== null && customBaseFile !== void 0 ? customBaseFile : path.resolve(medusaPackagePath, "oas", `${apiType}.oas.base.yaml`),
        format: ".json",
    });
    return (await openapi_parser_1.default.parse(JSON.parse(gen)));
}
async function getOASFromPaths(additionalPaths = [], customBaseFile) {
    console.log(`🔵 Gathering custom OAS`);
    const gen = await (0, swagger_inline_1.default)(additionalPaths, {
        base: customBaseFile !== null && customBaseFile !== void 0 ? customBaseFile : path.resolve(basePath, "oas", "default.oas.base.yaml"),
        format: ".json",
        logger: (log) => {
            console.log(log);
        },
    });
    return (await openapi_parser_1.default.parse(JSON.parse(gen)));
}
async function validateOAS(oas, apiType, force = false) {
    try {
        await openapi_parser_1.default.validate(JSON.parse(JSON.stringify(oas)));
        console.log(`🟢 Valid OAS - ${apiType}`);
    }
    catch (err) {
        console.error(`🔴 Invalid OAS - ${apiType}`, err);
        if (!force) {
            process.exit(1);
        }
    }
}
async function exportOASToJSON(oas, apiType, targetDir) {
    const json = JSON.stringify(oas, null, 2);
    const filePath = path.resolve(targetDir, `${apiType}.oas.json`);
    await (0, promises_1.writeFile)(filePath, json);
    console.log(`⚫️ Exported OAS - ${apiType} - ${filePath}`);
}
async function isDirectory(dirPath) {
    try {
        return (await (0, promises_1.lstat)(path.resolve(dirPath))).isDirectory();
    }
    catch (err) {
        console.log(err);
        return false;
    }
}
//# sourceMappingURL=command-oas.js.map