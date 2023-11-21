import { Command, Option, OptionValues } from "commander";
/**
 * CLI Command declaration
 */
export declare const commandName = "oas";
export declare const commandDescription = "Compile full OAS from swagger-inline compliant JSDoc.";
export declare const commandOptions: Option[];
export declare function getCommand(): Command;
/**
 * Main
 */
export declare function execute(cliParams: OptionValues): Promise<void>;
