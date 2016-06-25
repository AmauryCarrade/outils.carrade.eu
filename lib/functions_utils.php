<?php

function _pluralify($text, $count)
{
    return $text . ($count > 1 ? 's' : '');
}

function friendly_interval($seconds, $with_seconds = true)
{
    if ($seconds < 60)
    {
        return $seconds . _pluralify(' seconde', $seconds);
    }
    else if ($seconds < 3600)
    {
        $min = (int) ($seconds / 60);
        $sec = $seconds - 60 * $min;

        return $min . _pluralify(' minute', $min) . ($with_seconds && $sec > 0 ? ' et ' . $sec . _pluralify(' seconde', $sec) : '');
    }
    else if ($seconds < 86400)
    {
        $hours = (int) ($seconds / 3600);
        $min = (int) (($seconds - $hours * 3600) / 60);
        $sec = $seconds - $hours * 3600 - $min * 60;

        return
            $hours . _pluralify(' heure', $hours)
            . ($min > 0 ? ($with_seconds && $sec > 0 ? ', ' : ' et ') . $min . _pluralify(' minute', $min) : '')
            . ($with_seconds && $sec > 0 ? ' et ' . $sec . _pluralify(' seconde', $sec) : '');
    }
    else
    {
        $days = (int) ($seconds / 86400);
        $hours = (int) (($seconds - $days * 86400) / 3600);
        $min = (int) (($seconds - $days * 86400 - $hours * 3600) / 60);
        $sec = $seconds - $days * 86400 - $hours * 3600 - $min * 60;

        return
            $days . _pluralify(' jour', $days)
            . ($hours > 0 ? ($min > 0 || ($with_seconds && $sec > 0) ? ', ' : ' et ') . $hours . _pluralify(' heure', $hours) : '')
            . ($min > 0 ? ($with_seconds && $sec > 0 ? ', ' : ' et ') . $min . _pluralify(' minute', $min) : '')
            . ($with_seconds && $sec > 0 ? ' et ' . $sec . _pluralify(' seconde', $sec) : '');
    }
}
