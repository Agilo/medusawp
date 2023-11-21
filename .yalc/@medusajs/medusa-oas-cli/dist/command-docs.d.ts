import { Command, Option, OptionValues } from "commander";
/**
 * CLI Command declaration
 */
export declare const commandName = "docs";
export declare const commandDescription = "Sanitize OAS for use with Redocly's API documentation viewer.";
export declare const commandOptions: Option[];
export declare function getCommand(): Command;
/**
 * Main
 */
export declare function execute(cliParams: OptionValues): Promise<void>;
