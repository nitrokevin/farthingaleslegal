"use strict";

import plugins from "gulp-load-plugins";
import yargs from "yargs";
import browser from "browser-sync";
import gulp from "gulp";
import fs from "fs";
import yaml from "js-yaml";
import rimraf from "rimraf"; // Consider replacing with fs.rmSync or del if necessary
import webpackStream from "webpack-stream";
import webpack2 from "webpack";
import named from "vinyl-named";
import log from "fancy-log";
import colors from "ansi-colors";
import dartSass from 'sass';
import gulpSass from 'gulp-sass';
import {
  exec
} from 'child_process';

const sass = gulpSass(dartSass);
// Load all Gulp plugins into one variable
const $ = plugins();

// Check for --production flag
const PRODUCTION = !!yargs.argv.production;

// Load settings from config.yml
const {
  BROWSERSYNC,
  COMPATIBILITY,
  REVISIONING,
  PATHS
} = loadConfig();

// Check if file exists synchronously
function checkFileExists(filepath) {
  let flag = true;
  try {
    fs.accessSync(filepath, fs.F_OK);
  } catch (e) {
    flag = false;
  }
  return flag;
}

// Load default or custom YML config file
function loadConfig() {
  log("Loading config file...");

  if (checkFileExists("config.yml")) {
    log(colors.bold(colors.cyan("config.yml")), "exists, loading", colors.bold(colors.cyan("config.yml")));
    let ymlFile = fs.readFileSync("config.yml", "utf8");
    return yaml.load(ymlFile);
  } else if (checkFileExists("config-default.yml")) {
    log(colors.bold(colors.cyan("config.yml")), "does not exist, loading", colors.bold(colors.cyan("config-default.yml")));
    let ymlFile = fs.readFileSync("config-default.yml", "utf8");
    return yaml.load(ymlFile);
  } else {
    log("Exiting process, no config file exists.");
    process.exit(1);
  }
}

// Delete the "dist" folder
function clean(done) {
  rimraf(PATHS.dist, done); // Consider using fs.rmSync or del for modern environments
}

// Copy files out of the assets folder
function copy() {
  return gulp.src(PATHS.assets).pipe(gulp.dest(PATHS.dist + "/assets"));
}

// Compile Sass into CSS
function styles() {
  return (
    gulp.src(['src/assets/scss/app.scss', 'src/assets/scss/editor.scss'])
    .pipe($.sourcemaps.init())
    .pipe(
      sass.sync({
        includePaths: PATHS.sass
      })
      .on("error", sass.logError) // Improved error logging
    )
    .pipe($.autoprefixer())
    .pipe($.if(PRODUCTION, $.cleanCss({
      compatibility: "ie11"
    })))
    .pipe($.if(!PRODUCTION, $.sourcemaps.write()))
    .pipe($.if((REVISIONING && PRODUCTION) || (REVISIONING && !!yargs.argv.dev), $.rev()))
    .pipe(gulp.dest(PATHS.dist + "/assets/css"))
    .pipe(
      $.if(
        (REVISIONING && PRODUCTION) || (REVISIONING && !!yargs.argv.dev),
        $.rev.manifest()
      )
    )
    .pipe(gulp.dest(PATHS.dist + "/assets/css"))
    .pipe(browser.reload({
      stream: true
    }))
  );
}
exports.styles = styles;

// New task to run update-theme-json.js script
function updateThemeJson(done) {
  exec('node build/update-theme-json.js', function (err, stdout, stderr) {
    if (err) {
      log.error('[updateThemeJson:error]', err);
      done(err);
      return;
    }
    if (stdout) {
      log('[updateThemeJson]', stdout);
    }
    if (stderr) {
      log('[updateThemeJson]', stderr);
    }
    done();
  });
}
gulp.task('updateThemeJson', updateThemeJson);

// Base webpack configuration
const baseWebpackConfig = {
  module: {
    rules: [{
        test: /\.js$/,
        loader: "babel-loader",
        exclude: /node_modules(?![\\\/]foundation-sites)/,
      },
      {
        test: /\.scss$/,
        use: ["style-loader", "css-loader", "sass-loader"], // SCSS loader
      },
      {
        test: /\.css$/,
        use: ["style-loader", "css-loader"], // CSS loader
      },
    ],
  },
  mode: PRODUCTION ? "production" : "development",
  externals: {
    jquery: "jQuery",
  },
};

// Frontend webpack configuration
const webpack = {
  config: baseWebpackConfig,

  changeHandler(err, stats) {
    if (err) {
      log.error("[webpack:error]", err.toString({
        colors: true
      }));
    } else {
      log(
        "[webpack]",
        stats.toString({
          colors: true,
        })
      );
      browser.reload();
    }
  },

  build() {
    return gulp
      .src(PATHS.entries)
      .pipe(named())
      .pipe(webpackStream(webpack.config, webpack2))
      .pipe(
        $.if(
          PRODUCTION,
          $.uglify().on("error", (e) => {
            log.error("[uglify:error]", e);
          })
        )
      )
      .pipe($.if((REVISIONING && PRODUCTION) || (REVISIONING && !!yargs.argv.dev), $.rev()))
      .pipe(gulp.dest(PATHS.dist + "/assets/js"))
      .pipe(
        $.if(
          (REVISIONING && PRODUCTION) || (REVISIONING && !!yargs.argv.dev),
          $.rev.manifest()
        )
      )
      .pipe(gulp.dest(PATHS.dist + "/assets/js"));
  },

  watch() {
    const watchConfig = Object.assign({}, webpack.config, {
      watch: true,
      devtool: "inline-source-map",
    });

    return gulp
      .src(PATHS.entries)
      .pipe(named())
      .pipe(
        webpackStream(watchConfig, webpack2, webpack.changeHandler).on(
          "error",
          (err) => {
            log.error("[webpack:error]", err.toString({
              colors: true
            }));
          }
        )
      )
      .pipe(gulp.dest(PATHS.dist + "/assets/js"));
  },
};

