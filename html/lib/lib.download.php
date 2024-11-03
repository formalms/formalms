<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @category 	File managment
 *
 * @author 		Fabio Pirovano <fabio@docebo.com>
 *
 * @version 	$Id: lib.download.php 1000 2007-03-23 16:03:43Z fabio $
 */

/**
 * able the user to download a specified file as an attachment.
 *
 * @param string $path     where the files is on the server filesystem without the filename
 * @param string $filename the name of the file
 * @param string $ext      the extension of the file (.txt, .jpg ...)
 * @param string $sendname the name given to the downlodable file, if not passed it will be constructed in this way:
 *                         assumed that $filename is [number]_[number]_[time]_[filename]
 *                         the file sended will have the name [filename].$ext
 *
 * @return nothing
 */
function sendFile($path, $filename, $ext = null, $sendname = null)
{
    sendFileFromFS($path, $filename, $ext, $sendname);  //TODO: EVT_OBJECT (§)
    //\appCore\Events\DispatcherManager::addListener(
    //    \appCore\Events\Core\FileSystem\DownloadEvent::EVENT_NAME,
    //    'sendFileFromFS'
    //);

    //TODO: EVT_OBJECT (§)
    //$event = new \appCore\Events\Core\FileSystem\DownloadEvent($path, $filename, $ext, $sendname);
    //TODO: EVT_LAUNCH (&)
    //\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\FileSystem\DownloadEvent::EVENT_NAME, $event);
}

/**
 * @param \appCore\Events\Core\FileSystem\DownloadEvent $event
 *
 * @return bool
 */
//function sendFileFromFS(\appCore\Events\Core\FileSystem\DownloadEvent $event){ //TODO: EVT_OBJECT (§)
function sendFileFromFS($path, $filename, $ext, $sendname)
{
    if (FormaLms\lib\Get::cfg('uploadType') == 'fs' || FormaLms\lib\Get::cfg('uploadType') == 'ftp' || FormaLms\lib\Get::cfg('uploadType', null) == null) {
        //$path = $event->getPath(); //TODO: EVT_OBJECT (§)
        //$filename = $event->getFilename(); //TODO: EVT_OBJECT (§)
        //$ext = $event->getExt(); //TODO: EVT_OBJECT (§)
        //$sendname = $event->getSendname(); //TODO: EVT_OBJECT (§)

        //empty and close buffer
        if (!(_files_ == substr($path, 0, strlen(_files_)))) {
            $path = _files_ . $path;
        }
        if ($sendname === null) {
            $sendname = implode('_', array_slice(explode('_', $filename), 3));
            if ($sendname == '') {
                $sendname = $filename;
            }
        }

        if ($ext === null || $ext === false) {
            $ext = array_pop(explode('.', $filename));
        }
        if (substr($sendname, -strlen($ext)) != $ext) {
            $sendname .= '.' . $ext;
        }

        \FormaLms\db\DbConn::getInstance()->close();

        ob_end_clean();
        ob_start();
        session_write_close();
        header('Content-type: application/download; charset=utf-8');
        //ini_set("output_buffering", 0);
        //Download file
        //send file length info
        header('Content-Length:' . filesize($path . $filename));
        //content type forcing dowlad

        //cache control
        header('Cache-control: private');
        //sending creation time
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        //content type
        if (FormaLms\lib\Get::scheme() === 'https') {
            header('Pragma: private');
        }
        header('Content-Disposition: attachment; filename="' . $sendname . '"');
        //sending file
        $file = fopen($path . $filename, 'rb');
        $i = 0;
        if (!$file) {
            return false;
        }
        while (!feof($file)) {
            $buffer = fread($file, 4096);
            echo $buffer;
            if ($i % 100 == 0) {
                $i = 0;
                @ob_end_flush();
            }
            ++$i;
        }
        fclose($file);

        //and now exit
        exit();
    }
}

function sendStrAsFile($string, $filename, $charset = false)
{
    // UTF-8
    $bom = "\xEF\xBB\xBF";
    $meta = '<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />';

    switch (pathinfo($filename, PATHINFO_EXTENSION)) {
            case 'csv':
                $string = $bom . $string;
                break;
            case 'xls':
                $string = $meta . $string;
                break;
            default: break;
        }

    //empty and close buffer

    \FormaLms\db\DbConn::getInstance()->close();

    ob_end_clean();
    session_write_close();
    //ini_set("output_buffering", 0);
    //Download file
    //send file length info
    header('Content-Length:' . strlen($string));
    //content type forcing dowlad
    header('Content-type: application/download' . ($charset ? "; charset=$charset" : '; charset=utf-8') . "\n");
    //cache control
    header('Cache-control: private');
    //sending creation time
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    //content type
    if (FormaLms\lib\Get::scheme() === 'https') {
        header('Pragma: private');
    }
    header('Content-Encoding: UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo $string;

    //and now exit
    exit();
}
