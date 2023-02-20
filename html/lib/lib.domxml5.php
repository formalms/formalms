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
 * This package define classes for XML DOM compatibility with PHP < 5
 * In Docebo we use DOM API from PHP5 but you can use the platform also
 *	in PHP4. So in this package we redefine all the DOM API used in Docebo.
 * For do that we use domxml.
 *
 * @version 	$Id: lib.domxml5.php 113 2006-03-08 18:08:42Z ema $
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 */
class DoceboDOMNode extends DOMNode
{
}
class DoceboDOMDocument extends DOMDocument
{
}
class DoceboDOMDocumentType extends DOMDocumentType
{
}
class DoceboDOMAttr extends DOMAttr
{
}
class DoceboDOMElement extends DOMElement
{
}
class DoceboDOMNodeList extends DOMNodeList
{
}
class DoceboDOMNamedNodeMap extends DOMNamedNodeMap
{
}
class DoceboDOMXPath extends DOMXPath
{
}
