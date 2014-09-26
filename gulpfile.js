var gulp = require('gulp');
var concat = require('gulp-concat-util');
var clean = require('gulp-clean');
var uglify = require('gulp-uglify');
var imagemin = require('gulp-imagemin');
var minify_css = require('gulp-minify-css');
var sass = require('gulp-sass');
var es = require('event-stream');

var bower_js = [
    './bower_components/jquery/dist/jquery.js',
    './bower_components/bootstrap/dist/js/bootstrap.js',
];

var bower_css = [
    './bower_components/bootstrap/dist/css/bootstrap.css',
];

var bower_copy = [
    './bower_components/bootstrap/dist/fonts/**/*',
];

gulp.task('clean', function () {
    gulp.src(['web/js', 'web/images', 'web/css'])
        .pipe(clean());
});

gulp.task('images', function () {
    gulp.src(['frontend/images/*'])
        .pipe(imagemin())
        .pipe(gulp.dest('web/images'));
});

gulp.task('css', function () {
    var css = gulp.src(bower_css.concat(['frontend/css/*.css']));

    var scss = gulp.src('frontend/scss/main.scss')
        .pipe(sass());

    es.concat(css, scss)
        .pipe(concat('app.css'))
        .pipe(minify_css({keepSpecialComments: 0}))
        .pipe(gulp.dest('web/css'))
});

gulp.task('js', function () {
    gulp.src(bower_js.concat(['frontend/js/*.js']))
        .pipe(concat('app.js'))
        .pipe(uglify())
        .pipe(gulp.dest('web/js'));
});

gulp.task('copy', function () {
    gulp.src(bower_copy, {base: './bower_components/bootstrap/dist/'})
        .pipe(gulp.dest('web'));
});

gulp.task('watch', function () {
    gulp.watch(bower_js.concat(['frontend/js/*.js']), ['js']);
    gulp.watch(['frontend/images/*'], ['images']);
    gulp.watch(bower_css.concat(['frontend/css/*.css']).concat(['frontend/scss/*.scss']), ['css']);
    gulp.watch(bower_copy, ['copy']);
});

gulp.task('default', ['clean', 'images', 'css', 'copy', 'js']);

gulp.task('debug', ['clean', 'images', 'css', 'copy', 'js', 'watch']);