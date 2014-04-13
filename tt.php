#!/usr/bin/php
<?php

/////////////////////////////////////////////////////////////
// Uncomment this line if you need to hardcode your timezone
// (ie: no access to php.ini, etc...)
date_default_timezone_set('America/New_York');
/////////////////////////////////////////////////////////////


function error($string)
{
    echo "\033[0;31m$string\033[0m";
}


function format_diff($dt1, $dt2)
{
    $diff = date_diff($dt1, $dt2);
    if ($diff->y > 0)
        return $diff->y.'y'.$diff->m.'m'.$diff->d.'d'.$diff->h.'h'.$diff->i .'m';
    elseif ($diff->m > 0)
        return $diff->m.'m'.$diff->d.'d'.$diff->h.'h'.$diff->i .'m';
    elseif ($diff->d > 0)
        return $diff->d.'d'.$diff->h.'h'.$diff->i .'m';
    else
        return $diff->h.'h'.$diff->i .'m';
}


function start_working($project)
{
    global $f;
    $project = trim($project);
    if (empty($project)) {
        error("[!] specify a valid project name to start\n");
        return;
    }
    $dt   = date_create();
    $time = date_format($dt, 'H:i');
    $day  = date_format($dt, 'd M Y');
    fwrite($f, "$project: $time on $day - ");
    echo "starting work on $project at $time on $day\n";
}


function print_working($stop = false)
{
    global $f, $line;
    $project = explode(': ', $line);
    $times   = explode(' - ', $project[1]);
    $project = $project[0]; $times = $times[0];

    $start_time = str_replace(' on ', ' ', $times);
    $dts  = date_create($start_time);
    $dt   = date_create();
    $time = date_format($dt, 'H:i');
    $day  = date_format($dt, 'd M Y');

    if ($stop) fwrite($f, "$time on $day\n");
    $diff = format_diff($dt, $dts);
    $word = $stop ? 'worked' : 'working';
    echo "$word on $project:\n  from     $times\n  to now,  $time on $day\n";
    echo "        => \033[1;33m$diff elapsed\033[0m\n";
}


function print_summary()
{
    // TODO
}


function print_tracker()
{
    global $f, $tracker_file, $active;
    fseek($f, 0);
    $data = fread($f, filesize($tracker_file));
    $crlf = $active ? "\n" : "";
    echo "$data$crlf";
}


$tracker_file = $_SERVER['HOME'] . '/.timetracker';
$f = fopen($tracker_file, 'c+');
$cursor = -1;
fseek($f, $cursor--, SEEK_END);
$char = fgetc($f);
while ($char === "\n" || $char === "\r") {
    fseek($f, $cursor--, SEEK_END);
    $char = fgetc($f);
}
$jump = $cursor + 2;
while ($char !== false && $char !== "\n" && $char !== "\r") {
    $line = $char . $line;
    fseek($f, $cursor--, SEEK_END);
    $char = fgetc($f);
}
if (substr($line, -3) === ' - ') {
    $active = true;
    fseek($f, $jump, SEEK_END);
} else {
    $active = false;
    fseek($f, 0, SEEK_END);
}


$command = $argv[1] ? $argv[1] : 'summary';
switch ($command) {
    case 'summary':
        if ($active) print_working();
        else print_summary();
        break;
    case 'start':
        if ($active) error("[!] cannot start a new project while one is in progress\n");
        else start_working($argv[2]);
        break;
    case 'stop':
        if ($active) print_working(true);
        else error("[!] there are no active projects running\n");
        break;
    case 'print':
        print_tracker($active);
        break;
    case 'parse':
        error("[!] TODO\n");
        break;
    default:
        error("[!] invalid command\n");
        break;
}
fclose($f);

