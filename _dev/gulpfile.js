var gulp = require('gulp'),
	gulpLoadPlugins = require('gulp-load-plugins'),
    plugins = gulpLoadPlugins();

// misc plugins
var del 			= require('del'),
	replace 		= require("replace"),
	runSequence 	= require('run-sequence'),
	browserSync 	= require('browser-sync'),
	reload      	= browserSync.reload,
	dateFormat 		= require('dateformat');



// variables
now			= dateFormat(new Date(), "yyyy-mm-dd, HH:MM");
var pkg 	= require('./package.json');
config 	= require('./gulpconfig.json');
var	banner 	= ['/*!',
		' * <%= config.title %>, <%= config.copyright %>',
		' * @version <%= config.version %>',
		' * @date <%= this.now %>',
		' */',
		''].join('\n');

// Gulp plumber error handler
var onErrorCSS = function(err) {
        plugins.notify.onError({
                    subtitle: "Error in CSS File",
                    message:  "<%= error.message %>"
                })(err);
        this.emit('end');
    },
	onErrorJS = function(err) {
        plugins.notify.onError({
                    subtitle: "Error in JS File",
                    message:  "<%= error.message %>"
                })(err);
        this.emit('end');
    };





// Styles tasks
gulp.task('css', function () {
	return gulp.src(config.paths.src_sass + '/*.scss')
		.pipe(plugins.plumber({errorHandler: onErrorCSS}))
		.pipe(plugins.sourcemaps.init())
		.pipe(plugins.sass( {outputStyle:'expanded', indentWidth:4} ))
		.pipe(plugins.autoprefixer('last 2 version'))
		.pipe(plugins.header(banner, { pkg : pkg } ))
		.pipe(plugins.sourcemaps.write('.'))
		.pipe(gulp.dest(config.paths.dist_css))
		.pipe(reload({stream:true}))
		.pipe(plugins.notify({ message: 'Styles compiled' }));
		
});
gulp.task('css:minify', function () {
    return gulp.src(config.paths.dist_css + '*.css')
    	.pipe(plugins.plumber({errorHandler: onErrorCSS}))
    	.pipe(plugins.minifyCss())
    	.pipe(gulp.dest(config.paths.dist_css))
    	.pipe(plugins.notify({ message: 'Styles ready for distribution!', sound: "Beep" }));
});
gulp.task('css:clean', function (cb) {
    del([config.paths.dist_css + '*.map'], {force: true}, cb);
});
gulp.task('css:dist', function() { runSequence('css', ['css:minify', 'css:clean']) } );





// Scripts tasks
gulp.task('js', function() {
    return gulp.src([config.paths.src_js + 'vendor/*.js', config.paths.src_js + '*.js'])
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.concat('scripts.js'))
        .pipe(plugins.plumber({errorHandler: onErrorJS}))
        .pipe(plugins.header(banner, { pkg : pkg } ))
        .pipe(plugins.sourcemaps.write('.'))
        .pipe(gulp.dest(config.paths.dist_js))
        .pipe(reload({stream:true}))
        .pipe(plugins.notify({ message: 'JavaScript compiled' }));
});
gulp.task('js:hint', function() {
    return gulp.src(config.paths.src_js + '**/*.js')
    	.pipe(plugins.plumber({errorHandler: onErrorJS}))
    	.pipe(plugins.jshint('.jshintrc'))
    	.pipe(plugins.jshint.reporter('default'))
    	.pipe(plugins.jshint.reporter('fail'))
    	.on('error', plugins.notify.onError({ message: 'JS hint error'}));
});
gulp.task('js:uglify', function (cb) {
    return gulp.src(config.paths.dist_js + 'scripts.js')
    	.pipe(plugins.plumber({errorHandler: onErrorJS}))
    	.pipe(plugins.uglify({preserveComments:'some'}))
    	.pipe(gulp.dest(config.paths.dist_js))
    	.pipe(plugins.notify({ message: 'JavaScript ready for distribution!', sound: "Beep" }));
});
gulp.task('js:clean', function (cb) {
    del([config.paths.dist_js + '*.map'], {force: true}, cb);
});
gulp.task('js:dist', function() { runSequence('js:hint', 'js', 'js:uglify', 'js:clean') } );




// watch tasks
gulp.task('watch', function() {
    gulp.watch(config.paths.src_sass + '**/*', ['css']);
    gulp.watch(config.paths.src_js + '**/*', ['js']);
	plugins.livereload.listen();
	gulp.watch([
			config.paths.dist_js + '**/*.js', 
			config.paths.dist_css + 'styles.css', 
			config.paths.dist_css + 'print.css', 
			config.paths.theme + '**/*.php'
		]).on('change', plugins.livereload.changed);
});
gulp.task('watch:sync', function() {
	browserSync({
    	proxy: config.proxy_url,
    	notify: true,
    	open: true
    });
    gulp.watch(config.paths.src_sass + '**/*', ['css']);
    gulp.watch(config.paths.src_js + '**/*', ['js']);
    gulp.watch(config.paths.theme + '**/*.php').on('change', reload);
});




// version bumping
gulp.task('bump:patch', function() {
	gulp.src('./gulpconfig.json').pipe(plugins.bump({key: 'version', type:'patch', indent: 4})).pipe(gulp.dest('./'));
});
gulp.task('bump:minor', function() {
	gulp.src('./gulpconfig.json').pipe(plugins.bump({key: 'version', type:'minor', indent: 4})).pipe(gulp.dest('./'));
});
gulp.task('bump:major', function() {
	gulp.src('./gulpconfig.json').pipe(plugins.bump({key: 'version', type:'major', indent: 4})).pipe(gulp.dest('./'));
});





// default tasks
gulp.task('default', 		['watch', 'css', 'js']);
gulp.task('dev', 	 		['css', 'js']);
gulp.task('dist', 	 		['css:dist', 'js:dist']);