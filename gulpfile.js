/**
 * Gulp Task Runner
 * Compile front-end resources
 *
 * Based on the gulpfile from WPDTRT_Plugin, which this plugin predates.
 *
 * @package     WPDTRT_Gallery
 * @since       1.2.0
 * @version     1.1.2
 */

/* global require */

// dependencies

var gulp = require('gulp');
var autoprefixer = require('autoprefixer');
var bump = require('gulp-bump');
var del = require('del');
var filter = require('gulp-filter');
// `fs` is used instead of require to prevent caching in watch (require caches)
var fs = require('fs');
var jshint = require('gulp-jshint');
var log = require('fancy-log');
var phplint = require('gulp-phplint');
var postcss = require('gulp-postcss');
var print = require('gulp-print').default;
var pxtorem = require('postcss-pxtorem');
var runSequence = require('run-sequence');
var sass = require('gulp-sass');
var semver = require('semver');
var shell = require('gulp-shell');
var zip = require('gulp-zip');

// paths

var cssDir = 'css';
var distDir = 'wpdtrt-gallery';
var dummyFile = 'README.md';
var jsFiles = './js/*.js';
var phpFiles = [
  './**/*.php',
  '!node_modules/**/*',
  '!vendor/**/*',
  '!wpdtrt-gallery/**/*' // release folder
];
var scssFiles = './scss/*.scss';

// config

var getPackageJson = function () {
  return JSON.parse(fs.readFileSync('./package.json', 'utf8'));
};

// tasks

gulp.task('bower', function () {

  log(' ');
  log('========== 1. bower ==========');
  log(' ');

  // return stream or promise for run-sequence
  return gulp.src(dummyFile, {read: false})
    .pipe(shell([
      'bower install'
    ])
  );
});

// bump versions on package/bower/manifest
gulp.task('bump', function () {

  log(' ');
  log('========== bump version ==========');
  log(' ');

  // reget package
  var pkg = getPackageJson();

  // increment version
  var newVer = semver.inc(pkg.version, 'patch');
 
  // uses gulp-filter
  var jsonFilter = filter('*.json', {restore: true});
  var phpFilter = filter('*.php', {restore: true});
  var txtFilter = filter('*.txt', {restore: true});
  var php_constant = "WPDTRT_GALLERY_VERSION";
 
  log("Please manually bump readme.txt - Stable tag: " + newVer);
  log("Please manually bump wpdtrt-gallery.php - define( 'WPDTRT_GALLERY_VERSION', '" + newVer + "' )");

  return gulp.src([
      './bower.json',
      './package.json',
      './package-lock.json',
      './readme.txt',
      './wpdtrt-gallery.php'
    ])

    // bower.json, package.json, package-lock.json
    // "Error: Invalid semver: version key "version" is not found in file" if file is empty.
    .pipe(jsonFilter)
    .pipe(bump({ version: newVer }))
    .pipe(gulp.dest('./'))
    .pipe(jsonFilter.restore)

    // wpdtrt-gallery.php (header)
    .pipe(phpFilter)
    .pipe(bump())
    .pipe(gulp.dest('./'));
    //.pipe(phpFilter.restore)

    // wpdtrt-gallery.php
    // define( 'WPDTRT_GALLERY_VERSION', '1.6.6' );
    // TODO Not working
    /*
    .pipe(phpFilter)
    .pipe(bump({
      regex: new RegExp( "([<|\'|\"]?"+php_constant+"[>|\'|\"]?[ ]*[:=,]?[ ]*[\'|\"]?[a-z]?)(\\d+\\.\\d+\\.\\d+)(-[0-9A-Za-z\.-]+)?([\'|\"|<]?)", "i" ),
    }))
    .pipe(gulp.dest('./'))
    .pipe(phpFilter.restore)
    */

    // readme.txt
    // Stable tag: 1.6.6
    // TODO Not working
    /*
    .pipe(txtFilter)
    .pipe(bump({
      // find a version string with the format key: value
      // to match the pair that we would usually pass in
      regex: new RegExp( "(Stable\s+tag:\s)+([0-9].[0-9].[0-9]+)" )
    }))
    .pipe(gulp.dest('./'));
    */
});

gulp.task('composer', function () {

  log(' ');
  log('========== 2. composer ==========');
  log(' ');

  // return stream or promise for run-sequence
  return gulp.src(dummyFile, {read: false})
    .pipe(shell([
      'composer install --prefer-dist --no-interaction'
    ])
  );
});

gulp.task('css', function () {

  log(' ');
  log('========== 3. css ==========');
  log(' ');

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

  // return stream or promise for run-sequence
  return gulp.src(scssFiles)
    .pipe(sass({outputStyle: 'expanded'}))
    .pipe(postcss(processors))
    .pipe(gulp.dest(cssDir));
});

gulp.task('finish', function () {

  log(' ');
  log('========== All Tasks Complete ==========');
  log(' ');
});

gulp.task('js', function() {

  log(' ');
  log('========== 4. js =========='); // validate JS
  log(' ');

  // return stream or promise for run-sequence
  return gulp.src(jsFiles)
    .pipe(jshint())
    .pipe(jshint.reporter('default', { verbose: true }))
    .pipe(jshint.reporter('fail'));
});

