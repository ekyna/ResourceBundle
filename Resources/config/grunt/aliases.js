module.exports = {
    /*'copy:resource': [
        'copy:resource_fonts',
        'copy:resource_libs',
        'copy:resource_libs_fix',
        'copy:resource_files'
    ],*/
    'build:resource': [
        //'clean:resource_pre',
        //'copy:resource',
        //'less:resource',
        //'cssmin:resource',
        'uglify:resource'
        //'clean:resource_post'
    ]
};
