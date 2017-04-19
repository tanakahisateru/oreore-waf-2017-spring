var gulp = require('gulp');
var pump = require('pump');
var del = require('del');
var concat = require('gulp-concat');
// var urlAdjuster = require('gulp-css-url-adjuster');
var uglifycss = require('gulp-uglifycss');
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");
var sourcemaps = require('gulp-sourcemaps');
var rev = require('gulp-rev');
var revReplace = require('gulp-rev-replace');

var basedir = './web/assets';

gulp.task('default', ['vendor']);

gulp.task('build', ['fonts', 'css', 'js']);
gulp.task('dist', ['rev-replace']);

gulp.task('clean', function(cb) {
    return del([
        basedir + '/dist',
        basedir + '/vendor'
    ]);
});

gulp.task('vendor', ['clean'], function(cb) {
    pump([
        gulp.src('./node_modules/+(jquery|bootstrap)/dist/**/*', { base: './node_modules/' }),
        gulp.dest(basedir + '/vendor')
    ], cb);
});

gulp.task('fonts', ['vendor'], function(cb) {
    pump([
        gulp.src(basedir + '/vendor/bootstrap/dist/fonts/*'),
        gulp.dest(basedir + '/dist/fonts')
    ], cb);
});

gulp.task('css', ['vendor'], function(cb) {
    pump([
        gulp.src([
            basedir + '/vendor/bootstrap/dist/css/bootstrap.css',
            basedir + '/vendor/bootstrap/dist/css/bootstrap-theme.css',
            basedir + '/local/app.css'
        ], { base: basedir }),
        sourcemaps.init(),

        concat('all.css'),
        // urlAdjuster({
        //     replace:  ['../', '../vendor/bootstrap/dist/']
        // }),
        // gulp.dest(basedir + '/dist/css'),

        uglifycss(),
        rename({ extname: '.min.css'}),
        //gulp.dest(basedir + '/dist/css'),
        sourcemaps.write('.', {includeContent: false, sourceRoot: '../../'}),
        gulp.dest(basedir + '/dist/css')
    ], cb);
});

gulp.task('js', ['vendor'], function(cb) {
    pump([
        gulp.src([
            basedir + '/vendor/jquery/dist/jquery.js',
            basedir + '/vendor/bootstrap/dist/js/bootstrap.js',
            basedir + '/local/app.js'
        ], { base: basedir }),
        sourcemaps.init(),

        concat('all.js'),
        // gulp.dest(basedir + '/dist/js'),

        uglify(),
        rename({ extname: '.min.js'}),
        sourcemaps.write('.', {includeContent: false, sourceRoot: '../../'}),
        gulp.dest(basedir + '/dist/js')
    ], cb);
});

gulp.task('rev', ['build'], function(cb) {
    pump([
        // gulp.src(basedir + '/dist/**/*.+(js|css|png|gif|jpg|jpeg|svg|woff|woff2|ttf|eot|ico)'),
        gulp.src(basedir + '/dist/**/*'),

        rev(),
        gulp.dest(basedir + '/dist'),

        rev.manifest(),
        gulp.dest(basedir + '/dist')
    ], cb);
});

gulp.task('rev-replace', ['rev'], function(cb) {
    pump([
        gulp.src(basedir + '/dist/**/*.+(js|css)'),
        revReplace({manifest: gulp.src(basedir + '/dist/rev-manifest.json')}),
        gulp.dest(basedir + '/dist')
    ], cb);
});
