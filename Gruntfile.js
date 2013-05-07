module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON("package.json"),
        shell: {
            caketest: {
                command: '../../Console/cake test CakeResque AllCakeResque',
                options: {
                    stdout: true
                }
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

    grunt.registerTask('caketest', ['shell:caketest']);
};

/* --coverage-html ./Test/Coverage --configuration ./Test/phpunit.xml*/