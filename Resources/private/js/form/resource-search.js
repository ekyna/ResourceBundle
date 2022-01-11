define(['jquery', 'select2'], function($) {
    "use strict";

    const defaults = {
        limit: 10,
    };

    /**
     * Resource search widget
     */
    $.fn.resourceSearchEntity = function(config) {
        config = $.extend({
            limit: 10
        }, config);

        this.each(function() {
            var $this = $(this),
                config = $.extend({}, defaults, $this.data('config'));

            $this.select2({
                minimumInputLength: 3,
                ajax: {
                    delay: 300,
                    url: $this.data('search'),
                    dataType: 'json',
                    data: function (params) {
                        return {
                            query: params.term,
                            page:  params.page,
                            limit: config.limit
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: {
                                more: (params.page * config.limit) < data.total_count
                            }
                        };
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                }
            });
        });
        return this;
    };

    return {
        init: function($element) {
            $element.resourceSearchEntity();
        }
    };
});
