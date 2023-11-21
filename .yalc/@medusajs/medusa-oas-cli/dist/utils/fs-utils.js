"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.getTmpDirectory = exports.exists = exports.isFile = void 0;
const promises_1 = require("fs/promises");
const path_1 = __importDefault(require("path"));
const path_2 = require("path");
const os_1 = require("os");
async function isFile(filePath) {
    try {
        return (await (0, promises_1.lstat)(path_1.default.resolve(filePath))).isFile();
    }
    catch (err) {
        console.log(err);
        return false;
    }
}
exports.isFile = isFile;
async function exists(filePath) {
    try {
        await (0, promises_1.access)(path_1.default.resolve(filePath));
        return true;
    }
    catch (err) {
        return false;
    }
}
exports.exists = exists;
const getTmpDirectory = async () => {
    var _a;
    /**
     * RUNNER_TEMP: GitHub action, the path to a temporary directory on the runner.
     */
    const tmpDir = (_a = process.env["RUNNER_TEMP"]) !== null && _a !== void 0 ? _a : (0, os_1.tmpdir)();
    return await (0, promises_1.mkdtemp)(`${tmpDir}${path_2.sep}`);
};
exports.getTmpDirectory = getTmpDirectory;
//# sourceMappingURL=fs-utils.js.map