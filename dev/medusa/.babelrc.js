let ignore = [`**/dist`, "**/medusa-plugin-wordpress"];

// Jest needs to compile this code, but generally we don't want this copied
// to output folders
if (process.env.NODE_ENV !== `test`) {
  ignore.push(`**/__tests__`);
}

/**
 * Define typescript type for `.babelrc.js` file
 * @type {import('@babel/core').TransformOptions | import('@babel/core').ConfigFunction}
 */
module.exports = {
  presets: [["babel-preset-medusa-package"], ["@babel/preset-typescript"]],
  plugins: [["@babel/plugin-proposal-decorators", { "legacy": true }]],
  ignore,
};
