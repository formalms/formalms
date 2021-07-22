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
 * @version 	$Id: lib.mimetype.php 113 2006-03-08 18:08:42Z ema $
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
function mimeDetect($fileName, $path = true, $big = false) {
	//find file ext
	$expFileName = explode('.', $fileName);
	$totPart = count($expFileName) - 1;

	$basepath = ($path ? 'mimetypes'.($big ? '32' : '').'/' : '');
	$extension = ($path ? '.png' : '');

	//recognize the ext
	switch(strtolower($expFileName[$totPart])) {
		//text
		case "txt" :	case "rtf" :
			return $basepath.'txt'.$extension;

		//documnet
		case "sxw" :	case "stw" :	case "doc" :	case "sdw" :	case "docx" :
		case "odt" :	case "ott" :	case "odm" :
			return $basepath.'doc'.$extension;

		//spreadsheet
		case "sdc" :	case "xls" :	case "xlw" :	case "xlt" :	case "stc" :	case "sxc" :	case "xlsx":
		case "ods" :	case "ots" :
			return $basepath.'xls'.$extension;

		//presentation
		case "sdd" :	case "sxi" :	case "sti" :	case "pot" :	case "pps" :	case "ppt" :	case "pptx":
		case "odp" :	case "otp" :
			return $basepath.'ppt'.$extension;

		//archive
		case "zip" :	case "rar" :	case "ace" :	case "arj" :	case "gz" :	case "tgz" :	case "bz2" :
		case "tar" :	case "7z" :
			return $basepath.'zip'.$extension;

		//html
		case "htm" :	case "html" :
			return $basepath.'htm'.$extension;

		//image
		case "psd" :	case "pdd" :	case "pdp" :
		case "pxr" :	case "bmp" :	case "rle" :	case "pcx" :
		case "tif" :	case "tiff" :	case "iff" :
		case "jpc" :	case "jp2" :	case "jb2" :
		case "jpg" :	case "gif" :	case "pic" :	case "eps" :	case "tga" :	case "png" :	case "svg" :
		case "odg" :	case "otg" :
			return $basepath.'img'.$extension;

		//stream
		case "asf" :	case "nsv" :	case "smi" :	case "ram" :
		//audio
		case "au" :	case "wav" :	case "mid" :	case "ogg" :	case "mp3" :	case "m4a" :
			return $basepath.'mp3'.$extension;

		//real
		case "rm" :	case "ra" :
			return $basepath.'real'.$extension;

		//movie
		case "mov" :	case "asx" :	case "avi" :	case "mpeg" :	case "mpg" :
		case "xvid" :	case "divx" :	case "wmv" :	case "mp4" :
		case "flv" :
			return $basepath.'movie'.$extension;

		//flash
		case "swc" :	case "swf" :	case "fla" :
			return $basepath.'fla'.$extension;

		//cad
		case "cad" :	case "dwg" :	case "dwt" :
			return $basepath.'cad'.$extension;

		//3D
		case "blen" :	case "mp" :	case "ma" :	case "mel" :	case "max" :
			return $basepath.'3d'.$extension;

		//db
		case "mdb" :	case "sql" :
		case "odb" :
			return $basepath.'source'.$extension;

		//pdf file
		case "pdf":
			return $basepath.'pdf'.$extension;

		//executable
		case "deb" :
			return $basepath.'deb'.$extension;
		case "rpm" :
			return $basepath.'rpm'.$extension;
		case "exe" :
			return $basepath.'exe'.$extension;

		//defaut type
		default: return $basepath.'unknow'.$extension;
	}
}

