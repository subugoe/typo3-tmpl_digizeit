var gulp = require('gulp'),
	//livereload = require('gulp-livereload'),
	notify = require('gulp-notify'),
	sass = require('gulp-ruby-sass'),
	scsslint = require('gulp-scss-lint')

var paths = {
	sass: ['./Resources/Private/Scss/**/*.scss', '!./Resources/Private/Scss/vendors/**/*.scss']
}

gulp.task('sass', function() {
	gulp.src(paths.sass)
		.pipe(sass({
			style: 'compressed'
		}))
		.on('error', notify.onError({
			title: 'Sass Error',
			message: '<%= error.message %>'
		}))
		.pipe(gulp.dest('./Resources/Public/Css/'))
})

gulp.task('lint', function() {
	gulp.src(paths.sass)
		.pipe(scsslint({
			'config': 'scss-lint.yml',
			'maxBuffer': 9999999
		}))
})

gulp.task('compile', function() {
	gulp.start('sass')
})

gulp.task('watch', function() {
	gulp.watch(paths.sass, ['lint', 'compile'])
})

gulp.task('default', function() {
	gulp.start('lint', 'compile', 'watch')
})
