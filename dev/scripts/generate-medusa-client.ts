import path from "path";
import { execa } from "execa";
import * as url from "url";
import fs from "fs-extra";

const __dirname = url.fileURLToPath(new URL(".", import.meta.url));
const rootDir = path.resolve(__dirname, "..", "..");
const firstArg = process.argv[2]?.trim();
const type = (["store", "admin"].includes(firstArg) ? firstArg : "store") as
  | "store"
  | "admin";

const oasOperationIds = {
  store: ["PostCart", "GetCartsCart", "PostCartsCart"],
  admin: ["MedusaWPConnect", "MedusaWPDisconnect", "MedusaWPSync"],
};

const copyPaths = ["lib", "test", "phpunit.xml.dist"];

function copySecuritySchemas(srcOasJsonPath: string, distOasJsonPath: string) {
  const srcOasJson = fs.readJsonSync(srcOasJsonPath);
  const distOasJson = fs.readJsonSync(distOasJsonPath);

  const securitySchemas = srcOasJson.components.securitySchemes;

  distOasJson.components.securitySchemes = securitySchemas;

  fs.writeJsonSync(distOasJsonPath, distOasJson, { spaces: 2 });
}

(async () => {
  await execa(
    "npx",
    [
      "medusa-oas",
      "oas",
      "--out-dir",
      ".oas",
      "--type",
      type,
      ...(type === "admin" ? ["--paths", "./medusa-plugin/src/"] : []),
    ],
    {
      cwd: rootDir,
      stdio: "inherit",
    },
  );

  await execa(
    "npx",
    [
      "openapi-filter",
      `./.oas/${type}.oas.json`,
      `./.oas/filtered-${type}.oas.json`,
      "--inverse",
      "--valid",
      "--flags",
      "operationId",
      ...oasOperationIds[type].map((id) => ["-v", id]).flat(),
    ],
    {
      cwd: rootDir,
      stdio: "inherit",
    },
  );

  copySecuritySchemas(
    path.join(rootDir, ".oas", `${type}.oas.json`),
    path.join(rootDir, ".oas", `filtered-${type}.oas.json`),
  );

  await execa(
    "docker",
    [
      "run",
      "--rm",
      "-e",
      "JAVA_OPTS=-Xms4G -Xmx8G",
      "-v",
      `${rootDir}/.oas:/local`,
      "openapitools/openapi-generator-cli:v6.6.0",
      "generate",
      "-i",
      `/local/filtered-${type}.oas.json`,
      "-g",
      "php",
      "-o",
      `/local/${type}-client`,
      `--additional-properties`,
      `variableNamingConvention=snake_case,composerPackageName=agilo/medusawp-medusa-${type}-client,invokerPackage=MedusaWP\\MedusaClient\\${
        type === "admin" ? "Admin" : "Store"
      }`,
    ],
    {
      cwd: rootDir,
      stdio: "inherit",
    },
  );

  await fs.ensureDir(
    path.join(rootDir, "wordpress-plugin", "oas-clients", type),
  );

  await Promise.all(
    copyPaths.map(async (copyPath) => {
      const exists = await fs.exists(
        path.join(rootDir, ".oas", `${type}-client`, copyPath),
      );

      if (!exists) {
        await fs.remove(
          path.join(rootDir, "wordpress-plugin", "oas-clients", type, copyPath),
        );

        return;
      }

      return fs.copy(
        path.join(rootDir, ".oas", `${type}-client`, copyPath),
        path.join(rootDir, "wordpress-plugin", "oas-clients", type, copyPath),
        { overwrite: true },
      );
    }),
  );

  await execa(
    path.join(rootDir, "wordpress-plugin", "vendor", "bin", "phpcbf"),
    ["-q", `./oas-clients/${type}`],
    {
      cwd: path.join(rootDir, "wordpress-plugin"),
      stdio: "inherit",
      reject: false,
    },
  );
})();
