var gulp = require('gulp');
var shell = require('gulp-shell');
var eslint = require('gulp-eslint');
var uglify = require('uglify-js');
var composer = require('gulp-uglify/composer');
var stylelint = require('gulp-stylelint');
var merge = require('merge-stream');
var sourcemaps = require('gulp-sourcemaps');
var concat = require('gulp-concat');

var minify = composer(uglify, console);

gulp.task('eslint', function() {
	return gulp.src(['**/*.js', '!node_modules/**', '!API/**'])
		.pipe(eslint())
		.pipe(eslint.format())
		.pipe(eslint.failAfterError());
});

gulp.task('stylelint', function() {
	return gulp.src(['**/*.css', '!node_modules/**', '!API/**'])
		.pipe(stylelint({
			failAfterError: true,
			reporters: [
				{formatter: 'string', console: true}
			]
		}));
});

gulp.task('npm-check-updates', shell.task(['npm outdated'], {ignoreErrors: true}));

gulp.task('uglify', function() {
	function bundle(name, sources) {
		return gulp.src(sources)
			.pipe(sourcemaps.init())
			.pipe(concat(name + '.min.js'))
			.pipe(minify({ie8: true}))
			.pipe(sourcemaps.write('./'))
			.pipe(gulp.dest('dist/'));
	}
	return merge(
		bundle('serviceworker', ['src/serviceworker.js']),
		bundle('shared-pushed', ['src/shared/config.js', 'src/shared/AfterLoadEvent.js']),
		bundle('shared', ['src/shared/getLessonById.js', 'src/shared/OdyMarkdown.js', 'src/shared/xssOptions.js']),
		bundle('frontend-pushed', ['src/frontend/tools/cacheThenNetworkRequest.js', 'src/frontend/tools/request.js', 'src/frontend/UI/header.js', 'src/frontend/UI/navigation.js', 'src/frontend/UI/TOC.js', 'src/frontend/views/lesson.js', 'src/frontend/authentication.js', 'src/frontend/history.js', 'src/frontend/main.js', 'src/frontend/metadata.js']),
		bundle('frontend', ['src/frontend/tools/urlEscape.js', 'src/frontend/UI/lessonView.js', 'src/frontend/views/competence.js', 'src/frontend/views/competenceList.js', 'src/frontend/views/field.js', 'src/frontend/views/lessonList.js']),
		bundle('admin-pushed', ['src/admin/lessonEditor/refreshPreview.js', 'src/admin/tools/ActionQueue.js', 'src/admin/tools/refreshLogin.js', 'src/admin/tools/request.js', 'src/admin/views/main.js', 'src/admin/history.js', 'src/admin/main.js', 'src/admin/metadata.js'])
	);
});
