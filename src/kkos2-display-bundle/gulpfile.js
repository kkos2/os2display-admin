/* jshint node: true */
"use strict";

const fs = require('fs');
const del = require("del");
const gulp = require("gulp");
const concat = require("gulp-concat");
const uglify = require('gulp-uglify-es').default;
const rename = require("gulp-rename");
const sass = require('gulp-sass');

/**
 * Get an array of dir names in a dir.
 *
 * @param {string} source The directory to find directory names in.
 * @returns {string[]} Array of strings with directory names.
 */
const dirsInDir = source => fs.readdirSync(source, {withFileTypes: true})
  .filter(c => fs.statSync(source + '/' + c).isDirectory());

const scssDir = "Resources/public/assets/scss";
const slidesPath = "Resources/public/templates/slides";
const screensPath = "Resources/public/templates/screens";
const distDir = "Resources/public/dist";
const distJs = `${distDir}/js`;
const toolsDir = "Resources/public/apps/tools";
const slideFolders = dirsInDir(slidesPath);
const screenFolders = dirsInDir(screensPath);

/**
 * Delete the generated minified files.
 */
function clean() {
  return del([
    `${distJs}/*.min.js`,
    `${slidesPath}/**/*.min.css`,
    `${screensPath}/**/*.min.css`,
  ]);
}

/**
 * Compile the JS displaying the slides on the front end.
 */
const compileSlidesJs = () => {
  slideFolders.map(function (item) {
    const fileName = item.split("/").pop() + ".js";
    // Prepend slides-in-slide.js to all files. There is no way to include more
    // than one js file at the time, so it has to be baked in.
    gulp.src([
      "Resources/public/assets/js/kk-slide-ratio.js",
      "../../vendor/reload/os2display-slide-tools/Resources/public/js/slides-in-slide.js",
      `${slidesPath}/${item}/${fileName}`
    ])
      .pipe(concat(fileName))
      .pipe(uglify()).on('error', function(e){
        console.log(e);
      })
      .pipe(rename({extname: ".min.js"}))
      .pipe(gulp.dest(distJs));
  });

  return new Promise(function (resolve) {
    resolve();
  });
};

/**
 * Compile the JS for the tools used in the admin interface.
 */
const compileToolsJs = () => {
  return gulp.src(`${toolsDir}/*.js`)
    .pipe(concat('kff-tools.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest(distJs));
};

/**
 * Compile all .scss files in the slides folder.
 */
const compileScss = () => {
  slideFolders.map(function (item) {
    const fileName = item.split("/").pop() + ".scss";
    gulp.src(`${slidesPath}/${item}/${fileName}`)
      .pipe(sass({
        outputStyle: 'compressed'
      }).on('error', sass.logError))
      .pipe(rename({extname: ".min.css"}))
      .pipe(gulp.dest(`${slidesPath}/${item}`));
  });

  screenFolders.map(function (item) {
    const fileName = item.split("/").pop() + ".scss";
    gulp.src(`${screensPath}/${item}/${fileName}`)
      .pipe(sass({
        outputStyle: 'compressed'
      }).on('error', sass.logError))
      .pipe(rename({extname: ".min.css"}))
      .pipe(gulp.dest(`${screensPath}/${item}`));
  });

  return new Promise(function (resolve) {
    resolve();
  });
};

/**
 * Watch for changes to JS and CSS.
 */
function watchChanges() {
  gulp.watch(`${toolsDir}/*.js`, compileToolsJs);
  gulp.watch(`${slidesPath}/**/*.js`, compileSlidesJs);
  gulp.watch(`${scssDir}/**/*.scss`, compileScss);
  gulp.watch(`${slidesPath}/**/*.scss`, compileScss);
}

const compile = gulp.parallel(compileSlidesJs, compileToolsJs, compileScss);
compile.description = 'Compile all';

const all = gulp.series(clean, compile);
all.description = 'Clean and compile all';

const watch = gulp.series(clean, compile, watchChanges);

exports.watch = watch;
exports.compile = compile;
exports.all = all;

exports.default = all;
