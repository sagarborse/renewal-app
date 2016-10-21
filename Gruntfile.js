module.exports = function (grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        sass: {
            dist: {
                options: {
                    style: 'expanded',
                    lineNumbers: true,
                    sourcemap: false
                },
                files: [{
                    expand: true,
                    cwd: 'public/css/sass',
                    src: ['*.scss', '**/*.scss'],
                    dest: 'public/css/bin',
                    ext: '.css'
                }]
            }
        },
        cssmin: {
            css: {
                options: {
                    sourcemap: true
                },
                files: [{
                    expand: true,
                    cwd: 'public/css/bin',
                    src: ['*.css', '!*.min.css', '**/*.css', '!**/*.min.css'],
                    dest: 'public/css/bin',
                    ext: '.min.css'
                }]
            }
        },
        watch: {
            sass: {
                files: [
                    'public/css/sass/*.scss',
                    'public/css/sass/**/*.scss',
                    'public/css/partials/*.scss'
                ],
                tasks: ['sass', 'cssmin']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask('buildcss', ['sass', 'cssmin']);

};