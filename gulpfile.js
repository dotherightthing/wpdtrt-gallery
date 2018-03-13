/**
 * Gulp Task Runner
 * Compile front-end resources
 *
 * Based on the gulpfile from WPDTRT_Plugin, which this plugin predates.
 *
 * @package     WPDTRT_Gallery
 * @since       1.2.0
 * @version     1.0.0
 */

/* global require */

// dependencies
var gulp = require('gulp');
var autoprefixer = require('autoprefixer');
var del = require('del');
var jshint = require('gulp-jshint');
var phplint = require('gulp-phplint');
var postcss = require('gulp-postcss');
var pxtorem = require('postcss-pxtorem');
var runSequence = require('run-sequence');
var sass = require('gulp-sass');
var zip = require('gulp-zip');

var cssDir = 'css';
var distDir = 'dist';
var jsFiles = './js/*.js';
var phpFiles = [
  './**/*.php',
  '!vendor/**/*',
  '!node_modules/**/*'
];
var scssFiles = './scss/*.scss';

// tasks

gulp.task('css', function () {

  var processors = [
      autoprefixer({
        cascade: false
      }),
      pxtorem({
        rootValue: 16,
        unitPrecision: 5,
        propList: [
          'font',
          'font-size',
          'padding',
          'padding-top',
          'padding-right',
          'padding-bottom',
          'padding-left',
          'margin',
          'margin-top',
          'margin-right',
          'margin-bottom',
          'margin-left',
          'bottom',
          'top',
          'max-width'
        ],
        selectorBlackList: [],
        replace: false,
        mediaQuery: true,
        minPixelValue: 0
      })
  ];

  return gulp
    .src(scssFiles)
    .pipe(sass({outputStyle: 'expanded'}))
    .pipe(postcss(processors))
    .pipe(gulp.dest(cssDir));
});

// works best when run at the start rather than the end of the dist task
gulp.task('delDist', function () {
  return del([
    cssDir,
    distDir,
    'release.zip'
  ]);
});

gulp.task('js', function() {
  return gulp
    .src(jsFiles)

    // validate JS
    .pipe(jshint())
    .pipe(jshint.reporter('default', { verbose: true }))
    .pipe(jshint.reporter('fail'));
});

gulp.task('phplint', function () {
  return gulp
    .src(phpFiles)

    // validate PHP
    // The linter ships with PHP
    .pipe(phplint())
    .pipe(phplint.reporter(function(file){
      var report = file.phplintReport || {};

      if (report.error) {
        console.log(report.message+' on line '+report.line+' of '+report.filename);
      }
    }));
});

gulp.task('watch', function () {
  gulp.watch( scssFiles, ['scss'] );
  gulp.watch( jsFiles, ['js'] );
});

gulp.task('zipDist', function() {
  gulp.src([
    './app/**/*',
    './config/**/*',
    './css/**/*',
    './js/**/*',
    './languages/**/*',
    './templates/**/*',
    './index.php',
    './readme.txt',
    './uninstall.php',
    './wpdtrt-gallery.php'
  ], { base: '.' })
  .pipe(gulp.dest(distDir))
  .pipe(zip('release.zip'))
  .pipe(gulp.dest('./'))
});

gulp.task( 'build', [
    'phplint',
    'css',
    'js'
  ]
);

gulp.task('default', function(callback) {
  runSequence(
    'build',
    'watch'
  );
});

gulp.task('dist', function(callback) {
  runSequence(
    'delDist',
    'build',
    'zipDist'
  );
});
