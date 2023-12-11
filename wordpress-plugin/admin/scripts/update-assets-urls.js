import fs from "fs";
import path from "path";

const dirname = path.dirname(new URL(import.meta.url).pathname);
const distDir = path.resolve(dirname, "../dist");

const manifest = JSON.parse(
  fs.readFileSync(path.join(distDir, ".vite", "manifest.json"), "utf8"),
);

Object.values(manifest)
  .filter((value) => value.isEntry)
  .forEach((value) => {
    if (
      "assets" in value &&
      Array.isArray(value.assets) &&
      value.assets.length
    ) {
      let entryFileContent = fs.readFileSync(
        path.join(distDir, value.file),
        "utf8",
      );

      value.assets.forEach((asset) => {
        entryFileContent = entryFileContent.replace(
          `"/${asset}"`,
          `window.medusawp.distUrl + "/${asset}"`,
        );
      });

      fs.writeFileSync(
        path.join(distDir, value.file),
        entryFileContent,
        "utf8",
      );

      if ("css" in value && Array.isArray(value.css) && value.css.length) {
        value.css.forEach((cssFilePath) => {
          let cssFileContent = fs.readFileSync(
            path.join(distDir, cssFilePath),
            "utf8",
          );
          cssFileContent = cssFileContent.replaceAll(`/assets/`, `./`);

          fs.writeFileSync(
            path.join(distDir, cssFilePath),
            cssFileContent,
            "utf8",
          );
        });
      }
    }
  });
