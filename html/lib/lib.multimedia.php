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
 *
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 * @version 	$Id: lib.multimedia.php 1000 2007-03-23 16:03:43Z fabio $
 */

// ----------------------------------------------------------------------------

/**
 * @param 	string 	$src_path 	is the path of the folder of the uploaded image
 * @param 	string 	$dst_path 	is the path of the folder of the generated thumbinal
 * @param 	string 	$fn 		is the name of the image file
 * @param 	int 	$width 		is the width of the generated thumbinal
 * @param 	int 	$height 	is the height of the generated thumbinal
 * @param 	bool 	$forcesize 	if true then force the size to the one specified also if it is smaller
 */
function createPreview($src_path, $dst_path, $fn, $width, $height, $forcesize=true) {

	require_once(_base_.'/lib/lib.upload.php');


	if(!extension_loaded('gd')) {
		return -1; // no gd
	}

	// The file
	$filename = $fn;
	$ext = strtolower(end(explode(".", $filename)));
	if(function_exists("getimagesize")) {

		// Get new dimensions
		list($width_orig, $height_orig) = getimagesize($src_path.$filename);

		if(($forcesize) || ($width_orig > $width) || ($height_orig > $height)) {

			if ($width && ($width_orig < $height_orig)) {
				$width = ($height / $height_orig) * $width_orig;
			} else {
				$height = ($width / $width_orig) * $height_orig;
			}
			// Resample
			$image = NULL;
			$image_p = imagecreatetruecolor($width, $height);
			switch($ext) {
				case "jpeg" :
				case "jpg" : {

					$image = imagecreatefromjpeg($src_path.$filename);
				};break;
				case "png" : {

					$image = imagecreatefrompng($src_path.$filename);
				};break;
				case "gif" : {

					if(!function_exists("imagecreatefromgif")) return -2;
					$image = imagecreatefromgif($src_path.$filename);
				};break;
				default : {
					// unknow format
					return -3;
				};break;
			}
			if($image == NULL) return -2; // can't open the image
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

		} else {
			// If there is no need to resize it..

			// If the destination is the same of the source we don't need to do anything..
			if ($src_path.$filename == $dst_path.$filename)
				return 1;
			// TODO:  ...
			/*else {
			} */
		}

		// Output
		$outname = $dst_path.$fn;
		sl_unlink($outname);

		if ( (($ext == "jpg") || ($ext == "jpeg")) && (function_exists("imagejpeg"))) {
			return imagejpeg($image_p, $outname, 90);
		}
		else if (($ext == "png") && (function_exists("imagejpeg"))) {
			$color = ImageColorAt($image_p, 1, 1);
			imagecolortransparent($image_p, $color);
			return imagepng($image_p, $outname);
		}
		else if (($ext == "gif") && (function_exists("imagegif")) && (function_exists("imagetruecolortopalette"))) {
			$image_gif = imagetruecolortopalette($image_p, true, 256);
			return imagegif($image_p, $outname);
		}

	}

}

/**
 * create a resized image from a tmp file
 * @param string 	$tmp_pathfile 	the location of tmp file
 * @param string 	$dst_pathfile 	the destination file
 * @param string 	$original_name 	the original name of the uploaded file
 * @param int 		$width 			the width to be forced
 * @param int 		$height 		the height to be forced
 * @param bool 		$if_not_load 	if true, if the image cannot be resized it will be upload with his native size
 *
 * @return int	an error is a negative int, if 0 all is ok
 *			-1 ther isn't a required function, or the entire module
 *			-2 general error
 *			-3 image type not suported
 *			-4 impossibile resize and the width or height exceed the param
 */
