// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $.cookieBar({
            message: Translator.__('We use cookies to track usage and preferences.'),
            acceptText: Translator.__('I Understand'),
            element: '.navbar.fixed-top',
            append: true
        });
    });
})(jQuery);
