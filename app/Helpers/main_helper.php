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

function minutes_to_time(int $minutes): string
{
    if ($minutes < 0) {
        return '00:00:00';
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    if ($hours > 23) {
        $hours = 23;
        $mins = 59;
    }
    return sprintf('%02d:%02d:00', $hours, $mins);
}
