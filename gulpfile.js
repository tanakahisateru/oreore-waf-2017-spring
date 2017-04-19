var gulp = require('gulp');
var rimraf = require('rimraf');

var npm = {
    source_path: './node_modules/+(jquery|bootstrap)/**/*',
    destination_path: './web/assets/'
};

gulp.task('default', ['copy']);

gulp.task('clean', function(cb) {
    rimraf('./web/assets', cb);
});

gulp.task('copy', ['clean'], function() {
    return gulp.src(npm.source_path)
        .pipe(gulp.dest(npm.destination_path));
});
