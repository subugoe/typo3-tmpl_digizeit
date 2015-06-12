var config = {
	paths: {
		sass: ['./Resources/Private/Scss/**/*.scss', '!./Resources/Private/Scss/vendors/**/*.scss'],
		css: './Resources/Public/Css/',
		svg: './Resources/Public/Images/Layout/*.svg',
		svgout: './Resources/Public/Images/Layout/',
		js: './Resources/Private/JavaScript/**/*.js',
		jsmin: './Resources/Public/JavaScript/'
	},
	production: false
}

var gulp = require('gulp'),
	autoprefixer = require('gulp-autoprefixer'),
	uglify = require('gulp-uglify'),
	livereload = require('gulp-livereload'),
	notifier = require('node-notifier'),
	sass = require('gulp-sass'),
	scsslint = require('gulp-scss-lint'),
	sourcemaps = require('gulp-sourcemaps'),
	svgmin = require('gulp-svgmin')

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

gulp.task('default', function() {
	gulp.start('lint', 'sass', 'uglify', 'watch')
})

gulp.task('uglify', function() {
	gulp.src(config.paths.js)
		.pipe(uglify())
		.pipe(gulp.dest(config.paths.jsmin))
})

gulp.task('lint', function() {
	gulp.src(config.paths.sass)
		.pipe(scsslint({
			'config': 'scss-lint.yml',
			'maxBuffer': 9999999
		}))
})

gulp.task('lint-watch', function() {
	gulp.start('lint')
	gulp.watch(config.paths.sass, ['lint'])
})

gulp.task('production', function() {
	config.production = true
	gulp.start('lint', 'sass', 'uglify')
})

gulp.task('svgmin', function() {
	gulp.src(config.paths.svg)
		.pipe(svgmin())
		.pipe(gulp.dest(config.paths.svgout))
})

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
			// TODO: There's an issue preventing sourcemaps from being written correctly with autoprefixer (see
			// https://github.com/sindresorhus/gulp-autoprefixer/issues/8), so disable for now
			//.pipe(autoprefixer())
			.pipe(sourcemaps.write('.'))
			.pipe(gulp.dest(config.paths.css))
			.pipe(livereload())
	}
})

gulp.task('watch', function() {
	livereload.listen()
	gulp.watch(config.paths.sass, ['sass'])
	gulp.watch(config.paths.js, ['uglify'])
})
