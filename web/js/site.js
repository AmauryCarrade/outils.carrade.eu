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


// Thanks to http://stackoverflow.com/a/987376/5599794

function select_text(element)
{
    var doc  = document,
        range, selection;

    if (doc.body.createTextRange)
    {
        range = document.body.createTextRange();
        range.moveToElementText(element);
        range.select();
    }
    else if (window.getSelection)
    {
        selection = window.getSelection();
        range = document.createRange();
        range.selectNodeContents(element);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}
