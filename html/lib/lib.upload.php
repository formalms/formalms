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
 * @author 		Emanuele Sandri <esandri@tiscali.it>
 * @version 	$Id: lib.upload.php 193 2006-03-31 07:31:01Z fabio $
 */

$ftpConn = NULL;	// Chache for the last connection

function sl_open_fileoperations() {
	$uploadType = Get::cfg('uploadType');
	if( $uploadType == "ftp" ) {
		return sl_open_fileoperations_ftp();
	} elseif( $uploadType == "cgi" ) {
		return TRUE;
	} else {
		return TRUE;
	}
}

function sl_close_fileoperations() {
	$uploadType = Get::cfg('uploadType');
	if( $uploadType == "ftp" ) {
		return sl_close_fileoperations_ftp();
	} elseif( $uploadType == "cgi" ) {
		return TRUE;
	} else {
		return TRUE;
	}
}

function sl_mkdir( $path, $mode ) {
	$uploadType = Get::cfg('uploadType');

	if( $uploadType == "ftp" ) {
		return sl_mkdir_ftp( $path, $mode );
	} elseif( $uploadType == "cgi" ) {
		return FALSE;
	} else {
		$result = mkdir( $GLOBALS['where_files_relative'].$path, $mode );
		return $result;
	}
}

function sl_fopen( $filename, $mode ) {
	$uploadType = Get::cfg('uploadType');

	$mfirst = $mode{0};
	if( $uploadType == "ftp" && $mfirst != 'r' ) {
		return sl_fopen_ftp( $filename, $mode );
	} elseif( $uploadType == "cgi" ) {
		return FALSE;
	} else {
        if (substr($filename,0,1) != '/') {
            return fopen($filename, $mode);
        }     
		return fopen( $GLOBALS['where_files_relative'].$filename, $mode);
	}
}

function sl_upload( $srcFile, $dstFile, $file_ext) {
	$uploadType = Get::cfg('uploadType', null);

	// check if the mime type is allowed by the whitelist
	// if the whitelist is empty all types are accepted
	require_once(_lib_.'/lib.mimetype.php');
	$upload_whitelist =Get::sett('file_upload_whitelist', 'rar,exe,zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv,jpeg');
	$upload_whitelist_arr =explode(',', trim($upload_whitelist, ','));
	if (!empty($upload_whitelist_arr)) {
		$valid_ext = false;
		$ext=strtolower(substr(strrchr($dstFile, "."), 1));
		if($ext!=""){
			$file_ext =strtolower(substr(strrchr($dstFile, "."), 1));
		}

		foreach ($upload_whitelist_arr as $k=>$v) { // remove extra spaces and set lower case
			$ext =trim(strtolower($v));
			$mt =mimetype($ext);
			if ($mt) { $mimetype_arr[]=$mt; }
			getOtherMime($ext, $mimetype_arr);
			if ($ext == $file_ext) {
				$valid_ext =true;
			}
		}
		$mimetype_arr = array_unique($mimetype_arr);
		if ( class_exists('finfo') && method_exists('finfo', 'file')) {
			$finfo =new finfo(FILEINFO_MIME_TYPE);
			$file_mime_type =$finfo->file($srcFile);
		}
		else {
			$file_mime_type =mime_content_type($srcFile);
		}
		if (!$valid_ext || !in_array($file_mime_type, $mimetype_arr)) {
			return false;
		}
	}
	$dstFile =stripslashes($dstFile);
	if( $uploadType == "ftp" ) {
		return sl_upload_ftp( $srcFile, $dstFile );
	} elseif( $uploadType == "cgi" ) {
		return sl_upload_cgi( $srcFile, $dstFile );
	} elseif( $uploadType == "fs" || $uploadType == null ) {
		return sl_upload_fs( $srcFile, $dstFile );
	} else {
        $event = new \appCore\Events\Core\FileSystem\UploadEvent($srcFile, $dstFile);
        \appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\FileSystem\UploadEvent::EVENT_NAME, $event);
		unlink($srcFile);
        return $event->getResult();
    }
}

function sl_touch( $filename, $time ) {
	$uploadType = Get::cfg('uploadType');
	if( $uploadType == "ftp" ) {
		return TRUE;
	} elseif( $uploadType == "cgi" ) {
		return TRUE;
	} else {
		return touch( $GLOBALS['where_files_relative'].$filename, $time );
	}
}

function sl_is_file( $filename ) {
	return @is_file($GLOBALS['where_files_relative'].$filename);
}

function sl_is_dir( $path ) {
	return @is_dir($GLOBALS['where_files_relative'].$path);
}

function sl_is_readable( $filename ) {
	return is_readable( $GLOBALS['where_files_relative'].$filename );
}

function sl_is_writeable( $filename ) {
	return is_writeable( $GLOBALS['where_files_relative'].$filename );
}

function sl_filesize( $filename ) {
	return filesize( $GLOBALS['where_files_relative'].$filename );
}

function sl_filemtime($filename ) {
	return filemtime( $GLOBALS['where_files_relative'].$filename );
}

function sl_file_exists( $filename ) {
	return file_exists( $GLOBALS['where_files_relative'].$filename );
}

function sl_chmod( $filename, $mode ) {
	return chmod( $GLOBALS['where_files_relative'].$filename, $mode );
}

