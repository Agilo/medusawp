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
exports.jsonFileToYamlFile = exports.jsonObjectToYamlString = exports.writeYaml = exports.readYaml = void 0;
const promises_1 = __importDefault(require("fs/promises"));
const yaml = __importStar(require("js-yaml"));
const readYaml = async (filePath) => {
    const yamlString = await promises_1.default.readFile(filePath, "utf8");
    return yaml.load(yamlString);
};
exports.readYaml = readYaml;
const writeYaml = async (filePath, jsonObject) => {
    const yamlString = yaml.dump(jsonObject);
    await promises_1.default.writeFile(filePath, yamlString, "utf8");
};
exports.writeYaml = writeYaml;
const jsonObjectToYamlString = (jsonObject) => {
    return yaml.dump(jsonObject);
};
exports.jsonObjectToYamlString = jsonObjectToYamlString;
const jsonFileToYamlFile = async (inputJsonFile, outputYamlFile) => {
    const jsonString = await promises_1.default.readFile(inputJsonFile, "utf8");
    const jsonObject = JSON.parse(jsonString);
    const yamlString = yaml.dump(jsonObject);
    await promises_1.default.writeFile(outputYamlFile, yamlString, "utf8");
};
exports.jsonFileToYamlFile = jsonFileToYamlFile;
//# sourceMappingURL=yaml-utils.js.map