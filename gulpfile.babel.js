import gulp from 'gulp';
import del from 'del';
import concat from 'gulp-concat';
// import urlAdjuster from 'gulp-css-url-adjuster';
import uglifycss from 'gulp-uglifycss';
import uglify from 'gulp-uglify';
import rename from "gulp-rename";
import sourcemaps from 'gulp-sourcemaps';
import rev from 'gulp-rev';
import revReplace from 'gulp-rev-replace';

const basedir = './public/assets';

// noinspection JSUnusedGlobalSymbols
export default gulp.series(
    cleanVendorPackages,
    deployVendorPackages
);

export const debugbar = gulp.series(
    cleanDebugBar,
    deployDebugBar
);

export const build = gulp.series(
    cleanVendorPackages,
    deployVendorPackages,
    gulp.parallel(
        copyFontsToDist,
        buildCSSesAndCopyToDist,
        buildJSesAndCopyToDist
    )
);

export const dist = gulp.series(
    build,
    appendRevisionCodeToPublishedAssets,
    replaceLinksToRevisionAppendedAssets
);

function cleanVendorPackages() {
    return del([
        basedir + '/dist',
        basedir + '/vendor'
    ]);
}

function deployVendorPackages () {
    return gulp.src('./node_modules/+(jquery|bootstrap)/dist/**/*', { base: './node_modules/' })
        .pipe(gulp.dest(basedir + '/vendor'));
}

function cleanDebugBar() {
    return del([
        basedir + '/debugbar'
    ]);
}

function deployDebugBar() {
    return gulp.src([
        './vendor/maximebf/debugbar/src/DebugBar/Resources/**/*'
    ], {base: './vendor/maximebf/debugbar/src/DebugBar/Resources/'})
        .pipe(gulp.dest(basedir + '/debugbar'));
}

function copyFontsToDist() {
    return gulp.src(basedir + '/vendor/bootstrap/dist/fonts/*')
        .pipe(gulp.dest(basedir + '/dist/fonts'));
}

function buildCSSesAndCopyToDist() {
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

function buildJSesAndCopyToDist() {
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

function appendRevisionCodeToPublishedAssets() {
    // gulp.src(basedir + '/dist/**/*.+(js|css|png|gif|jpg|jpeg|svg|woff|woff2|ttf|eot|ico)')
    return gulp.src(basedir + '/dist/**/*')
        .pipe(rev())
        .pipe(gulp.dest(basedir + '/dist'))
        .pipe(rev.manifest())
        .pipe(gulp.dest(basedir + '/dist'))
}

function replaceLinksToRevisionAppendedAssets() {
    return gulp.src(basedir + '/dist/**/*.+(js|css)')
        .pipe(revReplace({manifest: gulp.src(basedir + '/dist/rev-manifest.json')}))
        .pipe(gulp.dest(basedir + '/dist'))
}
