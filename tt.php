#!/usr/bin/php
<?php

/**
 * @author Chris Frazier <chris@chrisfrazier.me>
 * @copyright Copyright (c) 2014, Chris Frazier
 * @license https://www.gnu.org/licenses/gpl.txt LGPL
 * 
 * Yet another command-line time tracking app. This is a port
 * of an old set of bash scripts. Pretty straight forward but
 * very effective.
 */


/////////////////////////////////////////////////////////////
// Uncomment this line if you need to hardcode your timezone
// (ie: no access to php.ini, etc...)
// date_default_timezone_set('America/New_York');
/////////////////////////////////////////////////////////////


function error($string)
{
    echo "\033[0;31m$string\033[0m";
}


function format_diff($diff)
{
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
    $diff = format_diff(date_diff($dt, $dts));
    $word = $stop ? 'worked' : 'working';
    echo "$word on $project:\n  from     $times\n  to now,  $time on $day\n";
    echo "        => \033[1;33m$diff elapsed\033[0m\n";
}


function parse_and_add($line, &$tracker)
{
    $project = explode(': ', trim($line));
    $times   = explode(' - ', $project[1]);
    $project = strtolower(trim($project[0]));
    if (!isset($times[1])) return;

    $start_time = str_replace(' on ', ' ', $times[0]);
    $start_time = date_create($start_time);
    $end_time   = str_replace(' on ', ' ', $times[1]);
    $end_time   = date_create($end_time);

    $diff = date_diff($start_time, $end_time);
    if (isset($tracker[$project])) {
        $a = date_create('00:00');
        $b = clone $a;
        $b = date_add($b, $tracker[$project]);
        $b = date_add($b, $diff);
        $tracker[$project] = date_diff($a, $b);
    } else {
        $tracker[$project] = $diff;
    }
}


function print_summary($pipe = false)
{
    $tracker = Array();
    if (!$pipe) {
        global $f; fseek($f, 0);
        while (false !== ($line = fgets($f)))
            parse_and_add($line, $tracker);
    } else {
        while (false !== ($line = fgets(STDIN)))
            parse_and_add($line, $tracker);
    }
    foreach ($tracker as $project => $diff)
        echo "$project: ", format_diff($diff), "\n";
}


function print_projects()
{
    global $f; fseek($f, 0);
    while (false !== ($line = fgets($f))) {
        $project = explode(': ', trim($line));
        $project = strtolower(trim($project[0]));
        $projects[] = $project;
    }
    $projects = array_unique($projects);
    foreach ($projects as $project)
        echo "$project\n";
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
    case 'projects':
        print_projects();
        break;
    case 'print':
        print_tracker($active);
        break;
    case 'parse':
        print_summary(true);
        break;
    default:
        error("[!] invalid command\n");
        break;
}
fclose($f);

