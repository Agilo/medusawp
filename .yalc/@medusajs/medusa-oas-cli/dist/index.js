#! /usr/bin/env node
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getBaseCommand = void 0;
const commander_1 = require("commander");
const command_oas_1 = require("./command-oas");
const command_client_1 = require("./command-client");
const command_docs_1 = require("./command-docs");
const run = async () => {
    const program = getBaseCommand();
    /**
     * Alias to command-oas.ts
     */
    program.addCommand((0, command_oas_1.getCommand)());
    /**
     * Alias to command-client.ts
     */
    program.addCommand((0, command_client_1.getCommand)());
    /**
     * Alias to command-docs.ts
     */
    program.addCommand((0, command_docs_1.getCommand)());
    /**
     * Run CLI
     */
    await program.parseAsync();
};
function getBaseCommand() {
    const command = new commander_1.Command();
    command.name("medusa-oas");
    command.action(async () => {
        console.log("No command provided.");
        command.outputHelp({ error: true });
    });
    command.showHelpAfterError(true);
    command.helpOption(false);
    return command;
}
exports.getBaseCommand = getBaseCommand;
void (async () => {
    await run();
})();
//# sourceMappingURL=index.js.map