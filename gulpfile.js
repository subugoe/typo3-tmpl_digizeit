var gulp = require('gulp'),
	autoprefixer = require('gulp-autoprefixer'),
	livereload = require('gulp-livereload'),
	notifier = require('node-notifier'),
	sass = require('gulp-sass'),
	scsslint = require('gulp-scss-lint'),
	sourcemaps = require('gulp-sourcemaps')

var config = {
	paths: {
		sass: ['./Resources/Private/Scss/**/*.scss', '!./Resources/Private/Scss/vendors/**/*.scss'],
		css: './Resources/Public/Css/'
	},
	production: false
}

gulp.task('sass', function() {
	if ( config.production ) {
		gulp.src(config.paths.sass)
			.pipe(sass({
				outputStyle: 'compressed',
				onError: function(error) { errorHandler('Sass', error) }
			}))
			.pipe(autoprefixer())
			.pipe(gulp.dest(config.paths.css))
	} else {
		gulp.src(config.paths.sass)
			.pipe(sourcemaps.init())
			.pipe(sass({
				onError: function(error) { errorHandler('Sass', error) }
			}))
			//.pipe(autoprefixer()) // TODO: .map file is generated, but only works if autoprefixer is disabled. Why?
			.pipe(sourcemaps.write('.'))
			.pipe(gulp.dest(config.paths.css))
			.pipe(livereload())
	}
})

gulp.task('lint', function() {
	gulp.src(config.paths.sass)
		.pipe(scsslint({
			'config': 'scss-lint.yml',
			'maxBuffer': 9999999
		}))
})

gulp.task('compile', function() {
	gulp.start('sass')
})

gulp.task('watch', function() {
	livereload.listen()
	gulp.watch(config.paths.sass, ['compile'])
})

gulp.task('production', function() {
	config.production = true
	gulp.start('lint', 'compile')
})

gulp.task('default', function() {
	gulp.start('lint', 'compile', 'watch')
})

function errorHandler(title, error){
	if (typeof error.file === 'undefined') error.file = '?'
	if (typeof error.line === 'undefined') error.line = '?'
	if (typeof error.column === 'undefined') error.column = '?'
	notifier.notify({
		title: title + ' Error',
		message: "\n" + error.message + "\n\t\nFile: " + error.file + "\nLine: " + error.line + "\nColumn: " + error.column
	});
	console.log(color(title + ' Error: ' + error.message, 'red') + "\nFile: " + error.file + "\nLine: " + error.line + "\nColumn: " + error.column);
}

function color(string, color) {
	var prefix
	switch (color) {
		case 'red': prefix = '\033[1;31m'; break; // and bold
		case 'green': prefix = '\033[0;32m'; break;
	}
	return prefix + string + '\033[0m'
}