function createImageFromTmp($tmp_pathfile, $dst_pathfile, $original_name, $width, $height, $if_not_load = true) {

	require_once(_base_.'/lib/lib.upload.php');

	if(!function_exists("getimagesize") ) {
		if($if_not_load !== true) return -1;
		else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
	}

	$dst_pathfile_rel 	= $GLOBALS['where_files_relative'].$dst_pathfile;
	$file_extension 	= strtolower(end(explode(".", $original_name)));
	$img_info 			= @getimagesize($tmp_pathfile);

	if($img_info === false) return -2;

	$tmp_width 		= $img_info[0];
	$tmp_heigth 	= $img_info[1];

	// Control if is needed to resize
	if(($tmp_width > $width) || ($tmp_heigth > $height)) {

		// I must control if all the required function for resample exists =================================
		if(!extension_loaded('gd')
			|| !function_exists("imagecopyresampled")
			|| !function_exists("imagecreatetruecolor")) {

			if($if_not_load !== true) return -1;
			else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
		}

		// Resample
		if ($width && ($tmp_width < $tmp_heigth)) {
			$width = ($height / $tmp_heigth) * $tmp_width;
		} else {
			$height = ($width / $tmp_width) * $tmp_heigth;
		}

		$image 		= NULL;
		$image_p 	= imagecreatetruecolor($width, $height);
		switch($file_extension) {
			// =Jpeg=========================================================================================
			case "jpeg" :
			case "jpg" : {

				if(!function_exists("imagecreatefromjpeg")) {
					if($if_not_load !== true) return -4;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}
				$image = imagecreatefromjpeg($tmp_pathfile);
			};break;
			// =Png==========================================================================================
			case "png" : {

				if(!function_exists("imagecreatefrompng")) {
					if($if_not_load !== true) return -4;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}
				$image = imagecreatefrompng($tmp_pathfile);
			};break;
			// =Gif==========================================================================================
			case "gif" : {

				if(!function_exists("imagecreatefromgif")) {
					if($if_not_load !== true) return -4;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}
				$image = imagecreatefromgif($tmp_pathfile);
			};break;
			// =Unknow format================================================================================
			default: {

				if($if_not_load !== true) return -3;
				else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
			}
		}
		if($image == NULL) {
			if($if_not_load !== true) return -2;
			else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
		}
		if(!imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $tmp_width, $tmp_heigth)) {
			if($if_not_load !== true) return -2;
			else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
		}
		sl_unlink($dst_pathfile);

		switch($file_extension) {

			case "jpeg" :
			case "jpg" : {

				if(!function_exists("imagejpeg")) {
					if($if_not_load !== true) return -4;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}
				if (imagejpeg($image_p, $dst_pathfile_rel, 90)) {
				    return 0;
                } else {
					if($if_not_load !== true) return -1;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}
			};break;
			case "png" : {

				if(!function_exists("ImageColorAt")) return -4;
				if(!function_exists("imagecolortransparent")) {
					if($if_not_load !== true) return -4;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}
				if(!function_exists("imagepng")) return -4;

				$color = ImageColorAt($image_p, 1, 1);
				imagecolortransparent($image_p, $color);
				if (imagepng($image_p, $dst_pathfile_rel) > 0) {
				    return 0;
                } else {
					if($if_not_load !== true) return -1;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}
			};break;
			case "gif" : {

				if(!function_exists("imagegif") || !function_exists("imagetruecolortopalette")) {
					if($if_not_load !== true) return -4;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}

				$image_gif = imagetruecolortopalette($image_p, true, 256);
				if (imagegif($image_p, $dst_pathfile_rel) > 0) {
				    return 0;
                } else {
					if($if_not_load !== true) return -1;
					else return uploadImageWitouthResize($tmp_pathfile, $dst_pathfile);
				}
			};break;
		}
	}
	// no resize needed
	sl_unlink($dst_pathfile);
	if(sl_upload($tmp_pathfile, $dst_pathfile, $file_extension)) return 0;
	return -2;
}

function uploadImageWitouthResize($tmp_pathfile, $dst_pathfile) {

	sl_unlink($dst_pathfile);
	if(sl_upload($tmp_pathfile, $dst_pathfile)) return 0;
	return -2;
}

/**
 * @param string 	$fname name of the file we want to check
 *
 * @return boolean FALSE if not a media; TRUE if valid media
 */
function isMedia($fname) {
	$res=FALSE;

	if (getMediaType($fname) !== FALSE)
		$res=TRUE;

	return $res;
}


/**
 * @param string 	$fname name of the file we want to check
 *
 * @return mixed FALSE if not a media, else the media type string
 */