function mimetype($ext) {
	//finding correct mime type
	switch(strtolower($ext)) {

		case "nml" :
			return 'animation/narrative';
		case "tsp" :
			return 'application/dsptype';
		case "lcc" :
			return 'application/fastman';
		case "pfr" : 	case "pfr" :
			return 'application/font-tdpfr';
		case "spl" :
			return 'application/futuresplash';
		case "hqx" :
			return 'application/mac-binhex40';
		case "cpt" :
			return 'application/mac-compactpro';
		case "bin" : 	case "class" : 	case "dms" : 	case "exe" : 	case "sea" :
			return 'application/octet-stream';
		case "jar" : 	case "war" :
			return 'application/x-java-archive';
		case "jnlp" :
			return 'application/x-java-jnlp-file';
		case "oda" :
			return 'application/oda';

		case "pdf" :
			return 'application/pdf';
		case "ai" : 	case "eps" : 	case "ps" :
			return 'application/postscript';
		case "rtf" :
			return 'text/rtf';
		case "xps" :
			return 'application/vnd.ms-xpsdocument';

		case "smi" :
			return 'application/smil';
		case "svi" :
			return 'application/softvision';
		case "ttz" :
			return 'application/t-time';
		case "aab" :
			return 'application/x-authorware-bin';
		case "aam" :
			return 'application/x-authorware-map';
		case "aas" :
			return 'application/x-authorware-seg';
		case "bcpio" :
			return 'application/x-bcpio';
		case "bz2" :
			return 'application/x-bzip2';
		case "cqk" :
			return 'application/x-calquick';
		case "vcd" :
			return 'application/x-cdlink';
		case "ccn" :
			return 'application/x-cnc';
		case "cco" :
			return 'application/x-cocoa';
		case "Z" :
			return 'application/x-compress';
		case "cpio" :
			return 'application/x-cpio';
		case "csh" :
			return 'application/x-csh';
		case "dcr" : 	case "dir" : 	case "dxr" :
			return 'application/x-director';
		case "dvi" :
			return 'application/x-dvi';
		case "ebk" :
			return 'application/x-expandedbook';
		case "gtar" :
			return 'application/x-gtar';
		case "gz" :
			return 'application/x-gzip';
		case "hdf" :
			return 'application/x-hdf';
		case "cgi" :
			return 'application/x-httpd-cgi';
		case "js" : 	case "ls" : 	case "mocha" :
			return 'application/x-javascript';
		case "skd" : 	case "skm" : 	case "skp" : 	case "skt" :
			return 'application/x-koan';
		case "latex" :
			return 'application/x-latex';
		case "lha" : 	case "lzh" :
			return 'application/x-lzh';
		case "mps" :
			return 'application/x-mapserver';
		case "mct" :
			return 'application/x-mascot';
		case "mif" :
			return 'application/x-mif';
		case "cdf" : 	case "nc" :
			return 'application/x-netcdf';
		case "pac" :
			return 'application/x-ns-proxy-auto-config';
		case "mpp" :
			return 'application/x-pixelscooter';
		case "sh" :
			return 'application/x-sh';
		case "shar" :
			return 'application/x-shar';
		case "swf" :
			return 'application/x-shockwave-flash';
		case "spr" : 	case "sprite" :
			return 'application/x-sprite';
		case "spt" :
			return 'application/x-spt';
		case "sit" :
			return 'application/x-stuffit';
		case "sv4cpio" :
			return 'application/x-sv4cpio';
		case "sv4crc" :
			return 'application/x-sv4crc';
		case "tar" : 	case "tgz" :	 case "tz" :
			return 'application/x-tar';
		case "tcl" :
			return 'application/x-tcl';
		case "tex" :
			return 'application/x-tex';
		case "texi" : 	case "texinfo" :
			return 'application/x-texinfo';
		case "roff" : 	case "t" : 	case "tr" :
			return 'application/x-troff';
		case "man" :
			return 'application/x-troff-man';
		case "me" :
			return 'application/x-troff-me';
		case "ms" :
			return 'application/x-troff-ms';
		case "ustar" :
			return 'application/x-ustar';

		case "src" :
			return 'application/x-wais-source';
		case "xdm" : 	case "xdma" :
			return 'application/x-xdma';
		case "zip" :
			return 'application/zip';
		case "rar" :
			// or application/octet-stream?			
			return 'application/x-rar';
		case "7z" :
			return 'application/x-7z-compressed';

		// AUDIO
		case "aac" :
			return 'audio/aac';
		case "au" : 	case "snd" :
			return 'audio/basic';
		case "es" :
			return 'audio/echospeech';
		case "kar" : 	case "mid" : 	case "midi" :
			return 'audio/midi';
		case "mp2" : 	case "mp3" : 	case "mpga" :
			return 'audio/mpeg';
		case "m4a" :
			return 'audio/mp4';
		case "tsi" :
			return 'audio/tsplayer';
		case "ra" :
			return 'audio/vnd.rn-realaudio';
		case "vox" :
			return 'audio/voxware';
		case "aif" : 	case "aifc" : 	case "aiff" :
			return 'audio/x-aiff';
		case "aba" :
			return 'audio/x-bamba';
		case "cha" :
			return 'audio/x-chacha';
		case "mio" :
			return 'audio/x-mio';
		case "ogg" :	case "oga" :
			return 'audio/ogg';
		case "ram" : 	case "rm" :
			return 'audio/x-pn-realaudio';
		case "rpm" :
			return 'audio/x-pn-realaudio-plugin';
		case "vqf" : 	case "vql" :
			return 'audio/x-twinvq';
		case "vqe" :
			return 'audio/x-twinvq-plugin';
		case "wma" :
			return 'audio/x-ms-wma';
		case "wav" :
			return 'audio/x-wav';
		case "flac" :
			return 'audio/flac';

		case "ogx" :
			return 'application/ogg';

		case "csm" :
			return 'chemical/x-csml';
		case "emb" :
			return 'chemical/x-embl-dl-nucleotide';
		case "gau" :
			return 'chemical/x-gaussian-input';
		case "mol" :
			return 'chemical/x-mdl-molfile';
		case "mop" :
			return 'chemical/x-mopac-input';
		case "pdb" :
			return 'chemical/x-pdb';
		case "xyz" :
			return 'chemical/x-xyz';
		case "ivr" :
			return 'i-world/i-vrml';
		case "bmp" :
			return 'image/bmp';
		case "fif" :
			return 'image/fif';
		case "gif" :
			return 'image/gif';
		case "ief" :
			return 'image/ief';
		case "jpe" : 	case "jpeg" : 	case "jpg" :
			return 'image/jpeg';
		case "png" :
			return 'image/png';
		case "tif" : 	case "tiff" :
			return 'image/tiff';
		case "mcf" :
			return 'image/vasa';
		case "rp" :
			return 'image/vnd.rn-realpix';
		case "ras" :
			return 'image/x-cmu-raster';
		case "fh" : 	case "fh4" : 	case "fh5" : 	case "fh7" : 	case "fhc" :
			return 'image/x-freehand';
		case "jps" :
			return 'image/x-jps';
		case "pnm" :
			return 'image/x-portable-anymap';
		case "pbm" :
			return 'image/x-portable-bitmap';
		case "pgm" :
			return 'image/x-portable-graymap';
		case "ppm" :
			return 'image/x-portable-pixmap';
		case "rgb" :
			return 'image/x-rgb';
		case "xbm" :
			return 'image/x-xbitmap';
		case "xpm" :
			return 'image/x-xpixmap';
		case "swx" :
			return 'image/x-xres';
		case "xwd" :
			return 'image/x-xwindowdump';
		case "ptlk" :
			return 'plugin/listenup';
		case "waf" : 	case "wan" :
			return 'plugin/wanimate';
		case "css" :
			return 'text/css';
		case "htm" : 	case "html" :
			return 'text/html';
		case "txt" :
			return 'text/plain';
		case "rtx" :
			return 'text/richtext';
		case "tsv" :
			return 'text/tab-separated-values';
		case "rt" :
			return 'text/vnd.rn-realtext';
		case "etx" :
			return 'text/x-setext';
		case "sgm" : 	case "sgml" :
			return 'text/x-sgml';
		case "talk" :
			return 'text/x-speech';
		case "vcf" :
			return 'text/x-vcard';
		case "xml" :
			return 'text/xml';
		case "xsl" :
			return 'text/xsl';

		// VIDEO
		case "mpe" : 	case "mpeg" : 	case "mpg" :
			return 'video/mpeg';
		case "mov" : 	case "qt" :
			return 'video/quicktime';
		case "rv" :
			return 'video/vnd.rn-realvideo';
		case "viv" : 	case "vivo" :
			return 'video/vnd.vivo';
		case "vba" :
			return 'video/x-bamba';
		case "asf" : 	case "asx" :
			return 'video/x-ms-asf';
		case "avi" :
			return 'video/x-msvideo';
		case "qm" :
			return 'video/x-qmsys';
		case "movie" :
			return 'video/x-sgi-movie';
		case "tgo" :
			return 'video/x-tango';
		case "vif" :
			return 'video/x-vif';
		case "flv" :
			return 'video/x-flv';
		case "wmv" :
			return 'video/x-ms-wmv';
		case "webm" :
			return 'video/webm';
		case "mp4" :
			return 'video/mp4';
		case "ogv" :
			return 'video/ogg';
		case "3gp" :
			return 'video/3gpp';
		case "m3u8" :
			return 'application/x-mpegURL';
		case "ts" :
			return 'video/MP2T';
		case "vts" :
			return 'workbook/formulaone';
		case "pan" :
			return 'world/x-panoramix';
		case "ice" :
			return 'x-conference/x-cooltalk';
		case "d96" : 	case "mus" :
			return 'x-world/x-d96';
		case "svr" :
			return 'x-world/x-svr';
		case "vrml" : 	case "wrl" :
			return 'x-world/x-vrml';
		case "vrt" :
			return 'x-world/x-vrt';

		// ms document
		case "doc" : case "dot" :
			return 'application/msword';
		case "docx" :	case "dotx":
			return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
		case "xls":
			return 'application/vnd.ms-excel';
		case "xlsx":	case "xltx":
			return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		case "ppt" : 	case "pot" : 	case "pps" : 	case "ppz" :
			return 'application/vnd.ms-powerpoint';
		case "pptx": 	case "potx" :	case "sldx":
			return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
		//

		// opendocument
		case "odt" :
			return 'application/vnd.oasis.opendocument.text';
		case "ods" :
			return 'application/vnd.oasis.opendocument.spreadsheet';
		case "odp" :
			return 'application/vnd.oasis.opendocument.presentation';
		case "odg" :
			return 'application/vnd.oasis.opendocument.graphics';
		case "odc" :
			return 'application/vnd.oasis.opendocument.chart';
		case "odf" :
			return 'application/vnd.oasis.opendocument.formula';
		case "odi" :
			return 'application/vnd.oasis.opendocument.image';
		case "odm" :
			return 'application/vnd.oasis.opendocument.text-master';
		case "odb" :
			return 'application/vnd.oasis.opendocument.database';

		default:
			return 'application/octet-stream';

	}

	return false;
}

