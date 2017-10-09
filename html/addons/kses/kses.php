<?php

# kses 0.2.2 - HTML/XHTML filter that only allows some elements and attributes
# Copyright (C) 2002, 2003, 2005  Ulf Harnhammar
#
# This program is free software and open source software; you can redistribute
# it and/or modify it under the terms of the GNU General Public License as
# published by the Free Software Foundation; either version 2 of the License,
# or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
# FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
# more details.
#
# http://www.gnu.org/licenses/gpl.html
#
# *** CONTACT INFORMATION ***
#
# E-mail:      metaur at users dot sourceforge dot net
# Web page:    http://sourceforge.net/projects/kses
# Paper mail:  Ulf Harnhammar
#              Ymergatan 17 C
#              753 25  Uppsala
#              SWEDEN
#
# [kses strips evil scripts!]

// i prefer to define myself the array of the allowable tag here 

if(!defined('IN_FORMA')) die('You cannot access this file directly!');



$GLOBALS['allowed_html'] = array(
	'a' => array(
		'href' => array(), 'title' => array(),
		'rel' => array(), 'rev' => array(),
		'name' => array()
	),
	'abbr' => array(
		'title' => array(), 'class' => array()
	),
	'acronym' => array(
		'title' => array()
	),
	'address' => array(),
	'b' => array(),
	'big' => array(),
	'blockquote' => array(
		'cite' => array(), 'xml:lang' => array(),
		'lang' => array()
	),
	'br' => array(),
	'button' => array(
		'disabled' => array(), 'name' => array(),
		'type' => array(), 'value' => array()
	),
	'caption' => array(
		'align' => array()
	),
	//'center' => array(),	//deprecated
	'code' => array(),
	'col' => array(
		'align' => array(), 'char' => array(),
		'charoff' => array(), 'span' => array(),
		'valign' => array(), 'width' => array()
	),
	'colgroup' => array(
		'align' => array(), 'char' => array(),
		'charoff' => array(), 'span' => array(),
		'valign' => array(), 'width' => array()
	),
	'dd' => array(),
	'del' => array(
		'datetime' => array()
	),
	'div' => array(
		'align' => array(), 'xml:lang' => array(),
		'lang' => array()
	),
	'dfn' => array(),
	'dl' => array(),
	'dt' => array(),
	'em' => array(),
	'embed' => array(	// player and video 
		'src' => array(),
		'width' => array(),
		'height' => array(),
		'loop' => array(),
		'quality' => array(),
		'pluginspage' => array(),
		'type' => array(),
		'menu' => array(),
	),
	'fieldset' => array(),
	'font' => array(
		'color' => array(), 'face' => array(),
		'size' => array()
	),
	'form' => array(
		'action' => array(), 'accept' => array(),
		'accept-charset' => array(), 'enctype' => array(),
		'method' => array(), 'name' => array(),
		'target' => array()
	),
	'h1' => array(
		'align' => array()
	),
	'h2' => array(
		'align' => array()
	),
	'h3' => array(
		'align' => array()
	),
	'h4' => array(
		'align' => array()
	),
	'h5' => array(
		'align' => array()
	),
	'h6' => array(
		'align' => array()
	),
	'hr' => array(
		'align' => array(), 'noshade' => array(),
		'size' => array(), 'width' => array()
	),
	'i' => array(),
	'img' => array(
		'alt' => array(), 'align' => array(),
		'border' => array(), 'height' => array(),
		'hspace' => array(), 'longdesc' => array(),
		'vspace' => array(), 'src' => array(),
		'width' => array()
	),
	'ins' => array(
		'datetime' => array(), 'cite' => array()
	),
	'kbd' => array(),
	'label' => array(
		'for' => array()
	),
	'legend' => array(
		'align' => array()
	),
	'li' => array(),
	'ol' => array(),
	'object' => array(
		'classid' => array(),
		'id' => array(),
		'height' => array(),
		'width' => array(),
		'codebase' => array(),
		'type' => array()
	),
	'p' => array(
		'align' => array(), 'xml:lang' => array(),
		'lang' => array()
	), 
	'param' => array(
		'name' => array(),
		'value' => array()
	),
	'pre' => array(
		'width' => array()
	),
	'q' => array(
		'cite' => array()
	),
	's' => array(),
	'span' => array(
		'style' => array()
	),
	'strike' => array(),
	'strong' => array(),
	'sub' => array(),
	'sup' => array(),
	'table' => array(
		'align' => array(), 'bgcolor' => array(),
		'border' => array(), 'cellpadding' => array(),
		'cellspacing' => array(), 'rules' => array(),
		'summary' => array(), 'width' => array()
	),
	'tbody' => array(
		'align' => array(), 'char' => array(),
		'charoff' => array(), 'valign' => array()
	),
	'td' => array(
		'abbr' => array(), 'align' => array(),
		'axis' => array(), 'bgcolor' => array(),
		'char' => array(), 'charoff' => array(),
		'colspan' => array(), 'headers' => array(),
		'height' => array(), 'nowrap' => array(),
		'rowspan' => array(), 'scope' => array(),
		'valign' => array(), 'width' => array()
	),
	'textarea' => array(
		'cols' => array(), 'rows' => array(),
		'disabled' => array(), 'name' => array(),
		'readonly' => array()
	),
	'tfoot' => array(
		'align' => array(), 'char' => array(),
		'charoff' => array(), 'valign' => array()
	),
	'th' => array(
		'abbr' => array(), 'align' => array(),
		'axis' => array(), 'bgcolor' => array(),
		'char' => array(), 'charoff' => array(),
		'colspan' => array(), 'headers' => array(),
		'height' => array(), 'nowrap' => array(),
		'rowspan' => array(), 'scope' => array(),
		'valign' => array(), 'width' => array()
	),
	'thead' => array(
		'align' => array(), 'char' => array(),
		'charoff' => array(), 'valign' => array()
	),
	'tr' => array(
		'align' => array(), 'bgcolor' => array(),
		'char' => array(), 'charoff' => array(),
		'valign' => array()
	),
	'tt' => array(),
	'u' => array(),
	'ul' => array(),
	'var' => array()
);

