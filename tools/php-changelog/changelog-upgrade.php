<?php

$gitLogFile = __DIR__ . '/git-log.txt';
$changeLogFile = __DIR__ . '/changelog.txt';
$lastHashFile = __DIR__ . '/last-hash.txt';

$lastHash = file_get_contents($lastHashFile);

function exportGitLog($hash, $gitLogFile)
{
    echo exec("git log $hash.. --pretty=format:'#%h# *%s*' --shortstat --no-merges | paste - - - > " . $gitLogFile);
}

function deleteFile($gitLogFile)
{
    if (is_file($gitLogFile)) {
        unlink($gitLogFile);
    }
}

function getGitLines($gitLogFile)
{
    $file = fopen($gitLogFile, 'r');

    if (!$file) {
        die('file does not exist or cannot be opened');
    }

    while (($line = fgets($file)) !== false) {
        yield $line;
    }
    fclose($file);
}

function getStringBetween($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

deleteFile($gitLogFile);
exportGitLog($lastHash, $gitLogFile);

$changeLog = '';
$commitHash = '';
foreach (getGitLines($gitLogFile) as $line) {
    $commitHash = getStringBetween($line, '#', '#');
    $commitMessage = getStringBetween($line, '*', '*');
    if (!empty($commitMessage)) {
        if (substr($commitMessage, 0, 2) !== '- ') {
            $commitMessage = '- ' . $commitMessage;
        }
        if (!empty($changeLog) && (strpos($commitMessage, $changeLog) === false)) {
            if (empty($changeLog)) {
                $changeLog .= $commitMessage;
            } else {
                $changeLog .= PHP_EOL . $commitMessage;
            }
        } else {
            $changeLog .= $commitMessage;
        }
    }
    // $line contains current line
}
file_put_contents($changeLogFile, $changeLog);
file_put_contents($lastHashFile, $commitHash);
deleteFile($gitLogFile);

