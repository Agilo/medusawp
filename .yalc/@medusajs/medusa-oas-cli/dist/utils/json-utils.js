"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.writeJson = exports.readJson = void 0;
const promises_1 = __importDefault(require("fs/promises"));
const readJson = async (filePath) => {
    const jsonString = await promises_1.default.readFile(filePath, "utf8");
    return JSON.parse(jsonString);
};
exports.readJson = readJson;
const writeJson = async (filePath, jsonObject) => {
    const jsonString = JSON.stringify(jsonObject);
    await promises_1.default.writeFile(filePath, jsonString, "utf8");
};
exports.writeJson = writeJson;
//# sourceMappingURL=json-utils.js.map