function getMediaType($fname) {
	$res=FALSE;

	if (preg_match("/http[s]?:\\/\\//i", $fname)) {
		$res ="streaming";
		return $res;
	}

	$ext=end(explode(".", strtolower($fname)));

	$image_ext_arr=getImageExtList();
	$flash_ext_arr=getFlashExtList();
	$video_ext_arr=getVideoExtList();
	$audio_ext_arr=getAudioExtList();

	if (in_array($ext, $image_ext_arr))
		$res="image";
	else if (in_array($ext, $flash_ext_arr))
		$res="flash";
	else if (in_array($ext, $video_ext_arr))
		$res="video";
	else if (in_array($ext, $audio_ext_arr))
		$res="audio";

	return $res;
}


function getImageExtList() {
	return array("bmp", "jpg", "jpeg", "png", "mng", "gif", "svg");
}


function getFlashExtList() {
	return array("swf");
}


function getVideoExtList() {
	return array("avi", "mpg", "mpeg", "divx", "ogm", "wmv", "asf",
	             "mov", "rm", "mp4", "3gp", "omf", "qt", "moov", "flv", "swf");
}


function getAudioExtList() {
	return array("mp3", "wav", "ogg", "aif", "aiff", "aac", "ac3",
	             "mpa", "ram", "wma");
}


/**
 * @param string 	$fname name of the swf file
 *
 * @return array :
 *               - error (FALSE || errorcode)
 *               - fname
 *               - magic
 *               - version
 *               - size
 *               - width
 *               - height
 *               - fps
 *               - frames
 */
function getSwfInfoArray($fname) {
	$res=array();
	require_once($GLOBALS["where_framework"]."/addons/swfheader/swfheader.class.php");

	$clean_fname=(strpos($fname, "?") !== FALSE ? preg_replace("/(\?.*)/", "", $fname) : $fname);

	$swf=new swfheader(FALSE);
	$swf->loadswf($clean_fname);

	if (!$swf->valid) {

		if (file_exists($clean_fname))
			$res["error"]=-1; // Invalid SWF file
		else
			$res["error"]=-2; // File not found

	}
	else {

		$res["error"]=FALSE;
		$res["fname"]=$swf->fname;
		$res["magic"]=$swf->magic;
		$res["version"]=$swf->version;
		$res["size"]=$swf->size;
		$res["width"]=$swf->width;
		$res["height"]=$swf->height;
		$res["fps"]=$swf->fps[1].".".$swf->fps[0];
		$res["frames"]=$swf->frames;

	}

	return $res;
}


function getFlashPluginCode($src, $bgcolor=FALSE, $width=FALSE, $height=FALSE) {

	$res = "";
	$flashvars = FALSE;
	$pos = strpos($src, "?");
	if($pos !== FALSE) {
		$flashvars = substr($src, $pos+1);
		$src = substr($src, 0, $pos);
	}

	$info = getSwfInfoArray($src);

	if($info["error"] !== FALSE) {
		return "\n\n<!-- Swf Load Error: ".$info["error"]." - File: ".$src." -->\n\n";
	}

	$bg = ( $bgcolor !== FALSE 	? $bgcolor 	: "#FFFFFF" );
	$w 	= ( $width !== FALSE 	? $width 	: $info["width"] );
	$h 	= ( $height !== FALSE 	? $height 	: $info["height"] );
	if($width !== false && $height === false) {
		$w = $width;
		$h = round(($info["height"] /  $info['width']) * $width);
	}
	if($width === false && $height !== false) {
		$w = round(($info["width"] /  $info['height']) * $height);
		$h = $height;
	}

	$res .= '<object type="application/x-shockwave-flash" data="'.$src.'" width="'.$w.'" height="'.$h.'" align="middle">'."\n";
	if(($flashvars !== FALSE) && (!empty($flashvars))) {
		$res .= '	<param name="flashvars" value="'.$flashvars.'" />'."\n";
	}
	$res .= '	<param name="movie" value="'.$src.'" />'."\n";
	$res .= '	<param name="bgcolor" value="'.$bg.'" />'."\n";
	$res .= '	<param name="quality" value="high" />'."\n";
	$res .= '	<param name="wmode" value="transparent">'."\n";

	$res .= '</object>	'."\n";

	// Commented code is not XHTML 1.1 compatible
	/* $res.='<embed src="'.$src.'" ';
	$res.='quality="high" bgcolor="'.$bg.'" width="'.$w.'" height="'.$h.'" ';
	$res.='align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" ';
	$res.='pluginspage="http://www.macromedia.com/go/getflashplayer" />'; */

	return $res;
}

