var paths = {
	sass: ['./Resources/Private/Scss/**/*.scss']
};

var gulp = require('gulp'),
	livereload = require('gulp-livereload'),
	sourcemaps = require('gulp-sourcemaps'),
	sass = require('gulp-sass')

gulp.task('sass', function() {
	gulp.src('./Resources/Private/Scss/digizeit.scss')
		//.pipe(sourcemaps.init())
		.pipe(sass())
		//.pipe(sourcemaps.write())
		.pipe(gulp.dest('./Resources/Public/Css/'))
});

// Rerun the task when a file changes
gulp.task('watch', function() {
	gulp.watch(paths.sass, ['sass'])
});

gulp.task('default', function() {
	gulp.start('sass')
	gulp.start('watch')
});
