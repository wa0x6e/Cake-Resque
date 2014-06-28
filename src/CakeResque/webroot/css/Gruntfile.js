module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),
		replace: {
			dist: {
				options: {
					variables: {
						version: '<%= pkg.version %>'
					}
				},
				files: [
					{
						expand: true,
						flatten: true,
						src: ['../../Templates/build/header.php'],
						dest: '../../Templates/'
					}
				]
			}
		}
	});

	// Run after each file save
	grunt.registerTask('default', ['bump', 'replace']);

	grunt.loadNpmTasks('grunt-bump');
	grunt.loadNpmTasks('grunt-replace');
};