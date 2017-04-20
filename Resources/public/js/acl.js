define(['jquery', 'ekyna-spinner'], function($) {
    "use strict";

    var $acl = $('.acl-list'),
        busy = false;

    $('button.acl-action').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (busy) {
            return false;
        }

        $acl.loadingSpinner();

        var $button = $(this),
            xhr = $.ajax({
            url: Router.generate('admin_ekyna_resource_ace_edit'),
            method: 'POST',
            data: {
                subject: $acl.data('subject'),
                resource: $button.closest('.acl-resource').data('resource'),
                action: $button.closest('.acl-action').data('action'),
                permission: $button.hasClass('btn-success') ? 0 : 2
            }
        });

        xhr.done(function(data) {
            if (data.permission === 2) {
                $button
                    .addClass('btn-success')
                    .removeClass('btn-danger btn-default')
                    .find('> i')
                    .addClass('fa-check')
                    .removeClass('fa-remove text-success text-danger');
            } else if (data.permission === 1) {
                $button
                    .addClass('btn-default')
                    .removeClass('btn-success btn-danger');
                if (data.inherited === 2) {
                    $button
                        .find('> i')
                        .addClass('fa-check text-success')
                        .removeClass('fa-remove text-danger');
                } else {
                    $button
                        .find('> i')
                        .addClass('fa-remove text-danger')
                        .removeClass('fa-check text-success');
                }
            } else {
                $button
                    .addClass('btn-danger')
                    .removeClass('btn-success btn-default')
                    .find('> i')
                    .addClass('fa-remove')
                    .removeClass('fa-check text-success text-danger');
            }
        });

        xhr.always(function() {
            $acl.loadingSpinner('off');
            busy = false;
        });

        return false;
    });
});
