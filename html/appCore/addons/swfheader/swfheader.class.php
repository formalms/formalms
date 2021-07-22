<?PHP 
//-----------------------------------------------------------------------------
// SWF HEADER - version 1.0
// Small utility class to determine basic data from a SWF file header
// Does not need any php-flash extension, based on raw binary data reading
//----------------------------------------------------------------------------- 
//	SWFHEADER CLASS - PHP SWF header parser
//	Copyright (C) 2004  Carlos Falo Hervás
//
//	This library is free software; you can redistribute it and/or
//	modify it under the terms of the GNU Lesser General Public
//	License as published by the Free Software Foundation; either
//	version 2.1 of the License, or (at your option) any later version.
//
//	This library is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//	Lesser General Public License for more details.
//
//	You should have received a copy of the GNU Lesser General Public
//	License along with this library; if not, write to the Free Software
//	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//----------------------------------------------------------------------------- 

class swfheader {

	var $debug ;				// Output DEBUG info
	var $fname ;				// SWF file analyzed
	var $magic ;				// Magic in a SWF file (FWS or CWS)
	var $compressed ;		// Flag to indicate a compressed file (CWS)
	var $version ;			// Flash version
	var $size ;					// Uncompressed file size (in bytes)
	var $width ;				// Flash movie native width
	var $height ;				// Flash movie native height
	var $valid ;				// Valid SWF file
	var $fps ;					// Flash movie native frame-rate
	var $frames ;				// Flash movie total frames

	//--------------------------------------------------------------------------- 	
	// swfheader($debug) : 	Constructor, basically does nothing but initilize 
	//											debug and data fields
	//--------------------------------------------------------------------------- 
	function swfheader($debug = false) {
		$this->debug = $debug ;
		$this->init() ;
	  }

	//--------------------------------------------------------------------------- 	
	// init() : initialize the data fields to "empty" values
	//--------------------------------------------------------------------------- 
	function init() {
		$this->valid 			= false ;
		$this->fname		  = "" ;
		$this->magic			= "" ;
		$this->compressed = false ;
		$this->version 		= 0 ;
		$this->width 			= 0 ;
		$this->height			= 0 ;
		$this->size				= 0 ;
		$this->frames			= 0 ;
		$this->fps[] 			= Array() ;
		if ($this->debug) echo "DEBUG: Data values initialized<br>" ;
	  }

	//---------------------------------------------------------------------------
	// loadswf($filename) : loads $filename and stores data from it's header
	//--------------------------------------------------------------------------- 
	function loadswf($filename) {
		$this->fname = $filename ;
		$fp = @fopen($filename,"rb") ;
		if ($fp) {
			if ($this->debug) echo "DEBUG: Opened " . $this->fname . "<br>" ;
			// Read MAGIC FIELD
			$this->magic = fread($fp,3) ;
			if ($this->magic!="FWS" && $this->magic!="CWS") {
				if ($this->debug) echo "DEBUG: " . $this->fname . " is not a valid/supported SWF file<br>" ;
				$this->valid =  0 ;
			} else {
				// Compression
				if (substr($this->magic,0,1)=="C") $this->compressed = true ;
				else $this->compressed = false ;
				if ($this->debug) echo "DEBUG: Read MAGIC signature: " . $this->magic . "<br>" ;
				// Version
				$this->version = ord(fread($fp,1)) ;
				if ($this->debug) echo "DEBUG: Read VERSION: " . $this->version . "<br>" ;
				// Size
				$lg = 0 ;
				// 4 LSB-MSB
				for ($i=0;$i<4;$i++) {
					$t = ord(fread($fp,1)) ;
					if ($this->debug) echo "DEBUG: Partial SIZE read: " . ($t<<(8*$i)) . "<br>" ;
					$lg += ($t<<(8*$i)) ;
					}
				$this->size = $lg ;
				if ($this->debug) echo "DEBUG: Total SIZE: " . $this->size . "<br>" ;
				// RECT... we will "simulate" a stream from now on... read remaining file
				$buffer = fread($fp,$this->size) ;
				if ($this->compressed) {
					// First decompress GZ stream
					$buffer = gzuncompress($buffer,$this->size) ;
					}
				$b 			= ord(substr($buffer,0,1)) ;
				$buffer = substr($buffer,1) ;
				$cbyte 	= $b ;
				$bits 	= $b>>3 ;
				if ($this->debug) echo "DEBUG: RECT field size: " . $bits . " bits<br>" ;
				$cval 	= "" ;
				// Current byte
				$cbyte &= 7 ;
				$cbyte<<= 5 ;
				// Current bit (first byte starts off already shifted)
				$cbit 	= 2 ;
				// Must get all 4 values in the RECT
				for ($vals=0;$vals<4;$vals++) {
					$bitcount = 0 ;
					while ($bitcount<$bits) {
						if ($cbyte&128) {
							$cval .= "1" ;
						} else {
							$cval.="0" ;
							}
						$cbyte<<=1 ;
						$cbyte &= 255 ;
						$cbit-- ;
						$bitcount++ ;
						// We will be needing a new byte if we run out of bits
						if ($cbit<0) {
							$cbyte	= ord(substr($buffer,0,1)) ;
							$buffer = substr($buffer,1) ;
							$cbit = 7 ;
							}
					  }
					// O.k. full value stored... calculate
					$c 		= 1 ;
					$val 	= 0 ;
					// Reverse string to allow for SUM(2^n*$atom)
					if ($this->debug) echo "DEBUG: RECT binary value: " . $cval  ;
					$tval = strrev($cval) ;
					for ($n=0;$n<strlen($tval);$n++) {
						$atom = substr($tval,$n,1) ;
						if ($atom=="1") $val+=$c ;
						// 2^n
						$c*=2 ;
					  }
					// TWIPS to PIXELS
					$val/=20 ;
					if ($this->debug) echo " (" . $val . ")<br>" ;
					switch ($vals) {
						case 0:
							// tmp value
							$this->width = $val ;
						break ;
						case 1:
							$this->width = $val - $this->width ;
						break ;
						case 2:
							// tmp value
							$this->height = $val ;
						break ;
						case 3:
							$this->height = $val - $this->height ;
						break ;
					  }
					$cval = "" ;
				  }
				// Frame rate
				$this->fps = Array() ;
				for ($i=0;$i<2;$i++) {
					$t 			= ord(substr($buffer,0,1)) ;
					$buffer = substr($buffer,1) ;
					$this->fps[] = $t ;
					}
				if ($this->debug) echo "DEBUG: Frame rate: " . $this->fps[1] . "." . $this->fps[0] . "<br>" ;
				// Frames
				$this->frames = 0 ;
				for ($i=0;$i<2;$i++) {
					$t 			= ord(substr($buffer,0,1)) ;
					$buffer = substr($buffer,1) ;
					$this->frames += ($t<<(8*$i)) ;
					}
				if ($this->debug) echo "DEBUG: Frames: " . $this->frames . "<br>" ;
				fclose($fp) ;
				if ($this->debug) echo "DEBUG: Finished processing " . $this->fname . "<br>" ;
				$this->valid =  1 ;
	  		}
		} else {
			$this->valid = 0 ;
			if ($this->debug) echo "DEBUG: Failed to open " . $this->fname . "<br>" ;
		  }
		return $this->valid ;
	  }

