/**
 * Created by brett on 07/05/16.
 */

var gulp = require('gulp');

var sass = require('gulp-sass');

var useref = require('gulp-useref');

var browserSync = require('browser-sync').create();

var uglify = require('gulp-uglify');

var cssnano = require('gulp-cssnano');

var gulpIf = require('gulp-if');

var imagemin = require('gulp-imagemin');

var runSequence = require('run-sequence');

var del = require('del');


gulp.task('sass', function () {
    return gulp.src('app/sass/**.sass')
        .pipe(sass())
        .pipe(gulp.dest('app/css'))
        .pipe(browserSync.reload({
            stream: true
        }))
});

gulp.task('copyHtml', function() {
    return gulp.src('app/**')
        .pipe(gulp.dest('dist/'))
});

gulp.task('webSpin', function () {
    browserSync.init({
        server: {
            baseDir: 'app'
        }
    })
});

gulp.task('useref', function(){
    return gulp.src('app/*.html')
        .pipe(useref())
        // Minifies only if it's a JavaScript file
        .pipe(gulpIf('*.js', uglify()))
        .pipe(gulpIf('*.css', cssnano()))
        .pipe(gulp.dest('dist'))
});

gulp.task('images', function(){
    return gulp.src('app/images/**/*.+(png|jpg|jpeg|gif|svg)')
        .pipe(imagemin({
            interlaced: true
        }))
        .pipe(gulp.dest('dist/images'))
});

gulp.task('clean', function() {
    return del.sync('dist');
});

gulp.task('default', function (callback) {
    runSequence(['sass','webSpin', 'watch'],
        callback
    )
});

gulp.task('build', function (callback) {
    runSequence('clean',
        ['sass', 'useref', 'images', 'copyHtml'],
        callback
    )
});

gulp.task('watch', ['webSpin', 'sass'], function (){
    gulp.watch('app/sass/**/*.scss', ['sass']);
    gulp.watch('app/*.html', browserSync.reload);
    gulp.watch('app/js/**.js', browserSync.reload);
});