function isPossibleEmbedPlay($path, $file_name, $ext=FALSE) {

	if ($ext === FALSE) {
		$ext=strtolower(end(explode(".", $file_name)));
	}

	$rel_path = $GLOBALS['where_files_relative'].$path;
	switch($ext) {
		case "jpg" :
		case "jpeg" :
		case "png" :
		case "gif" :

		case "wma" :
		case "wmv" :
		case "mpg" :
		case "avi" :
		case "mp3" :
		case "mp4" :
		case "mov" :
		case "flv" :
		case "swf" : { return true; };break;
	}
	return false;
}

function getEmbedPlay($path, $file_name, $ext=FALSE, $width = false, $height = false, $if_unknow_send = true, $alt_image = false, $path_from_player=FALSE, $force_path=FALSE) {

	if ($path_from_player === FALSE)
	{
		$path_from_player="../../";
		//$path_from_player="";
	}

	if ($ext === FALSE) {
		$ext=strtolower(end(explode(".", $file_name)));
	}

	if ($force_path) {
		$rel_path = $path;
	}
	else {
		$rel_path = $GLOBALS['where_files_relative'].$path;
	}

	switch($ext) {

		case "jpg" :
		case "jpeg" :
		case "png" :
		case "gif" :  {

			if($width == false && $height === false) { $width = '450';  $height = '450'; }
			$img_size = @getimagesize($rel_path.$file_name);
			return '<img src="'.$rel_path.$file_name.'" alt="'.( $alt_image != false ? $alt_image : $file_name ).'"'
				.( $img_size[0] == ''
					? ' width="'.$width.'px"'
					: $img_size[0] > $width ? ' width="'.$width.'px"' : '' )
				.( $img_size[1] == ''
					? ' height="'.$height.'px"'
					: $img_size[1] > $height ? ' height="'.$height.'px"' : '' )
				.' />';
		};break;
		case "wma" : {

			return '<object width="'.$width.'" height="'.$height.'">'
				.'<param name="movie" value="'.$rel_path.$file_name.'"></param>'
				.'<embed src="'.$rel_path.$file_name.'" type="video/mpeg" width="'.$width.'" height="'.$height.'"></embed>'
				.'</object>';
		};break;
		case "wmv": {
			$res ="";
			$res.='<object width="'.$width.'" height="'.$height.'" classid="CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95" type="application/x-oleobject" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112">';
			$res.='<param name="filename" value="'.$rel_path.$file_name.'">';
			$res.='<param name="Showcontrols" value="True">';
			$res.='<param name="autostart" value="false">';
			$res.='<embed type="application/x-mplayer2" src="'.$rel_path.$file_name.'" width="'.$width.'" height="'.$height.'" autostart="false"></embed>';

			$res.='</object>';

			return $res;
		} break;
		case "mpg" : {

			return '<object width="'.$width.'" height="'.$height.'">'
				.'<param name="movie" value="'.$rel_path.$file_name.'"></param>'
				.'<embed src="'.$rel_path.$file_name.'" type="video/mpeg" width="'.$width.'" height="'.$height.'"></embed>'
				.'</object>';
		};break;
		case "mov" : {

			return '<object width="'.$width.'" height="'.$height.'">'
				.'<param name="movie" value="'.$rel_path.$file_name.'"></param>'
				.'<embed src="'.$rel_path.$file_name.'" type="video/quicktime" width="'.$width.'" height="'.$height.'"></embed>'
				.'</object>';
		};break;
		case "flv" : {

			return getDoceboFlashPlayer($path_from_player.$path.$file_name, $width, $height);
		} break;
		case "swf" : {

			return getFlashPluginCode($path.$file_name);
		};break;

		case "mp3" : {

			$converted_filename = implode('_', array_slice(explode('_', $file_name), 3));
			$converted_filename = implode('.', array_slice(explode('.', $converted_filename), 0, -1));

			return getDoceboFlashAudioPlayer($path_from_player.$path.$file_name, $converted_filename, false, $width, $height);
			/* return '<object type="application/x-shockwave-flash" data="'.$GLOBALS['where_framework_relative'].'/addons/players/playerDoceboMp3.swf" height="90" width="295">'
				.'	<param name="flashvars" value="&stream=false&mp3_name='.$path.$file_name.'&url_server=null" />'
				.'	<param name="movie" value="'.$GLOBALS['where_framework_relative'].'/addons/players/playerDoceboMp3.swf" />'
				.'	<param name="quality" value="high" />'
				.'</object>'; */
		};break;



		default: {
			if(!$if_unknow_send) return false;
			require_once(_base_.'/lib/lib.download.php' );

			sendFile($path, $file_name, false);
			exit();
		};break;
	}
	return false;
}


