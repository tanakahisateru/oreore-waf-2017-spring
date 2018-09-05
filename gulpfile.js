const gulp = require('gulp');
const del = require('del');
const concat = require('gulp-concat');
// const urlAdjuster = require('gulp-css-url-adjuster');
const uglifycss = require('gulp-uglifycss');
const uglify = require('gulp-uglify');
const rename = require("gulp-rename");
const sourcemaps = require('gulp-sourcemaps');
const rev = require('gulp-rev');
const revReplace = require('gulp-rev-replace');

const basedir = './public/assets';

function clean() {
    return del([
        basedir + '/dist',
        basedir + '/vendor'
    ]);
}

function vendor () {
    return gulp.src('./node_modules/+(jquery|bootstrap)/dist/**/*', { base: './node_modules/' })
        .pipe(gulp.dest(basedir + '/vendor'));
}

gulp.task('default', gulp.series(clean, vendor));

function cleanDebugbar() {
    return del([
        basedir + '/debugbar'
    ]);
}

function vendorDebugbar() {
    return gulp.src('./vendor/maximebf/debugbar/src/DebugBar/Resources/**/*', {base: './vendor/maximebf/debugbar/src/DebugBar/Resources/'})
        .pipe(gulp.dest(basedir + '/debugbar'));
}

gulp.task('debugbar', gulp.series(cleanDebugbar, vendorDebugbar));

function fonts() {
    return gulp.src(basedir + '/vendor/bootstrap/dist/fonts/*')
        .pipe(gulp.dest(basedir + '/dist/fonts'));
}

function css() {
    return gulp.src([
        basedir + '/vendor/bootstrap/dist/css/bootstrap.css',
        basedir + '/vendor/bootstrap/dist/css/bootstrap-theme.css',
        basedir + '/local/app.css'
    ], { base: basedir })
        .pipe(sourcemaps.init())
        .pipe(concat('all.css'))
        // .pipe(urlAdjuster({
        //     replace:  ['../', '../vendor/bootstrap/dist/']
        // }))
        .pipe(uglifycss())
        .pipe(rename({ extname: '.min.css'}))
        .pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: '../../'}))
        .pipe(gulp.dest(basedir + '/dist/css'));
}

function js() {
    return gulp.src([
        basedir + '/vendor/jquery/dist/jquery.js',
        basedir + '/vendor/bootstrap/dist/js/bootstrap.js',
        basedir + '/local/app.js'
    ], {base: basedir})
        .pipe(sourcemaps.init())
        .pipe(concat('all.js'))
        .pipe(uglify())
        .pipe(rename({extname: '.min.js'}))
        .pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: '../../'}))
        .pipe(gulp.dest(basedir + '/dist/js'));
}

gulp.task('build', gulp.series(
    clean,
    vendor,
    gulp.parallel(fonts, css, js)
));

function revisoning() {
    // gulp.src(basedir + '/dist/**/*.+(js|css|png|gif|jpg|jpeg|svg|woff|woff2|ttf|eot|ico)')
    return gulp.src(basedir + '/dist/**/*')
        .pipe(rev())
        .pipe(gulp.dest(basedir + '/dist'))
        .pipe(rev.manifest())
        .pipe(gulp.dest(basedir + '/dist'))
}

function revisonReplace() {
    return gulp.src(basedir + '/dist/**/*.+(js|css)')
        .pipe(revReplace({manifest: gulp.src(basedir + '/dist/rev-manifest.json')}))
        .pipe(gulp.dest(basedir + '/dist'))
}

gulp.task('dist', gulp.series('build', revisoning, revisonReplace));
