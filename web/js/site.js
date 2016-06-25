$(function()
{
    'use strict';

    var $main_nav = $('#main-nav');

    $(':not(#main-nav, .navbar-toggle)').click(function ()
    {
        setTimeout(function () {
            if ($main_nav.hasClass('in') && !$main_nav.hasClass('collapsing'))
            {
                $main_nav.collapse('hide');
            }
        }, 20);
    });
});
