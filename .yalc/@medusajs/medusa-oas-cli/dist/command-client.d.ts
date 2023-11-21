import { Command, Option, OptionValues } from "commander";
/**
 * CLI Command declaration
 */
export declare const commandName = "client";
export declare const commandDescription = "Generate API clients from OAS.";
export declare const commandOptions: Option[];
export declare function getCommand(): Command;
/**
 * Main
 */
export declare function execute(cliParams: OptionValues): Promise<void>;