// original kses code -------------------------------------------------------

function kses($string, $allowed_html = false, $allowed_protocols = array('http', 'https', 'ftp', 'mailto', 'color', 'background-color'))	{
//'news', 'nntp', 'telnet', 'gopher',  
  $string = kses_no_null($string);
  $string = kses_js_entities($string);
  $string = kses_normalize_entities($string);
  $string = kses_hook($string);
  $allowed_html_fixed = kses_array_lc( ( $allowed_html !== false ? $allowed_html : $GLOBALS['allowed_html'] ) );
  return kses_split($string, $allowed_html_fixed, $allowed_protocols);
} # function kses

function kses_hook($string) {
  return $string;
} # function kses_hook

function kses_version() {
  return '0.2.2';
} # function kses_version

function kses_split($string, $allowed_html, $allowed_protocols)
{
    $callback = function ($matches) use ($allowed_html, $allowed_protocols)
    { return kses_split2($matches[1], $allowed_html, $allowed_protocols); };
    return preg_replace_callback('%(<'.   # EITHER: <
        '[^>]*'. # things that aren't >
        '(>|$)'. # > or end of string
        '|>)%', # OR: just a >
        $callback,
        $string);
} # function kses_split

function kses_split2($string, $allowed_html, $allowed_protocols) {
  $string = kses_stripslashes($string);

  if (substr($string, 0, 1) != '<')
    return '&gt;';
    # It matched a ">" character

  if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?$%', $string, $matches))
    return '';
    # It's seriously malformed

  $slash = trim($matches[1]);
  $elem = $matches[2];
  $attrlist = $matches[3];

  if (!@isset($allowed_html[strtolower($elem)]))
    return '';
    # They are using a not allowed HTML element

  if ($slash != '')
    return "<$slash$elem>";
  # No attributes are allowed for closing elements

  return kses_attr("$slash$elem", $attrlist, $allowed_html,
                   $allowed_protocols);
} # function kses_split2

