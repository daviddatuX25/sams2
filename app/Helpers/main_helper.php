<?php

function formatDuration(string $time): string
{
    if ($time === '00:00:00' || empty($time)) {
        return '-';
    }

    list($hours, $minutes, $seconds) = explode(':', $time);

    $hours = (int)$hours;
    $minutes = (int)$minutes;

    $result = [];

    if ($hours > 0) {
        $result[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
    }

    if ($minutes > 0) {
        $result[] = $minutes . ' min' . ($minutes > 1 ? 's' : '');
    }

    return implode(' ', $result);
}
