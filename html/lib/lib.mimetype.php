<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
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
		case "sxw" :	case "stw" :	case "doc" :	case "sdw" :
			return $basepath.'doc'.$extension;

		//spreadsheet
		case "sdc" :	case "xls" :	case "xlw" :	case "xlt" :	case "stc" :	case "sxc" :
			return $basepath.'xls'.$extension;

		//presentation
		case "sdd" :	case "sxi" :	case "sti" :	case "pot" :	case "pps" :	case "ppt" :
			return $basepath.'ppt'.$extension;

		//archive
		case "zip" :	case "rar" :	case "ace" :	case "arj" :	case "gz" :	case "tgz" :	case "bz2" :
		case "tar" :
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
			return $basepath.'img'.$extension;

		//stream
		case "asf" :	case "nsv" :	case "smi" :	case "ram" :
		//audio
		case "au" :	case "wav" :	case "mid" :	case "ogg" :	case "mp3" :
			return $basepath.'mp3'.$extension;

		//real
		case "rm" :	case "ra" :
			return $basepath.'real'.$extension;

		//movie
		case "mov" :	case "asx" :	case "avi" :	case "mpeg" :	case "mpg" :
		case "xvid" :	case "divx" :
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
		case "pot" : 	case "pps" : 	case "ppt" : 	case "ppz" :
			return 'application/mspowerpoint';
		case "bin" : 	case "class" : 	case "dms" : 	case "exe" : 	case "jar" : 	case "sea" :
			return 'application/octet-stream';
		case "oda" :
			return 'application/oda';
		case "pdf" :
			return 'application/pdf';
		case "ai" : 	case "eps" : 	case "ps" :
			return 'application/postscript';
		case "rtf" :
			return 'application/rtf';
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
		case "xls" :
			return 'application/x-excel';
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
		case "tar" : 	case "tgz" :
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
		case "au" : 	case "snd" :
			return 'audio/basic';
		case "es" :
			return 'audio/echospeech';
		case "kar" : 	case "mid" : 	case "midi" :
			return 'audio/midi';
		case "mp2" : 	case "mp3" : 	case "mpga" :
			return 'audio/mpeg';
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
		case "ram" : 	case "rm" :
			return 'audio/x-pn-realaudio';
		case "rpm" :
			return 'audio/x-pn-realaudio-plugin';
		case "vqf" : 	case "vql" :
			return 'audio/x-twinvq';
		case "vqe" :
			return 'audio/x-twinvq-plugin';
		case "wav" :
			return 'audio/x-wav';
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
	}
	
	return false;
}


?>