function getStreamingEmbed($url, $ext=FALSE, $filename=FALSE) {
	$res ="";

	if (isYouTube($url)) {
		$video_id =getYouTubeId($url);
		$res =getYouTubeCode($video_id);
		return $res;
	}

	if ($ext === FALSE) {

		if ($filename === FALSE) {
			$filename =basename($url);
			$filename =(strpos($filename, "?") !== FALSE ? preg_replace("/(\?.*)/", "", $filename) : $filename);
		}

		$ext =end(explode(".", $filename));
	}

	switch ($ext) {

		case "flv": {
			$res.=getDoceboFlashPlayer($url);
		} break;

		case "swf": {
			$res.=getFlashPluginCode($url);
		} break;
		case "wmv": {
			$res ="";
			$res.='<object width="400px"  height="300px" classid="CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95" type="application/x-oleobject" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112">';
			$res.='<param name="filename" value="'.$url.'">';
			$res.='<param name="Showcontrols" value="True">';
			$res.='<param name="autostart" value="false">';
			$res.='<embed type="application/x-mplayer2" src="'.$url.'"  height="300px" width="400px" autostart="false"></embed>';

			$res.='</object>';

			return $res;
		} break;
	}

	return $res;
}


function getDoceboFlashPlayer($src, $width = false, $heigth = false) {

	$video =urlencode($src);
	// TODO: change the video path to have it more flexible
	return getFlashPluginCode($GLOBALS["where_framework_relative"]."/addons/players/playerDocebo.swf?video=".$video."", false, $width, $heigth);
}


function getDoceboFlashAudioPlayer($src, $song_title = false, $url_server = false, $width = false, $heigth = false) {

	if($song_title === false) Lang::t("_UNTITLED_SONG");
	if($url_server === false) $url_server = 'null';
	$song_title = urlencode(stripslashes($song_title));
	$file =urlencode($src);
	// TODO: change the video path to have it more flexible
	return getFlashPluginCode($GLOBALS["where_framework_relative"]."/addons/players/playerDoceboMp3.swf?mp3_name=".$file."&song_title=".$song_title."&url_server=".$url_server."", false, $width, $heigth);
}


function isYouTube($url) {
	$yt ="http://www.youtube.com/watch?v=";
	if (strtolower(substr($url, 0, strlen($yt))) == $yt) {
		$res =TRUE;
	}
	else {
		$res =FALSE;
	}
	return $res;
}


function getYouTubeId($url) {
	return preg_replace("/.*\\?v=([^&?\\s]*)/si", "\$1", $url);
}


function getYouTubeCode($video_id) {
	$res ="";
	$res.='<object width="425" height="350">';
	$res.='<param name="movie" value="http://www.youtube.com/v/'.$video_id.'"></param>';
	$res.='<param name="wmode" value="transparent"></param>';
	$res.='<embed src="http://www.youtube.com/v/'.$video_id.'" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed>';
	$res.='</object>';

	return $res;
}


?>