/**
 * Return array with extra mime type for given extension
 * The mime_type param is also modified by reference
 *
 * @param string $ext
 * @param array $mime_array (by reference)
 */
function getOtherMime($ext, & $mime_array) {
	switch(strtolower($ext)) {
		case "rtf" : {
			$mime_array[]='text/rtf';
			$mime_array[]='application/x-rtf';
			$mime_array[]='text/richtext';
			$mime_array[]='application/rtf';
			$mime_array[]='text/richtext';
		} break;
		case "doc" :
		case "docx": {
			$mime_array[]='application/msword';
			$mime_array[]='application/zip';
		} break;
		case "xls":
		case "xlsx":  {
			$mime_array[]='application/msword';
			$mime_array[]='application/vnd.ms-excel';
			$mime_array[]='application/vnd.ms-office';
			$mime_array[]='application/zip';
		} break;
		case "ppt":
		case "pptx": {
			$mime_array[]='application/msword';
			$mime_array[]='application/vnd.ms-powerpoint';
			$mime_array[]='application/vnd.ms-office';
			$mime_array[]='application/zip';
		} break;
		case "xml" : {
			$mime_array[]='application/xml';
		} break;
		case "aac" : {
			$mime_array[]='audio/x-hx-aac-adts';
			$mime_array[]='audio/x-hx-aac-adif';
			$mime_array[]='audio/mp4a-latm';
			$mime_array[]='audio/mp4a-latm';
		} break;
		case "flac" : {
			$mime_array[]='audio/x-flac';
		} break;
		case "m4a" : {
			$mime_array[]='audio/m4a';
			$mime_array[]='audio/x-m4a';
			$mime_array[]='audio/mp4a-latm';
			$mime_array[]='video/3gpp';
			$mime_array[]='audio/mpeg';
		} break;
		case "wma" : {
			$mime_array[]='video/x-ms-asf';
		} break;
		case "ra" : {
			$mime_array[]='audio/x-pn-realaudio';
			$mime_array[]='audio/x-realaudio';
			$mime_array[]='audio/x-pm-realaudio-plugin';
			$mime_array[]='video/x-pn-realvideo';
		} break;
		case "ogg" : {
			$mime_array[]='application/ogg';
			$mime_array[]='audio/x-ogg';
			$mime_array[]='application/x-ogg';
		} break;
		case "ogg" : {
			$mime_array[]='video/x-ms-asf';
			$mime_array[]='video/x-ms-wm';
			$mime_array[]='video/x-ms-wmx';
			$mime_array[]='application/x-ms-wmz';
			$mime_array[]='application/x-ms-wmd';
			$mime_array[]='video/x-ms-wvx';
			$mime_array[]='audio/x-ms-wax';
		} break;
		case "exe" : {
			$mime_array[]='application/x-dosexec';
		} break;

	}

	return $mime_array;
}

?>
