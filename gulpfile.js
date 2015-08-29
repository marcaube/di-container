var gulp = require('gulp'),
    notify = require('gulp-notify'),
    phpspec = require('gulp-phpspec');

// Run all specs
gulp.task('phpspec', function () {
    gulp.src('spec/**/*.php')
        .pipe(phpspec('./bin/phpspec run', {
            notify: true,
            clear: true
        }))
        .on('error', notify.onError({
            title: 'Awww shit!',
            message: 'Your tests failed!',
            icon: __dirname + '/node_modules/gulp-phpspec/assets/test-fail.png'
        }))
        .pipe(notify({
            title: 'Awww yeah!',
            message: 'All green!',
            icon: __dirname + '/node_modules/gulp-phpspec/assets/test-pass.png'
        }));
});

// Keep an eye on PHP files for changes...
gulp.task('watch', function () {
    gulp.watch(['spec/**/*.php', 'src/**/*.php'], ['phpspec']);
});

// What tasks does running gulp trigger?
gulp.task('default', ['phpspec', 'watch']);
