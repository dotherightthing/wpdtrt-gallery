/**
 * gulpfile.js
 * Run tasks to aid the development process.
 * @example gulp default
 */
var gulp = require('gulp');
var phplint = require('gulp-phplint');

gulp.task('default', function () {
  return gulp.src(['**/*.php'])

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
