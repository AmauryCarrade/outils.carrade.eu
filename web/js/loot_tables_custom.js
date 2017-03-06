'use strict';

var SHORTENER = '/ajax/shorten?url=';


// Import loot table from hash if present
if (window.location.hash.startsWith('#!'))
{
    console.log(Base64.decode(window.location.hash.substr(2)));
    importFromJSON(Base64.decode(window.location.hash.substr(2)));
}

function get_share_url()
{
    return window.location.origin
              + window.location.pathname
              + '#!'
              + Base64.encode(JSON.stringify(getJSONTable()));
}

document.getElementById('share_table_link').onclick = function(ev)
{
    prompt('Link to this table', get_share_url());
    return false;
};

document.getElementById('share_table_link_short').onclick = function(ev)
{
    ev.preventDefault();

    var url = get_share_url();
    var r = new XMLHttpRequest();

    console.log(SHORTENER + encodeURIComponent(url));
    r.open('GET', SHORTENER + encodeURIComponent(url), true);
    r.onreadystatechange = function()
    {
        if (r.readyState != 4 || r.status != 200) return;
        prompt('Link to this table', r.responseText);
    };
    console.log(r);
    r.send();
    console.log(r);

    return false;
};
