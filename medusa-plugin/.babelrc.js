const ignore = [];

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
  presets: ["@babel/preset-env", "@babel/preset-typescript"],
  plugins: [
    ["@babel/plugin-proposal-decorators", { legacy: true }],
    ["@babel/plugin-transform-private-methods", { loose: true }],
    ["@babel/plugin-transform-private-property-in-object", { loose: true }],
    ["@babel/plugin-proposal-class-properties", { loose: true }],
  ],
  ignore,
};
