define(['jquery'], function($) {
    return {
        init: function(jsfragment) {
            $(document).ready(function() {
                $('body').prepend(jsfragment);
            });
        }
    };
});