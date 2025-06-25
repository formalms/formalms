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
 * PDF class extends TCPDF to provide PDF generation functionality for FormaLMS
 * 
 * This class handles PDF creation, watermarks, text rotation, and PDF encryption
 */
class PDF extends TCPDF
{
    /** @var float Current rotation angle */
    public $angle = 0;

    /** @var bool Whether document is protected */
    public $encrypted;

    /** @var string Encryption password */
    public $password;

    /** @var string U entry in pdf document */
    public $Uvalue;

    /** @var string O entry in pdf document */
    public $Ovalue;

    /** @var int P entry in pdf document */
    public $Pvalue;

    /** @var int Encryption object id */
    public $enc_obj_id;

    /** @var string Last RC4 key encrypted (cached for optimization) */
    public $last_rc4_key;

    /** @var string Last RC4 computed key */
    public $last_rc4_key_c;

    /** @var string Page delimiter for HTML content */
    public $page_delimiter;

    /**
     * Constructor for PDF class
     *
     * @param string $orientation Page orientation (P=portrait, L=landscape)
     * @param string $unit Unit of measure (mm, cm, in, pt)
     * @param string $format Page format (A4, Letter, etc)
     * @param bool $unicode True if unicode support is needed
     * @param string $encoding Character encoding
     */
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8')
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding);

        // Set document information
        $this->SetCreator(PDF_CREATOR);

        // Remove default header/footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);

        // Set margins
        $this->SetMargins(0, 0, 0);

        // Set auto page breaks
        $this->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);

        // Set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set default font
        $this->SetFont('dejavusans', '', 10);

        // Initialize encryption as false
        $this->encrypted = false;
    }

    /**
     * Places a watermark on the current page
     * 
     * Adds a "Fac-simile" watermark in light gray
     */
    public function PlaceWater()
    {
        $this->SetFont('times', '', 50);
        $this->SetTextColor(230, 230, 230);
        $this->RotatedText(0, 0, 'F a c - s i m i l e', 90);
    }

    /**
     * Adds rotated text to the PDF
     * 
     * @param float $x X position
     * @param float $y Y position
     * @param string $txt Text to be displayed
     * @param float $angle Rotation angle in degrees
     */
    public function RotatedText($x, $y, $txt, $angle)
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    /**
     * Rotates the coordinate system
     * 
     * @param float $angle Rotation angle in degrees
     * @param float $x X position of rotation center, -1 for current position
     * @param float $y Y position of rotation center, -1 for current position
     */
    public function Rotate($angle, $x = -1, $y = -1)
    {
        // Use current position if not specified
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }

        // End any previous rotation
        if ($this->angle != 0) {
            $this->_out('Q');
        }

        $this->angle = $angle;

        // Apply rotation if angle is not zero
        if ($angle != 0) {
            $angle *= M_PI / 180; // Convert to radians
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', 
                $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    /**
     * Overrides parent _endpage method to handle rotation
     */
    public function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    /**
     * Generates a PDF from HTML content
     *
     * @param string $html HTML content to convert to PDF
     * @param string $name Name of the PDF file (without extension)
     * @param string|bool $img Optional background image path
     * @param bool $download Whether to force download
     * @param bool $facs_simile Whether to add a "Fac-simile" watermark
     * @param bool $for_saving Whether to return PDF content instead of downloading
     * @return string|void PDF content if $for_saving is true, otherwise outputs PDF and exits
     * @throws Exception If image format is not supported
     */
    public function getPdf($html, $name, $img = false, $download = true, $facs_simile = false, $for_saving = false)
    {
        // Clean output buffer to prevent issues with PDF download
        @ob_end_clean();

        // If HTML is empty, return early
        if (empty($html)) {
            return;
        }

        try {
            // Process HTML content
            $html = $this->processHtmlContent($html);

            // Set up PDF language and protection
            $this->setupPdfProperties();

            // Initialize PDF document
            $this->getAliasNbPages();
            $this->AddPage();
            $this->setJPEGQuality(80);

            // Add watermark if requested
            if ($facs_simile) {
                $this->addFacsimileWatermark();
            }

            // Set initial position
            $this->setXY(0, 0);

            // Process pages and background image
            $this->processPages($html, $img);

            // Sanitize filename
            $name = $this->sanitizeFilename($name);

            // Return or output PDF
            if ($for_saving) {
                return $this->Output(('"' . $name . '.pdf"'), 'S');
            }

            $pdf = $this->Output(('"' . $name . '.pdf"'), 'S');

            // Set headers for download
            $this->setDownloadHeaders($name, $pdf);

            // Output PDF content
            echo $pdf;
            exit();
        } catch (Exception $e) {
            // Record error for system administrators
            $errorMessage = 'PDF generation error: ' . $e->getMessage();
            error_log($errorMessage);

            // Return empty string if PDF was for saving
            if ($for_saving) {
                return '';
            }

            // Otherwise show a user-friendly error message
            die('Unable to generate PDF. Please contact system administrator.');
        }
    }

    /**
     * Process HTML content for PDF generation
     * 
     * @param string $html Original HTML content
     * @return string Processed HTML content
     */
    private function processHtmlContent($html)
    {
        $doc = new DOMDocument('1.0', $this->encoding);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', $this->encoding));

        // Process image paths
        $xpath = new DOMXPath($doc);
        $nodelist = $xpath->query('//img');

        foreach ($nodelist as $node) {
            $value = $node->attributes->getNamedItem('src')->nodeValue;
            $value = str_replace(' ', '%20', $value);

            $pathExplode = explode('/files/', $value);
            if ($pathExplode) {
                $value = str_replace($pathExplode[0], '', $value);
            }
            $node->attributes->getNamedItem('src')->nodeValue = $value;
        }

        return $doc->saveHTML();
    }

    /**
     * Set up PDF language and protection properties
     */
    private function setupPdfProperties()
    {
        // Get language settings
        $query = 'SELECT lang_browsercode, lang_direction'
            . ' FROM %adm_lang_language'
            . " WHERE lang_code = '" . Lang::get() . "'";
        [$lang_code, $lang_direction] = sql_fetch_row(sql_query($query));

        if (str_contains($lang_code, ';')) {
            $lang_code = current(explode(';', $lang_code));
        }

        // Set language array
        $lg = [
            'a_meta_charset' => $this->encoding,
            'a_meta_dir' => $lang_direction,
            'a_meta_language' => $lang_code,
            'w_page' => 'page'
        ];
        $this->setLanguageArray($lg);

        // Apply PDF protection if encryption is enabled
        if ($this->isEncrypted()) {
            $this->SetProtection(
                ['modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'], 
                '', 
                $this->getPassword()
            );
        }
    }

    /**
     * Add a facsimile watermark to the PDF
     */
    private function addFacsimileWatermark()
    {
        $this->SetFont('dejavusans', '', 40);
        $this->SetTextColor(240, 240, 240);
        $this->RotatedText(15, 50, 'F a c - s i m i l e', 270);
        $this->SetFont('dejavusans', '', 10);
        $this->SetTextColor(0, 0, 0);
    }

    /**
     * Process HTML pages and add background image if specified
     * 
     * @param string $html HTML content
     * @param string|bool $img Background image path
     * @throws Exception If image format is not supported
     */
    private function processPages($html, $img)
    {
        // Set page delimiter and split content into pages
        $this->page_delimiter = '<!-- pagebreak -->';
        $pages = explode($this->page_delimiter, $html);
        $cnt = count($pages);
        $index = 0;

        // Process background image if provided
        $htmlFolderPath = dirname(__FILE__, 3);
        $imagePath = '';
        $imageType = '';

        if ($img) {
            $imagePath = $htmlFolderPath . '/' . _folder_files_ . '/appLms/certificate/' . $img;

            if (file_exists($imagePath)) {
                $imageType = $this->getImageType($imagePath);
            }
        }

        // Process each page
        foreach ($pages as $page) {
            // Add background image if available
            if ($img && file_exists($imagePath)) {
                $this->setXY(0, 0);
                $this->Image(
                    $imagePath, 
                    0, 
                    0, 
                    ($this->CurOrientation == 'P' ? 210 : 298), 
                    0, 
                    $imageType, 
                    '', 
                    '', 
                    true
                );
                $this->setXY(0, 0);
            }

            // Write HTML content
            $this->writeHTML($this->page_delimiter . $page, true, 0, true, 0);

            // Add a new page if not the last page
            if ($index < $cnt - 1) {
                $this->AddPage();
            }
            ++$index;
        }

        $this->lastPage();
    }

    /**
     * Determine image type from file path
     * 
     * @param string $imagePath Path to the image file
     * @return string TCPDF image type
     * @throws Exception If image format is not supported
     */
    private function getImageType($imagePath)
    {
        $mime_type = mime_content_type($imagePath);

        switch ($mime_type) {
            case 'image/jpeg':
                return 'JPG';
            case 'image/png':
                return 'PNG';
            case 'image/gif':
                return 'GIF';
            case 'image/svg+xml':
                return 'SVG';
            case 'image/bmp':
                return 'BMP';
            default:
                throw new Exception("Unsupported image format: " . $mime_type);
        }
    }

    /**
     * Sanitize filename to remove invalid characters
     * 
     * @param string $name Original filename
     * @return string Sanitized filename
     */
    private function sanitizeFilename($name)
    {
        return str_replace(
            ['\\', '/', ':', '\'', '\*', '?', '"', '<', '>', '|'],
            ['', '', '', '', '', '', '', '', '', ''],
            $name
        );
    }

    /**
     * Set HTTP headers for PDF download
     * 
     * @param string $name Filename (without extension)
     * @param string $pdf PDF content
     */
    private function setDownloadHeaders($name, $pdf)
    {
        header('Content-Length:' . strlen($pdf));
        header("Content-type: application/pdf");
        header('Cache-control: private');
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="' . $name . '.pdf"');
    }

    /**
     * Check if PDF encryption is enabled
     *
     * @return bool True if encryption is enabled, false otherwise
     */
    public function isEncrypted()
    {
        return $this->encrypted;
    }

    /**
     * Enable or disable PDF encryption
     *
     * @param bool $encrypted True to enable encryption, false to disable
     * @return void
     */
    public function setEncrypted($encrypted)
    {
        $this->encrypted = (bool)$encrypted;
    }

    /**
     * Get the current encryption password
     *
     * @return string The current password or empty string if not set
     */
    public function getPassword()
    {
        return $this->password ?? '';
    }

    /**
     * Set the encryption password
     *
     * @param string $password The password to use for encryption
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
