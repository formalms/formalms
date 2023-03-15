<?php

$gitLogFile = __DIR__ . '/git-log.txt';
$changeLogFile = __DIR__ . '/changelog.txt';
$lastHashFile = __DIR__ . '/last-hash.txt';

$lastHash = file_get_contents($lastHashFile);
echo 'Changelog is starting from commit hash : '.$lastHash.PHP_EOL;
function exportGitLog($hash, $gitLogFile)
{
    $hash = str_replace(' ','',$hash);
    echo exec("git log $hash.. --pretty=format:'%h###%s###' --shortstat --no-merges | paste - - - > " . $gitLogFile);
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



deleteFile($gitLogFile);
exportGitLog($lastHash, $gitLogFile);

$changeLog = '';
$commitHash = '';
foreach (getGitLines($gitLogFile) as $line) {
    $lineArray = explode('###',$line);
    if (empty($commitHash)) {
        $commitHash = str_replace([' ', "\t"], ['', ''], $lineArray[0]);
    }
    $commitMessage = $lineArray[1];
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
if (empty($commitHash)){
    $commitHash = $lastHash;
}

deleteFile($changeLogFile);
file_put_contents($changeLogFile, $changeLog);
deleteFile($lastHashFile);
file_put_contents($lastHashFile, $commitHash);
echo 'Last commit hash is : '.$commitHash.PHP_EOL;
deleteFile($gitLogFile);