	//--------------------------------------------------------------------------- 	
	// show() : report to screen all the header info
	//--------------------------------------------------------------------------- 
	function show() {
	  if ($this->valid) {
			// FNAME
			echo "<b>FILE: " . $this->fname . "</b><br>" ;
			// Magic
			echo "<b>MAGIC:</b> " . $this->magic ;
			if ($this->compressed) echo " (COMPRESSED)" ;
			echo "<br>" ;
			// Version
			echo "<b>VERSION:</b> " . $this->version . "<br>" ;
			// Size
			echo "<b>SIZE:</b> " . $this->size . " bytes <br>" ;
			// FRAMESIZE
			echo "<b>WIDHT:</B> " . $this->width . "<br>";
			echo "<b>HEIGHT:</B> " . $this->height . "<br>" ;
			// FPS
			echo "<b>FPS:</b> " . $this->fps[1] . "." . $this->fps[0] . " Frames/s <br>" ;
			// FRAMES
			echo "<b>FRAMES:</b> " . $this->frames . " FRAME <br>" ;
		} else {
			if (file_exists($this->fname))
				echo $this->fname . "is not a valid SWF file<br>" ;
			else
				if ($this->fname=="")
					echo "SWFHEADER->SHOW : No file loaded<br>" ;
				else
					echo "SWFHEDAR->SHOW : " . $this->fname . "was not found<br>" ;
		  }
	  }
		
	//---------------------------------------------------------------------------
	// display($trans) : just echo <OBJECT>/<EMBED> tags for the parsed file, if
	//									 trans is set, WMODE is set to transparent
	//--------------------------------------------------------------------------- 
	function display($trans = false, $qlty = "high", $bgcolor = "#ffffff", $name = "") {
		
		$endl = chr(13) ;
		
		if ($this->valid) {
		  if ($name=="") $name = substr($this->fname,0,strrpos($this->fname,".")) ;
			echo '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=' . $this->version . ',0,0,0" width="' . $this->width . '" height="' . $this->height . '" id="' . $name . '" align="middle">' . $endl ;
			echo '<param name="allowScriptAccess" value="sameDomain" />' . $endl ;
			if ($trans) {
				echo '<param name="wmode" value="transparent" />' . $endl ;
			  }
			echo '<param name="movie" value="' . $this->fname . '" />' . $endl ;
			echo '<param name="quality" value="' . $qlty . '" />' . $endl ;
			echo '<param name="bgcolor" value="' . $bgcolor .'" />' . $endl ;
			echo '<embed src="' . $this->fname . '" ';
			if ($trans) echo 'wmode="transparent" ' ;
			echo 'quality="' . $qlty . '" bgcolor="' . $bgcolor . '" width="' . $this->width . '" height="' . $this->height . '" name="' . $name . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />' . $endl ;
			echo '</object>' . $endl ;
		} else {
			if ($this->debug) {
			  if ($this->fname=="") {
					echo "SWFHEADER->DISPLAY : No loaded file in the object<br>" ;
				} else {
					if (file_exists($this->fname)) {
						echo "SWFHEADER->DISPLAY : " . $this->fname . " is not a valid SWF file<br>" ;
					} else {
						echo "SWFHEADER->DISPLAY : " . $this->fname . " was not found<br>" ;
						}
					}
				}
		  }
	}
}
?>

