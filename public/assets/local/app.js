;(function($) {
    $.fn.appendCaption = function (text) {
        this.append($('<small>').text(text));
    };
})(jQuery);
