module.exports = function (grunt, options) {
    return {
        resource_js: {
            files: ['src/Ekyna/Bundle/ResourceBundle/Resources/private/js/**/*.js'],
            tasks: ['copy:resource_js'],
            options: {
                spawn: false
            }
        }
    }
};