function kses_attr($element, $attr, $allowed_html, $allowed_protocols) {
	# Is there a closing XHTML slash at the end of the attributes?

  $xhtml_slash = '';
  if (preg_match('%\s/\s*$%', $attr))
    $xhtml_slash = ' /';

	# Are any attributes allowed at all for this element?

  if (@count($allowed_html[strtolower($element)]) == 0)
    return "<$element$xhtml_slash>";

	# Split it

  $attrarr = kses_hair($attr, $allowed_protocols);

	# Go through $attrarr, and save the allowed attributes for this element
	# in $attr2

  $attr2 = '';

  foreach ($attrarr as $arreach)
  {
    if (!@isset($allowed_html[strtolower($element)]
                            [strtolower($arreach['name'])]))
      continue; # the attribute is not allowed

    $current = $allowed_html[strtolower($element)]
                            [strtolower($arreach['name'])];

    if (!is_array($current))
      $attr2 .= ' '.$arreach['whole'];
    # there are no checks

    else
    {
    # there are some checks
      $ok = true;
      foreach ($current as $currkey => $currval)
        if (!kses_check_attr_val($arreach['value'], $arreach['vless'],
                                 $currkey, $currval))
        { $ok = false; break; }

      if ($ok)
        $attr2 .= ' '.$arreach['whole']; # it passed them
    } # if !is_array($current)
  } # foreach

# Remove any "<" or ">" characters

  $attr2 = preg_replace('/[<>]/', '', $attr2);

  return "<$element$attr2$xhtml_slash>";
} # function kses_attr

function kses_hair($attr, $allowed_protocols) {
  $attrarr = array();
  $mode = 0;
  $attrname = '';

# Loop through the whole attribute list

  while (strlen($attr) != 0)
  {
    $working = 0; # Was the last operation successful?

    switch ($mode)
    {
      case 0: # attribute name, href for instance

        if (preg_match('/^([-a-zA-Z]+)/', $attr, $match))
        {
          $attrname = $match[1];
          $working = $mode = 1;
          $attr = preg_replace('/^[-a-zA-Z]+/', '', $attr);
        }

        break;

      case 1: # equals sign or valueless ("selected")

        if (preg_match('/^\s*=\s*/', $attr)) # equals sign
        {
          $working = 1; $mode = 2;
          $attr = preg_replace('/^\s*=\s*/', '', $attr);
          break;
        }

        if (preg_match('/^\s+/', $attr)) # valueless
        {
          $working = 1; $mode = 0;
          $attrarr[] = array
                        ('name'  => $attrname,
                         'value' => '',
                         'whole' => $attrname,
                         'vless' => 'y');
          $attr = preg_replace('/^\s+/', '', $attr);
        }

        break;

      case 2: # attribute value, a URL after href= for instance

        if (preg_match('/^"([^"]*)"(\s+|$)/', $attr, $match))
         # "value"
        {
          $thisval = kses_bad_protocol($match[1], $allowed_protocols);

          $attrarr[] = array
                        ('name'  => $attrname,
                         'value' => $thisval,
                         'whole' => "$attrname=\"$thisval\"",
                         'vless' => 'n');
          $working = 1; $mode = 0;
          $attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
          break;
        }

        if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match))
         # 'value'
        {
          $thisval = kses_bad_protocol($match[1], $allowed_protocols);

          $attrarr[] = array
                        ('name'  => $attrname,
                         'value' => $thisval,
                         'whole' => "$attrname='$thisval'",
                         'vless' => 'n');
          $working = 1; $mode = 0;
          $attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
          break;
        }

        if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match))
         # value
        {
          $thisval = kses_bad_protocol($match[1], $allowed_protocols);

          $attrarr[] = array
                        ('name'  => $attrname,
                         'value' => $thisval,
                         'whole' => "$attrname=\"$thisval\"",
                         'vless' => 'n');
                         # We add quotes to conform to W3C's HTML spec.
          $working = 1; $mode = 0;
          $attr = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
        }

        break;
    } # switch

    if ($working == 0) # not well formed, remove and try again
    {
      $attr = kses_html_error($attr);
      $mode = 0;
    }
  } # while

  if ($mode == 1)
  # special case, for when the attribute list ends with a valueless
  # attribute like "selected"
    $attrarr[] = array
                  ('name'  => $attrname,
                   'value' => '',
                   'whole' => $attrname,
                   'vless' => 'y');

  return $attrarr;
} # function kses_hair

