module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON("package.json"),
        clean: {
            coverage: ['Test/Coverage']
        },
        shell: {
            caketest: {
                command: '../../Console/cake test CakeResque AllCakeResque',
                options: {
                    stdout: true
                }
            },
            coverage: {
                command: '../../Console/cake test CakeResque AllCakeResque --configuration Test/phpunit.xml --coverage-html Test/Coverage'
            }
        },
        watch: {
          scripts: {
            files: ['Console/**/*.php', 'Test/**/*.php'],
            tasks: ['caketest'],
            options: {
              nospawn: true
            }
          }
        }
    });

    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');

    grunt.registerTask('caketest', ['shell:caketest']);
    grunt.registerTask('coverage', ['clean:coverage', 'shell:coverage']);
};