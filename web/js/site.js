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

    prettyPrint();

    $('.tooltip-top')   .tooltip();
    $('.tooltip-left')  .tooltip({ placement: 'left'   });
    $('.tooltip-right') .tooltip({ placement: 'right'  });
    $('.tooltip-bottom').tooltip({ placement: 'bottom' });
});