function kses_check_attr_val($value, $vless, $checkname, $checkvalue) {
  $ok = true;

  switch (strtolower($checkname))
  {
    case 'maxlen':
    # The maxlen check makes sure that the attribute value has a length not
    # greater than the given value. This can be used to avoid Buffer Overflows
    # in WWW clients and various Internet servers.

      if (strlen($value) > $checkvalue)
        $ok = false;
      break;

    case 'minlen':
    # The minlen check makes sure that the attribute value has a length not
    # smaller than the given value.

      if (strlen($value) < $checkvalue)
        $ok = false;
      break;

    case 'maxval':
    # The maxval check does two things: it checks that the attribute value is
    # an integer from 0 and up, without an excessive amount of zeroes or
    # whitespace (to avoid Buffer Overflows). It also checks that the attribute
    # value is not greater than the given value.
    # This check can be used to avoid Denial of Service attacks.

      if (!preg_match('/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value))
        $ok = false;
      if ($value > $checkvalue)
        $ok = false;
      break;

    case 'minval':
    # The minval check checks that the attribute value is a positive integer,
    # and that it is not smaller than the given value.

      if (!preg_match('/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value))
        $ok = false;
      if ($value < $checkvalue)
        $ok = false;
      break;

    case 'valueless':
    # The valueless check checks if the attribute has a value
    # (like <a href="blah">) or not (<option selected>). If the given value
    # is a "y" or a "Y", the attribute must not have a value.
    # If the given value is an "n" or an "N", the attribute must have one.

      if (strtolower($checkvalue) != $vless)
        $ok = false;
      break;
  } # switch

  return $ok;
} # function kses_check_attr_val

function kses_bad_protocol($string, $allowed_protocols) {
  $string = kses_no_null($string);
  $string = preg_replace('/\xad+/', '', $string); # deals with Opera "feature"
  $string2 = $string.'a';

  while ($string != $string2)
  {
    $string2 = $string;
    $string = kses_bad_protocol_once($string, $allowed_protocols);
  } # while

  return $string;
} # function kses_bad_protocol

function kses_no_null($string) {
  $string = preg_replace('/\0+/', '', $string);
  $string = preg_replace('/(\\\\0)+/', '', $string);

  return $string;
} # function kses_no_null

function kses_stripslashes($string) {
  return preg_replace('%\\\\"%', '"', $string);
} # function kses_stripslashes

function kses_array_lc($inarray) {
  $outarray = array();

  foreach ($inarray as $inkey => $inval)
  {
    $outkey = strtolower($inkey);
    $outarray[$outkey] = array();

    foreach ($inval as $inkey2 => $inval2)
    {
      $outkey2 = strtolower($inkey2);
      $outarray[$outkey][$outkey2] = $inval2;
    } # foreach $inval
  } # foreach $inarray

  return $outarray;
} # function kses_array_lc

function kses_js_entities($string) {
  return preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);
} # function kses_js_entities

function kses_html_error($string) {
  return preg_replace('/^("[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*/', '', $string);
} # function kses_html_error

function kses_bad_protocol_once($string, $allowed_protocols) {
  return preg_replace('/^((&[^;]*;|[\sA-Za-z0-9])*)'.
                      '(:|&#58;|&#[Xx]3[Aa];)\s*/e',
                      'kses_bad_protocol_once2("\\1", $allowed_protocols)',
                      $string);
} # function kses_bad_protocol_once

function kses_bad_protocol_once2($string, $allowed_protocols) {
  $string2 = kses_decode_entities($string);
  $string2 = preg_replace('/\s/', '', $string2);
  $string2 = kses_no_null($string2);
  $string2 = preg_replace('/\xad+/', '', $string2);
   # deals with Opera "feature"
  $string2 = strtolower($string2);

  $allowed = false;
  foreach ($allowed_protocols as $one_protocol)
    if (strtolower($one_protocol) == $string2)
    {
      $allowed = true;
      break;
    }

  if ($allowed)
    return "$string2:";
  else
    return '';
} # function kses_bad_protocol_once2

function kses_normalize_entities($string) {
# Disarm all entities by converting & to &amp;

  $string = Util::str_replace_once('&', '&amp;', $string);

# Change back the allowed entities in our entity whitelist

  $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]{0,19});/',
                         '&\\1;', $string);
  $string = preg_replace_callback('/&amp;#0*([0-9]{1,5});/', function ($m){ return kses_normalize_entities2($m[1]);}, $string);
  $string = preg_replace('/&amp;#([Xx])0*(([0-9A-Fa-f]{2}){1,2});/',
                         '&#\\1\\2;', $string);

  return $string;
} # function kses_normalize_entities

function kses_normalize_entities2($i) {
  return (($i > 65535) ? "&amp;#$i;" : "&#$i;");
} # function kses_normalize_entities2

function kses_decode_entities($string) {
  $string = preg_replace('/&#([0-9]+);/e', 'chr("\\1")', $string);
  $string = preg_replace('/&#[Xx]([0-9A-Fa-f]+);/e', 'chr(hexdec("\\1"))',
                         $string);

  return $string;
} # function kses_decode_entities

?>