// Editor webpack configuration (for Gutenberg blocks)
const webpackEditor = {
  config: {
    ...baseWebpackConfig,
    externals: {
      jquery: "jQuery",
      // WordPress dependencies as externals (don't bundle them)
      '@wordpress/blocks': ['wp', 'blocks'],
      '@wordpress/element': ['wp', 'element'],
      '@wordpress/i18n': ['wp', 'i18n'],
      '@wordpress/components': ['wp', 'components'],
      '@wordpress/compose': ['wp', 'compose'],
      '@wordpress/block-editor': ['wp', 'blockEditor'],
      '@wordpress/data': ['wp', 'data'],
      '@wordpress/hooks': ['wp', 'hooks'],
      'wp-blocks': ['wp', 'blocks'],
      'wp-element': ['wp', 'element'],
      'wp-i18n': ['wp', 'i18n'],
      'wp-components': ['wp', 'components'],
      'wp-compose': ['wp', 'compose'],
      'wp-block-editor': ['wp', 'blockEditor'],
      'wp-data': ['wp', 'data'],
      'wp-hooks': ['wp', 'hooks'],
    },
  },

  changeHandler(err, stats) {
    if (err) {
      log.error("[webpack-editor:error]", err.toString({
        colors: true
      }));
    } else {
      log(
        "[webpack-editor]",
        stats.toString({
          colors: true,
        })
      );
      browser.reload();
    }
  },

  build() {
    return gulp
      .src(PATHS.editorEntries || 'src/assets/js/editor.js')
      .pipe(named())
      .pipe(webpackStream(webpackEditor.config, webpack2))
      .pipe(
        $.if(
          PRODUCTION,
          $.uglify().on("error", (e) => {
            log.error("[uglify-editor:error]", e);
          })
        )
      )
      .pipe($.if((REVISIONING && PRODUCTION) || (REVISIONING && !!yargs.argv.dev), $.rev()))
      .pipe(gulp.dest(PATHS.dist + "/assets/js"))
      .pipe(
        $.if(
          (REVISIONING && PRODUCTION) || (REVISIONING && !!yargs.argv.dev),
          $.rev.manifest()
        )
      )
      .pipe(gulp.dest(PATHS.dist + "/assets/js"));
  },

  watch() {
    const watchConfig = Object.assign({}, webpackEditor.config, {
      watch: true,
      devtool: "inline-source-map",
    });

    return gulp
      .src(PATHS.editorEntries || 'src/assets/js/editor.js')
      .pipe(named())
      .pipe(
        webpackStream(watchConfig, webpack2, webpackEditor.changeHandler).on(
          "error",
          (err) => {
            log.error("[webpack-editor:error]", err.toString({
              colors: true
            }));
          }
        )
      )
      .pipe(gulp.dest(PATHS.dist + "/assets/js"));
  },
};

gulp.task("webpack:build", webpack.build);
gulp.task("webpack:watch", webpack.watch);
gulp.task("webpack-editor:build", webpackEditor.build);
gulp.task("webpack-editor:watch", webpackEditor.watch);

// Copy images to the "dist" folder
function images() {
  return gulp
    .src("src/assets/images/**/*")
    .pipe(
      $.if(
        PRODUCTION,
        $.imagemin([
          $.imagemin.mozjpeg({
            progressive: true
          }),
          $.imagemin.optipng({
            optimizationLevel: 5
          }),
          $.imagemin.gifsicle({
            interlaced: true
          }),
          $.imagemin.svgo({
            plugins: [{
                cleanupAttrs: true
              },
              {
                removeComments: true
              }
            ]
          }),
        ])
      )
    )
    .pipe(gulp.dest(PATHS.dist + "/assets/images"));
}

// Start BrowserSync to preview the site
function server(done) {
  browser.init({
    proxy: BROWSERSYNC.url,
    ui: {
      port: 8080
    },
  });
  done();
}

// Reload the browser
function reload(done) {
  browser.reload();
  done();
}

// Watch for changes (updated to include editor JS)
function watch() {
  gulp.watch(PATHS.assets, copy);
  gulp.watch("src/assets/scss/**/*.scss", gulp.series(updateThemeJson, styles))
    .on("change", (path) => log("File " + colors.bold(colors.magenta(path)) + " changed."))
    .on("unlink", (path) => log("File " + colors.bold(colors.magenta(path)) + " was removed."));
  gulp.watch("**/*.php", reload)
    .on("change", (path) => log("File " + colors.bold(colors.magenta(path)) + " changed."))
    .on("unlink", (path) => log("File " + colors.bold(colors.magenta(path)) + " was removed."));
  gulp.watch("src/assets/images/**/*", gulp.series(images, reload));

  // Watch editor JS files
  gulp.watch("src/assets/js/editor/**/*.js", gulp.series("webpack-editor:build", reload))
    .on("change", (path) => log("Editor file " + colors.bold(colors.magenta(path)) + " changed."))
    .on("unlink", (path) => log("Editor file " + colors.bold(colors.magenta(path)) + " was removed."));
}

// Build the "dist" folder by running all of the below tasks (updated to include editor build)
gulp.task("build", gulp.series(
  updateThemeJson,
  clean,
  gulp.parallel(styles, "webpack:build", "webpack-editor:build", images, copy)
));

// Build the site, run the server, and watch for file changes (updated to include editor watch)
gulp.task("default", gulp.series(
  "build",
  server,
  gulp.parallel("webpack:watch", "webpack-editor:watch", watch)
));