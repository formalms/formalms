===== BASIC DOCUMENTATION FOR SWFHEADER CLASS =====

0.- INTRODUCTION AND EXTENTS

I implemented this class to allow for native PHP coding support to get basic
information from SWF files (i.e. to allow dynamic SWF banner systems with 
non-fixed banner sizes or flash versions)

I will be working a bit more on this class and maybe I'll be merging it with 
file_finder class for a common locate-and-show class.

1.- BASICS OF OPERATION

The class works on 4 basic functions

PUBLIC INTERFACE VARIABLES:

  valid 			: boolean - The processed file was vaild
  fname 			: string 	- Filename of the file we parsed
  compressed 	: boolean - True on compressed files, file size won't match 
													value in size member variable (see below)
  version 		: numeric - Flash version for the file (useful for generating 
													OBJECT plug-in check)
  width 			: numeric - Movie width (pixels)
  height 			: numeric - Movie height (pixels)
	size				: numeric - Size in bytes for the movie, it is the uncompressed
													size so might not mach the real file size on movies
													that are compressed (see compressed above)
  frames 			: numeric - total frames in the movie
  fps[] 			: Array 	- 2 values array holding FPS in 8.8 (array[1].array[0]) 
													format

PUBLIC INTERFACE METHODS:

  function swfheader($debug=false) ;
  	The constructor:
		$debug -> determine if the class shows debug information while processing the file

		Just creates the object and initialized it to "empty" values
		
  function init()
		Initialization

		Clears all data from previous uses		
  
  function loadswf($filename) ;
  	The SWF header parser:
		$filename -> file (with full path) to parse

		Opens the file and gets all the info from it's header (based on Flash 6/MX specs).
		Returns 1/0 on success/error

  function show() ;
  	Print report:

		Shows on-screen a report for all the analyzed file data

  function display($trans = false, $qlty = "high", $bgcolor = "#ffffff", $name = "") ;
  	Echo <OBJECT>/<EMBED> code for the loaded file:
		$trans 		-> transparency (boolean) sets WMODE to transparent if true
		$qlty  		-> movie quality "high" or "low"
		$bgcolor 	-> background color for the movie (if not $trans)
		$name			-> ID/NAME for the object/embed, defaults to fname without .swf extension
		
		Generates HTML <OBJECT> & <EMBED> code for the parsed file based on the data just
		gathered from the header information.

		
2.- Tech notes 
 
  In order to manage CWS (compressed) files the class MUST simulate a buffer 
	meaning it has to read the full file on a string and fetch characters 
	manually, making it a bit memory hungry for big files (wich also might need to
	decompress in memory).
 
3.- The SWF header format

	This information is based on Macromedia Flash MX official SWF format specs and
	some hex reading in real files itself.
	
	MAGIC 	: 3 bytes, 'F' or 'C', 'W', 'S', a C on the first byte states compression
	VERSION : 1 byte, i.e. 5 , 6, 7
	SIZE 		: 4 bytes, uncompressed size in LSB-MSB format ([0]+[1]<<8+[2]<<16+[3]<<32)

	If file is compressed, a GZ stream starts here (0x78 indicating DEFLATE)  	

	RECT	  : variable size, RECT struct for the movie dimensions (see below) PACKED 
  FRAMES  : 2 bytes in LSB-MSB format ([0]+[1]<<8)
	FPS			: 8 bytes in 8.8 ([1].[0]) format
		
	RECT struct information:
	
	N_BITS	: 5 bits, shows the number of bits for each of the following sections
	MIN_X		: N_BITS bits, min. X coord MUST BE 0
	MAX_X		: N_BITS bits, min. X coord (width is MAX_X - MIN_X : GENERIC)
	MIN_Y		: N_BITS bits, min. Y coord MUST BE 0
	MAX_Y		: N_BITS bits, min. Y coord (width is MAX_Y - MIN_Y : GENERIC)
	
	This struct is PACKED (bit '0' padded to the end of last used byte)
	 
4.- License

	SWFHEADER CLASS - PHP SWF header parser
	Copyright (C) 2004  Carlos Falo Hervás

	This library is free software; you can redistribute it and/or
	modify it under the terms of the GNU Lesser General Public
	License as published by the Free Software Foundation; either
	version 2.1 of the License, or (at your option) any later version.

	This library is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public
	License along with this library; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

5.- Future releases and WIP

  I don't think i'll work much on this class, but any new release will be posted 
  at http://phpclasses.upperdesign.com

  Any suggestions are appreciated

6.- Contact information

  Carlos Falo Hervás
  carles@bisinteractive.net
  http://www.bisinteractive.net

  C/Manila 54-56 Esc. A Ent. 4ª		
  08034 Barcelona Spain

  Phone: +34 9 3 2063652
  Fax:	 +34 9 3 2063689
