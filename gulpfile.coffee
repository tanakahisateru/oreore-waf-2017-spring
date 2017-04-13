gulp = require 'gulp'
rimraf = require 'rimraf'

npm =
  source_path: './node_modules/+(jquery|bootstrap)/**/*'
  destination_path: './web/assets/'

gulp.task 'default', ['copy']

gulp.task 'clean', (cb)->
  rimraf './web/assets', cb

gulp.task 'copy', ['clean'], ->
  gulp.src npm.source_path
    .pipe gulp.dest npm.destination_path