function sl_copy( $srcFile, $dstFile ) {
	$uploadType = Get::cfg('uploadType');
	if( $uploadType == "ftp" ) {
		return sl_upload_ftp( $GLOBALS['where_files_relative'].$srcFile, $dstFile );
	} elseif( $uploadType == "cgi" ) {
		return sl_upload_cgi( $srcFile, $dstFile );
	} elseif( $uploadType == "fs" || $uploadType == null ) {
		return copy($GLOBALS['where_files_relative'].$srcFile, $GLOBALS['where_files_relative'].$dstFile);
	} else {
        $event = new \appCore\Events\Core\FileSystem\CopyEvent($srcFile, $dstFile);
        \appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\FileSystem\UploadEvent::EVENT_NAME, $event);

        return $event->getResult();
    }
}

/** file system implementation **/

function sl_upload_fs( $srcFile, $dstFile ) {
	if ($srcFile == _files_.$dstFile) return true;
	$re = move_uploaded_file($srcFile, _files_.$dstFile);
	if(!$re) die("Error on move_uploaded_file from: $srcFile to ".$dstFile);
	return $re;
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function sl_copyr($source, $dest)
{
    // Simple copy for a file
    if (is_file($GLOBALS['where_files_relative'].$source)) {
        return sl_copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($GLOBALS['where_files_relative'].$dest)) {
        sl_mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($GLOBALS['where_files_relative'].$source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        if ($dest !== "$source/$entry") {
            sl_copyr("$source/$entry", "$dest/$entry");
        }
    }

    // Clean up
    $dir->close();
    return true;
}


/** Ftp implementation **/

function sl_open_fileoperations_ftp() {
	$ftpuser = Get::cfg('ftpuser');
	$ftppass = Get::cfg('ftppass');
	$ftphost = Get::cfg('ftphost');
	$ftpport = Get::cfg('ftpport');

	$result = FALSE;

	$timeout = Get::cfg('ftptimeout', 0);
	if( $timeout == 0 ) {
		$timeout = ini_get('max_execution_time');
		if( $timeout == 0 ) {
			$timeout = 20;
		} elseif( $timeout > 60 ) {
			$timeout = 50;
		} else {
			$timeout = round(($timeout*8)/10);
		}
	}

	$GLOBALS['ftpConn'] = @ftp_connect( $ftphost, $ftpport, $timeout );
	if( $GLOBALS['ftpConn'] === FALSE ) {
		return FALSE;
	}
	if( @ftp_login($GLOBALS['ftpConn'], $ftpuser, $ftppass) )
		return TRUE;
	else
		return FALSE;
}

function sl_close_fileoperations_ftp() {
	if($GLOBALS['ftpConn'] !== false) ftp_close($GLOBALS['ftpConn']);
}

function sl_upload_ftp( $srcFile, $dstFile ) {
	$ftppath = Get::cfg('ftppath')._folder_files_;
	$ftpConn = $GLOBALS['ftpConn'];
	if( !ftp_put( $ftpConn, $ftppath.$dstFile, $srcFile, FTP_BINARY) ) {
		return FALSE;
	} /*
	if( ftp_site( $ftpConn, "CHMOD 0666 $ftppath"."$dstFile" ) === FALSE ) {
		return FALSE;
	}	else {
		return TRUE;
	}	*/
	return TRUE;
}

function sl_mkdir_ftp( $path, $mode = FALSE) {
	$ftppath = Get::cfg('ftppath')._folder_files_;
	$ftpConn = $GLOBALS['ftpConn'];
	if( !@ftp_mkdir($ftpConn, $ftppath.$path) )
		return FALSE;
	if( $mode !== FALSE ) {
		if( ftp_site( $ftpConn, "CHMOD 0777 $ftppath"."$path"  ) === FALSE ) {
			return FALSE;
		}	else {
			return TRUE;
		}
	}	else {
		return TRUE;
	}
	return TRUE;
}

function sl_fopen_ftp( $file, $mode ) {
 	// only create file then open it with fopen
	$ftppath = Get::cfg('ftppath')._folder_files_;
	$ftpConn = $GLOBALS['ftpConn'];
	if( !file_exists( $GLOBALS['where_files_relative'].$file ) ) {
   		if( !ftp_put( $ftpConn, $ftppath.$file, dirname(__FILE__)."/nullfile", FTP_BINARY ) ) {
  			return FALSE;
  		} else {
      		if( ftp_site( $ftpConn, "CHMOD 0666 $ftppath"."$file" ) === FALSE )
      			return FALSE;
	 	}
  	}
	$ret = @fopen( $GLOBALS['where_files_relative'].$file, $mode );
	return $ret;
}

/** CGI Implementation **/

function sl_upload_cgi( $srcFile, $dstFile ) {
	global $url;
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url."testcgi.sh?fname=".$dstFile);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);

	$hfileSrc = fopen( $srcFile, "rb" );
	$hfileDst = fopen( $dstFile, "wb" );

	while (!feof($hfileSrc)) {
	$buffer = fread($hfileSrc, 8192);
	fwrite( $hfileDst, $buffer );
	}

	fclose( $hfileSrc );
	fclose( $hfileDst );
	return TRUE;
}

/**
 * @param $path pathname for file retriving
 * @return bool
 **/

 function sl_unlink( $path ) {
     $uploadType = Get::cfg('uploadType', null);

     if( $uploadType == "fs" || $uploadType == "ftp" || $uploadType == null ) {
         if( !file_exists($GLOBALS['where_files_relative'].$path) ) return true;
         return @unlink($GLOBALS['where_files_relative'].$path);
     } else {
         $event = new \appCore\Events\Core\FileSystem\UnlinkEvent($path);
         \appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\FileSystem\UnlinkEvent::EVENT_NAME, $event);

         return $event->getResult();
     }


 }
 

?>
