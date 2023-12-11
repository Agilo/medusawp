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
const preview_docs_1 = require("@redocly/cli/lib/commands/preview-docs");
const commander_1 = require("commander");
const execa_1 = __importDefault(require("execa"));
const promises_1 = __importStar(require("fs/promises"));
const lodash_1 = require("lodash");
const path = __importStar(require("path"));
const circular_patch_utils_1 = require("./utils/circular-patch-utils");
const fs_utils_1 = require("./utils/fs-utils");
const json_utils_1 = require("./utils/json-utils");
const yaml_utils_1 = require("./utils/yaml-utils");
/**
 * Constants
 */
const basePath = path.resolve(__dirname, "../");
const medusaPluginRelativePath = "./plugins/medusa/index.js";
const medusaPluginAbsolutePath = path.resolve(basePath, "redocly/plugins/medusa/index.js");
const configFileDefault = path.resolve(basePath, "redocly/redocly-config.yaml");
/**
 * CLI Command declaration
 */
exports.commandName = "docs";
exports.commandDescription = "Sanitize OAS for use with Redocly's API documentation viewer.";
exports.commandOptions = [
    new commander_1.Option("-s, --src-file <srcFile>", "Path to source OAS JSON file.").makeOptionMandatory(),
    new commander_1.Option("-o, --out-dir <outDir>", "Destination directory to output the sanitized OAS files.").default(process.cwd()),
    new commander_1.Option("--config <config>", "Configuration file to merge with default configuration before passing to Redocly's CLI."),
    new commander_1.Option("-D, --dry-run", "Do not output files."),
    new commander_1.Option("--clean", "Delete destination directory content before generating documentation."),
    new commander_1.Option("--split", "Creates a multi-file structure output."),
    new commander_1.Option("--preview", "Open a preview of the documentation. Does not output files."),
    new commander_1.Option("--html", "Generate a static HTML using Redocly's build-docs command."),
    new commander_1.Option("--main-file-name <mainFileName>", "The name of the main YAML file.").default("openapi.yaml")
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
    const shouldClean = !!cliParams.clean;
    const shouldSplit = !!cliParams.split;
    const shouldPreview = !!cliParams.preview;
    const shouldBuildHTML = !!cliParams.html;
    const dryRun = !!cliParams.dryRun;
    const srcFile = path.resolve(cliParams.srcFile);
    const outDir = path.resolve(cliParams.outDir);
    const configFileCustom = cliParams.config
        ? path.resolve(cliParams.config)
        : undefined;
    if (configFileCustom) {
        if (!(await (0, fs_utils_1.isFile)(configFileCustom))) {
            throw new Error(`--config must be a file - ${configFileCustom}`);
        }
        if (![".json", ".yaml"].includes(path.extname(configFileCustom))) {
            throw new Error(`--config file must be of type .json or .yaml - ${configFileCustom}`);
        }
    }
    /**
     * Command execution
     */
    console.log(`🟣 Generating API documentation`);
    const tmpDir = await (0, fs_utils_1.getTmpDirectory)();
    const configTmpFile = path.resolve(tmpDir, "redocly-config.yaml");
    /** matches naming convention from `redocly split` */
    const finalOASFile = cliParams.mainFileName;
    await createTmpConfig(configFileDefault, configTmpFile);
    if (configFileCustom) {
        console.log(`🔵 Merging configuration file - ${configFileCustom} > ${configTmpFile}`);
        await mergeConfig(configTmpFile, configFileCustom, configTmpFile);
    }
    if (!dryRun) {
        if (shouldClean) {
            console.log(`🟠 Cleaning output directory`);
            await promises_1.default.rm(outDir, { recursive: true, force: true });
        }
        await (0, promises_1.mkdir)(outDir, { recursive: true });
    }
    const srcFileSanitized = path.resolve(tmpDir, "tmp.oas.json");
    await sanitizeOAS(srcFile, srcFileSanitized, configTmpFile);
    await circularReferenceCheck(srcFileSanitized);
    if (dryRun) {
        console.log(`⚫️ Dry run - no files generated`);
        return;
    }
    if (shouldPreview) {
        await preview(srcFileSanitized, configTmpFile);
        return;
    }
    if (shouldSplit) {
        await generateReference(srcFileSanitized, outDir);
    }
    else {
        await (0, yaml_utils_1.jsonFileToYamlFile)(srcFileSanitized, path.join(outDir, finalOASFile));
    }
    if (shouldBuildHTML) {
        const outHTMLFile = path.resolve(outDir, "index.html");
        await buildHTML(finalOASFile, outHTMLFile, configTmpFile);
    }
    console.log(`⚫️ API documentation generated - ${outDir}`);
}
exports.execute = execute;
const mergeConfig = async (configFileDefault, configFileCustom, configFileOut) => {
    const configDefault = await (0, yaml_utils_1.readYaml)(configFileDefault);
    const configCustom = path.extname(configFileCustom) === ".yaml"
        ? await (0, yaml_utils_1.readYaml)(configFileCustom)
        : await (0, json_utils_1.readJson)(configFileCustom);
    const config = (0, lodash_1.mergeWith)(configDefault, configCustom, (objValue, srcValue) => (0, lodash_1.isArray)(objValue) ? objValue.concat(srcValue) : undefined);
    await (0, yaml_utils_1.writeYaml)(configFileOut, config);
};
const createTmpConfig = async (configFileDefault, configFileOut) => {
    var _a;
    const config = (await (0, yaml_utils_1.readYaml)(configFileDefault));
    config.plugins = ((_a = config.plugins) !== null && _a !== void 0 ? _a : []).filter((plugin) => plugin !== medusaPluginRelativePath);
    config.plugins.push(medusaPluginAbsolutePath);
    await (0, yaml_utils_1.writeYaml)(configFileOut, config);
};
const sanitizeOAS = async (srcFile, outFile, configFile) => {
    const { all: logs } = await (0, execa_1.default)("yarn", [
        "redocly",
        "bundle",
        srcFile,
        `--output=${outFile}`,
        `--config=${configFile}`,
    ], { cwd: basePath, all: true });
    console.log(logs);
};
const circularReferenceCheck = async (srcFile) => {
    const { circularRefs, oas } = await (0, circular_patch_utils_1.getCircularReferences)(srcFile);
    if (circularRefs.length) {
        console.log(circularRefs);
        let errorMessage = `🔴 Unhandled circular references - Please manually patch using --config ./redocly-config.yaml`;
        const recommendation = (0, circular_patch_utils_1.getCircularPatchRecommendation)(circularRefs, oas);
        if (Object.keys(recommendation).length) {
            const hint = (0, circular_patch_utils_1.formatHintRecommendation)(recommendation);
            errorMessage += `
Within redocly-config.yaml, try adding the following:
###
${hint}
###
`;
        }
        throw new Error(errorMessage);
    }
    console.log(`🟢 All circular references are handled`);
};
const generateReference = async (srcFile, outDir) => {
    const { all: logs } = await (0, execa_1.default)("yarn", ["redocly", "split", srcFile, `--outDir=${outDir}`], { cwd: basePath, all: true });
    console.log(logs);
};
const preview = async (oasFile, configFile) => {
    await (0, preview_docs_1.previewDocs)({
        port: 8080,
        host: "127.0.0.1",
        api: oasFile,
        config: configFile,
    });
};
const buildHTML = async (srcFile, outFile, configFile) => {
    const { all: logs } = await (0, execa_1.default)("yarn", [
        "redocly",
        "build-docs",
        srcFile,
        `--output=${outFile}`,
        `--config=${configFile}`,
        `--cdn=true`,
    ], { cwd: basePath, all: true });
    console.log(logs);
};
//# sourceMappingURL=command-docs.js.map