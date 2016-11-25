module.exports = function (grunt, options) {
    return {
        resource: {
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/ResourceBundle/Resources/private',
                    src: ['js/*.js', 'js/form/*.js'],
                    dest: 'src/Ekyna/Bundle/ResourceBundle/Resources/public'
                }
            ]
        }
    }
};
