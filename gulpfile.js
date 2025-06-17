const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const concat = require('gulp-concat');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer'); 
const cssnano = require('cssnano'); 

gulp.task('styles', function() {
    return gulp.src('./scss/**/*.scss') 
        .pipe(sass({
            includePaths: ['node_modules'] 
        }).on('error', sass.logError))
        .pipe(postcss([autoprefixer(), cssnano()])) 
        .pipe(concat('style.css'))
        .pipe(gulp.dest('./css')); 
});

gulp.task('watch', function() {
    gulp.watch('./scss/**/*.scss', gulp.series('styles')); 
});

gulp.task('default', gulp.series('styles', 'watch'));