gulp.task('list_files', function() {

  log(' ');
  log('========== 8. list_files ==========');
  log(' ');

  // return stream or promise for run-sequence
  return gulp.src('./*')
    .pipe(print());
});

gulp.task('phpdoc_delete', function () {

  log(' ');
  log('========== 6a. phpdoc_delete ==========');
  log(' ');

  // return stream or promise for run-sequence
  return del([
    'docs/phpdoc'
  ]);
});

gulp.task('phpdoc_doc', function() {

  log(' ');
  log('========== 6c. phpdoc_doc ==========');
  log(' ');

  // return stream or promise for run-sequence
  // note: src files are not used,
  // this structure is only used
  // to include the preceding log()
  return gulp.src(dummyFile, {read: false})
    .pipe(shell([
      'vendor/bin/phpdoc -d . -t ./docs/phpdoc'
    ])
  );
});

gulp.task('phpdoc_tgmpa', function() {

  log(' ');
  log('========== 6d. phpdoc_tgmpa ==========');
  log(' ');

  // return stream or promise for run-sequence
  // note: src files are not used,
  // this structure is only used
  // to include the preceding log()
  return gulp.src(dummyFile, {read: false})
    .pipe(shell([
      // install plugin which generates Fatal Error (#12)
      // if previously installed via package.json
      'composer require tgmpa/tgm-plugin-activation:2.6.*'
    ])
  );
});

gulp.task('phpdoc', function(callback) {

  log(' ');
  log('========== 6. phpdoc ==========');
  log(' ');

  // return?
  runSequence(
    'phpdoc_delete',
    'phpdoc_doc',
    'phpdoc_tgmpa',
    callback
  );
});

gulp.task('phplint', function () {

  log(' ');
  log('========== 5. phplint ==========');
  log(' ');

  // return stream or promise for run-sequence
  return gulp.src(phpFiles)

    // validate PHP
    // The linter ships with PHP
    .pipe(phplint())
    .pipe(phplint.reporter(function(file) {
      var report = file.phplintReport || {};

      if (report.error) {
        log.error(report.message+' on line '+report.line+' of '+report.filename);
      }
    }));
});

gulp.task('release_delete_pre', function () {

  log(' ');
  log('========== 7a. release_delete_pre ==========');
  log(' ');

  // return stream or promise for run-sequence
  return del([
    'release.zip'
  ]);
});

gulp.task('remove_dev_dependencies', function() {

  log(' ');
  log('========== remove_dev_dependencies ==========');
  log(' ');

  /**
   * Remove dev packages once we've used them
   * @see https://github.com/dotherightthing/wpdtrt-plugin/issues/47
   */
  return gulp.src(dummyFile, {read: false})
    .pipe(shell([
      'composer install --prefer-dist --no-interaction --no-dev'
    ])
  );
});

gulp.task('release_delete_post', function () {

  log(' ');
  log('========== 7d. release_delete_post ==========');
  log(' ');

  // return stream or promise for run-sequence
  return del([
    cssDir,
    distDir // wpdtrt-plugin
  ]);
});

gulp.task('release_copy', function() {

  log(' ');
  log('========== 7b. release_copy ==========');
  log(' ');

  // return stream or promise for run-sequence
  // https://stackoverflow.com/a/32188928/6850747
  return gulp.src([
    './app/**/*',
    './config/**/*',
    './css/**/*',
    './docs/**/*',
    './js/**/*',
    './languages/**/*',
    './templates/**/*',
    './vendor/**/*',
    './index.php',
    './readme.txt',
    './uninstall.php',
    './wpdtrt-gallery.php'
  ], { base: '.' })
  .pipe(gulp.dest(distDir));
});

gulp.task('release_zip', function() {

  log(' ');
  log('========== 7c. release_zip ==========');
  log(' ');

  // return stream or promise for run-sequence
  // https://stackoverflow.com/a/32188928/6850747
  return gulp.src([
    './' + distDir + '/**/*'
  ], { base: '.' })
  .pipe(zip('release.zip'))
  .pipe(gulp.dest('./'));
});

gulp.task('release', function(callback) {

  log(' ');
  log('========== 7. release ==========');
  log(' ');

  runSequence(
    'release_delete_pre',
    'release_copy',
    'release_zip',
    'release_delete_post',
    callback
  );
});

gulp.task('start', function () {

  log(' ');
  log('========== Tasks Started ==========');
  log(' ');
});

gulp.task('watch', function () {

  log(' ');
  log('========== watch ==========');
  log(' ');

  gulp.watch( scssFiles, ['css'] );
  gulp.watch( jsFiles, ['js'] );
  gulp.watch( phpFiles, ['phplint'] );
});

gulp.task('default', [
    'composer',
    'bower',
    'css',
    'js',
    'phplint',
    'watch'
  ]
);

gulp.task ('maintenance', function(callback) {
  runSequence(
    'start',
    'bower', // 1
    'composer', // 2
    'css', // 3
    'js', // 4
    'phplint', // 5
    'phpdoc', // 6
    'remove_dev_dependencies',
    'release', // 7
    'list_files', // 8
    'finish'
  );

  callback();
});

gulp.task ('dist', [
    'maintenance'
  ]
);
