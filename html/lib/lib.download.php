<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * @package 	admin-library
 * @category 	File managment
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id: lib.download.php 1000 2007-03-23 16:03:43Z fabio $
 */

/**
 * able the user to download a specified file as an attachment
 *
 * @param string	$path		where the files is on the server filesystem without the filename
 * @param string	$filename	the name of the file
 * @param string	$ext		the extension of the file (.txt, .jpg ...)
 * @param string	$sendname	the name given to the downlodable file, if not passed it will be constructed in this way:
 *								assumed that $filename is [number]_[number]_[time]_[filename]
 *								the file sended will have the name [filename].$ext
 *
 * @return nothing
 */
function sendFile($path, $filename, $ext = NULL, $sendname = NULL)
{

    \appCore\Events\DispatcherManager::addListener(
        \appCore\Events\Core\FileSystem\DownloadEvent::EVENT_NAME,
        'sendFileFromFS'
	);

    $event = new \appCore\Events\Core\FileSystem\DownloadEvent($path, $filename, $ext, $sendname);
    \appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\FileSystem\DownloadEvent::EVENT_NAME, $event);
}



/**
 * @param \appCore\Events\Core\FileSystem\DownloadEvent $event
 * @return bool
 */
function sendFileFromFS(\appCore\Events\Core\FileSystem\DownloadEvent $event){
	if (Get::cfg('uploadType') == 'fs' || Get::cfg('uploadType') == 'ftp' || Get::cfg('uploadType', null) == null) {
		$path = $event->getPath();
		$filename = $event->getFilename();
		$ext = $event->getExt();
		$sendname = $event->getSendname();

		//empty and close buffer
		if (!($GLOBALS['where_files_relative'] == substr($path, 0, strlen($GLOBALS['where_files_relative'])))) {
			$path = $GLOBALS['where_files_relative'] . $path;
		}
		if ($sendname === NULL) {
			$sendname = implode('_', array_slice(explode('_', $filename), 3));
			if ($sendname == '') $sendname = $filename;
		}

		if ($ext === NULL || $ext === false) {
			$ext = array_pop(explode('.', $filename));

		}
		if (substr($sendname, -strlen($ext)) != $ext) $sendname .= '.' . $ext;

		@DbConn::getInstance()->close();

		ob_end_clean();
		session_write_close();
		//ini_set("output_buffering", 0);
		//Download file
		//send file length info
		header('Content-Length:' . filesize($path . $filename));
		//content type forcing dowlad
		header("Content-type: application/download; charset=utf-8\n");
		//cache control
		header("Cache-control: private");
		//sending creation time
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		//content type
		//if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		if ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
			or (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
			or (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) == 'on')
		) {
			header('Pragma: private');
		}
		header('Content-Disposition: attachment; filename="' . $sendname . '"');
		//sending file
		$file = fopen($path . $filename, "rb");
		$i = 0;
		if (!$file) return false;
		while (!feof($file)) {
			$buffer = fread($file, 4096);
			echo $buffer;
			if ($i % 100 == 0) {
				$i = 0;
				@ob_end_flush();
			}
			$i++;
		}
		fclose($file);

		//and now exit
		exit();
	}
}

function sendStrAsFile($string, $filename, $charset=false) {

        // UTF-8
        $bom = "\xEF\xBB\xBF";
        $meta = '<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />';
        
        switch( pathinfo($filename, PATHINFO_EXTENSION) ) {
            case "csv": 
                $string = $bom.$string;
                break;
            case "xls":
                $string = $meta.$string;
                break;
            default: break;
        }
    
	//empty and close buffer

	@DbConn::getInstance()->close();

	ob_end_clean();
	session_write_close();
	//ini_set("output_buffering", 0);
	//Download file
	//send file length info
	header('Content-Length:'. strlen($string));
	//content type forcing dowlad
	header("Content-type: application/download".($charset ? "; charset=$charset" : "; charset=utf-8")."\n");
	//cache control
	header("Cache-control: private");
	//sending creation time
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	//content type
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		header('Pragma: private');
	}
	header('Content-Encoding: UTF-8');
	header('Content-Disposition: attachment; filename="'.$filename.'"');


	echo $string;

	//and now exit
	exit();
}
?>
