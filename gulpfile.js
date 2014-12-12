var gulp = require('gulp'),
	livereload = require('gulp-livereload'),
	compass = require('gulp-compass');

var paths = {
	sass: ['./Resources/Private/Scss/**/*.scss']
};

gulp.task('default', function() {

});



gulp.task('compass', function() {
	gulp.src('./Resources/Private/Scss/**/*.scss')
		.pipe(compass({
						  css: './Resources/Public/Css',
						  sass: './Resources/Private/Scss',
						  image: './Resources/Public/Images'
					  }))
		.pipe(gulp.dest('./Resources/Public/Css'));
});


// Rerun the task when a file changes
gulp.task('watch', function() {
  gulp.watch(paths.sass, ['compass']);
});