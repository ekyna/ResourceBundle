module.exports = function (grunt, options) {
    return {
        /*resource_files: {
            expand: true,
            cwd: 'src/Ekyna/Bundle/ResourceBundle/Resources/private',
            src: ['img/**', 'lib/**'],
            dest: 'src/Ekyna/Bundle/ResourceBundle/Resources/public'
        },*/
        resource_js: {
            expand: true,
            cwd: 'src/Ekyna/Bundle/ResourceBundle/Resources/private',
            src: ['js/*.js', 'js/form/**'],
            dest: 'src/Ekyna/Bundle/ResourceBundle/Resources/public'
        }/*,
        resource_web: {
            expand: true,
            cwd: 'src/Ekyna/Bundle/ResourceBundle/Resources/public',
            src: ['**'],
            dest: 'web/bundles/ekynaresource'
        }*/
    }
};
