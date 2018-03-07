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
var phplint = require('gulp-phplint');
var postcss = require('gulp-postcss');
var pxtorem = require('postcss-pxtorem');
var sass = require('gulp-sass');

var scssDir = './scss/*.scss';
var cssDir = './css/';
var phpDir = [
  './**/*.php',
  '!vendor/**/*',
  '!node_modules/**/*'
];

// tasks

gulp.task('scss', function () {

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
    .src(scssDir)
    .pipe(sass({outputStyle: 'expanded'}))
    .pipe(postcss(processors))
    .pipe(gulp.dest(cssDir));
});

gulp.task('phplint', function () {
  return gulp
    .src(phpDir)

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
  gulp.watch( scssDir, ['scss'] );
});

gulp.task( 'default', [
    'phplint',
    'scss',
    'watch'
  ]
);
