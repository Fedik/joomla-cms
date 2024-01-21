/**
 * Build Flatpickr modules
 */
/* eslint-disable import/no-extraneous-dependencies, global-require, import/no-dynamic-require */

const { readFileSync, writeFile } = require('fs-extra');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { transform } = require('esbuild');

// Build the module
const buildModule = async (module, externalModules, destFile) => {
  const build = await rollup.rollup({
    input: module,
    external: externalModules || [],
    plugins: [
      nodeResolve(),
      replace({
        preventAssignment: true,
        'process.env.NODE_ENV': '"production"',
      }),
    ],
    onwarn(warning, handler) {
      // Skip certain warnings

      // Ignore: The 'this' keyword is equivalent to 'undefined'
      if (warning.code === 'THIS_IS_UNDEFINED') { return; }

      // console.warn everything else
      handler(warning);
    },
  });

  await build.write({
    format: 'es',
    sourcemap: false,
    file: destFile,
  });
  await build.close();
};

// Minify a js file
const createMinified = async (filePath) => {
  const destFile = filePath.replace('.js', '.min.js');
  // Read source
  const src = readFileSync(filePath, { encoding: 'utf8' });
  // Minify
  const min = await transform(src, { minify: true });
  // Save result
  await writeFile(destFile, min.code, { encoding: 'utf8', mode: 0o644 });
};

module.exports.compileFlatpickr = async () => {
  // eslint-disable-next-line no-console
  console.log('Building Flatpickr Component...');

  const module = 'flatpickr';
  const destPath = 'media/vendor/flatpickr/js/flatpickr.js';

  return buildModule(module, [], destPath).then(() => {
    return createMinified(destPath);
  });
};
