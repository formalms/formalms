<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class PDF extends TCPDF
{
    public $angle = 0;
    /** @var boolean */
    public $encrypted;          //whether document is protected
    /** @var string */
    public $password;           //encryption password
    public $Uvalue;             //U entry in pdf document
    public $Ovalue;             //O entry in pdf document
    public $Pvalue;             //P entry in pdf document
    public $enc_obj_id;         //encryption object id
    public $last_rc4_key;       //last RC4 key encrypted (cached for optimisation)
    public $last_rc4_key_c;     //last RC4 computed key
    public $page_delimiter;

    public function PDF($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8')
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding);

        // set document information
        $this->SetCreator(PDF_CREATOR);

        // remove default header/footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);

        //set margins
        $this->SetMargins(0, 0, 0);

        //set auto page breaks
        $this->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $this->SetFont('dejavusans', '', 10);

        // print a line using Cell()

        //		$this->encrypted=false;
        //		$this->last_rc4_key='';
                /*$this->padding="\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08".
                "\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";*/
        //$this->SetFont('freeserif','',12);
    }

    public function PlaceWater()
    {
        //Put watermark  Author: Ivan
        $this->SetFont('times', '', 50);
        $this->SetTextColor(230, 230, 230);
        $this->RotatedText(0, 0, 'F a c - s i m i l e', 90);
    }

    public function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    public function Rotate($angle, $x = -1, $y = -1)
    {
        //Author: Olivier
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }
        if ($this->angle != 0) {
            $this->_out('Q');
        }
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    public function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    public function getPdf($html, $name, $img = false, $download = true, $facs_simile = false, $for_saving = false)
    {
        @ob_end_clean();

        $doc = new DOMDocument(null, $this->encoding);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', $this->encoding));
        $xpath = new DOMXPath($doc);
        $nodelist = $xpath->query('//img'); // "/images/image.jpg"

        foreach ($nodelist as $node) {
            $value = $node->attributes->getNamedItem('src')->nodeValue;
            $value = str_replace(' ', '%20', $value);
            $node->attributes->getNamedItem('src')->nodeValue = $value;
        }

        $html = $doc->saveHTML();

        $query = 'SELECT lang_browsercode, lang_direction'
            . ' FROM %adm_lang_language'
            . " WHERE lang_code = '" . getLanguage() . "'";
        list($lang_code, $lang_direction) = sql_fetch_row(sql_query($query));

        if (strpos($lang_code, ';') !== false) {
            $lang_code = current(explode(';', $lang_code));
        }
        $lg = [];
        $lg['a_meta_charset'] = $this->encoding;
        $lg['a_meta_dir'] = $lang_direction;
        $lg['a_meta_language'] = $lang_code;
        $lg['w_page'] = 'page';
        $this->setLanguageArray($lg);
      
        /*
         * Protection for the PDF
         *
         * print : Print the document;
         * modify : Modify the contents of the document by operations other than those controlled by 'fill-forms', 'extract' and 'assemble';
         * copy : Copy or otherwise extract text and graphics from the document;
         * annot-forms : Add or modify text annotations, fill in interactive form fields, and, if 'modify' is also set, create or modify interactive form fields (including signature fields);
         * fill-forms : Fill in existing interactive form fields (including signature fields), even if 'annot-forms' is not specified;
         * extract : Extract text and graphics (in support of accessibility to users with disabilities or for other purposes);
         * assemble : Assemble the document (insert, rotate, or delete pages and create bookmarks or thumbnail images), even if 'modify' is not set;
         * print-high : Print the document to a representation from which a faithful digital copy of the PDF content could be generated. When this is not set, printing is limited to a low-level representation of the appearance, possibly of degraded quality.
         * owner : (inverted logic - only for public-key) when set permits change of encryption and enables all other permissions.
         */
        if ($this->isEncrypted()) {
            $this->SetProtection(['modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'], '', $this->getPassword());
        }

        $this->getAliasNbPages();

        $this->AddPage();
        // set JPEG quality
        $this->setJPEGQuality(80);

        if ($facs_simile) {
            //Put watermark  Author: Ivan
            $this->SetFont('dejavusans', '', 40);
            $this->SetTextColor(240, 240, 240);
            $this->RotatedText(15, 50, 'F a c - s i m i l e', 270);
            $this->SetFont('dejavusans', '', 10);
            $this->SetTextColor(0, 0, 0);
        }
        $this->setXY(0, 0);

        // managing page break
        $this->page_delimiter = '<!-- pagebreak -->';
        $pages = explode($this->page_delimiter, $html);
        $cnt = count($pages);
        $index = 0;
        foreach ($pages as $page) {
            if ($img != '') {
                $this->setXY(0, 0);
                $this->Image($GLOBALS['where_files_relative'] . '/appLms/certificate/' . $img, 0, 0, ($this->CurOrientation == 'P' ? 210 : 298), 0, '', '', '', true);
                $this->setXY(0, 0);
            }

            $this->writeHTML($this->page_delimiter . $page, true, 0, true, 0);

            if ($index < $cnt - 1) {
                $this->AddPage();
            }
            ++$index;
        }
        $this->lastPage();

        $name = str_replace(
            ['\\', '/', ':', '\'', '\*', '?', '"', '<', '>', '|'],
            ['', '', '', '', '', '', '', '', '', ''],
            $name
        );

        // maybe there is a way to understand why ie6 doesn't use correctly the 'D' option, but this work so....

        if ($for_saving) {
            return $this->Output(('"' . $name . '.pdf"'), 'S');
        }

        $pdf = $this->Output(('"' . $name . '.pdf"'), 'S');

        session_write_close();
        //ini_set("output_buffering", 0);
        //Download file
        //send file length info
        header('Content-Length:' . strlen($pdf));
        //content type forcing dowlad
        header("Content-type: application/download\n charset: utf-8\n");
        //cache control
        header('Cache-control: private');
        //sending creation time
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        //content type
        header('Content-Disposition: attachment; filename="' . $name . '.pdf"');

        echo $pdf;
        exit();
    }

    /**
     * @return bool
     */
    public function isEncrypted()
    {
        return $this->encrypted;
    }

    /**
     * @param bool $encrypted
     */
    public function setEncrypted($encrypted)
    {
        $this->encrypted = $encrypted;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
