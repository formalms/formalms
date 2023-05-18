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

require_once dirname(__FILE__) . '/lib.connector.php';

/**
 * class for define csv connection to data source.
 *
 * @version    1.1
 *
 * @author        Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnectorCsv extends FormaConnector
{
    public $curr_file = '';
    public $filename = '';
    public $filehandle = null;
    public $first_row_header = '1';    // '1' = yes, '0' = no
    public $separator = ',';
    public $enclosure = '"';
    public $cols_descriptor = null;
    public $row_index = 0;
    public $readwrite = 0; // read = 1, write = 2, readwrite = 3
    public $last_error = '';
    public $name = '';
    public $description = '';
    public $subpattern = '';

    /**
     * This constructor require the source file name.
     *
     * @param array $params the array of params
     *                      - 'filename' => name of the file (required)
     *                      - 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
     *                      - 'separator' => string a char with the fields separator (Optional, default = ,)
     **/
    public function __construct($params)
    {
        $this->set_config($params);
    }

    public function get_config()
    {
        return ['filepattern' => $this->filename,
            'first_row_header' => $this->first_row_header,
            'field_delimiter' => $this->separator,
            'field_enclosure' => $this->enclosure,
            'field_def' => $this->cols_descriptor,
            'readwrite' => $this->readwrite,
            'name' => $this->name,
            'description' => $this->description,
            'subpattern' => $this->subpattern, ];
    }

    public function set_config($params)
    {
        if (isset($params['filepattern'])) {
            $this->filename = $params['filepattern'];
        }
        if (isset($params['readwrite'])) {
            $this->readwrite = (int) $params['readwrite'];
        }
        if (isset($params['first_row_header'])) {
            $this->first_row_header = (int) $params['first_row_header'];
        }
        if (isset($params['field_delimiter'])) {
            $this->separator = substr($params['field_delimiter'], 0, 5);
        }
        if (isset($params['field_enclosure'])) {
            $this->enclosure = substr($params['field_enclosure'], 0, 5);
        }
        if (isset($params['field_def'])) {
            $this->cols_descriptor = $params['field_def'];
        }
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
    }

    public function get_configUI()
    {
        return new FormaConnectorCsvUI($this);
    }

    /**
     * execute the connection to source.
     **/
    public function connect()
    {
        $this->close();

        /* search for file with pattern */
        $pat = str_replace(['*', '?'], ['.*', '.{1}'], $this->filename);
        $arr_files = preg_ls(DOCEBOIMPORT_BASEDIR . $this->subpattern, false, '/' . $pat . '/');
        if (count($arr_files) == 0 && !$this->is_writeonly()) {
            //$this->last_error = 'file not found: '.DOCEBOIMPORT_BASEDIR.$this->filename;
            return DOCEBO_IMPORT_NOTHINGTOPROCESS;
        } elseif (!$this->is_writeonly()) {
            $this->curr_file = $arr_files[0];
        } else {
            $this->curr_file = DOCEBOIMPORT_BASEDIR . $this->subpattern . $pat;
        }
        /* open file */
        if ($this->is_readonly()) {
            $this->filehandle = @fopen($this->curr_file, 'r');
        } elseif ($this->is_writeonly()) {
            $this->filehandle = @fopen($this->curr_file, 'w');
        } else {
            $this->filehandle = @fopen($this->curr_file, 'rw');
        }

        if ($this->filehandle === false) {
            $this->last_error = 'file not opened: ' . $this->curr_file;

            return false;
        }
        if ($this->is_writeonly()) {
            return true;
        }

        /* get header if required */
        if ($this->first_row_header == '1') {
            $row = fgetcsv($this->filehandle, 10000, $this->separator);
            if (!is_array($row)) {
                $this->last_error = 'no rows found on ' . $this->curr_file;

                return false;
            }
            if (count($row) == 0) {
                $this->last_error = 'no rows found on ' . $this->curr_file;

                return false;
            }
        }

        return true;
    }

    /**
     * execute the close of the connection.
     **/
    public function close()
    {
        if ($this->filehandle !== null) {
            if (!@fclose($this->filehandle)) {
                return false;
            }
            if ($this->is_writeonly()) {
                rename($this->curr_file, DOCEBOIMPORT_BASEDIR . basename($this->curr_file));
            } else {
                $currentDate = new DateTime();
                if (file_exists(DOCEBOIMPORT_BASEDIR . 'processed_' . $currentDate->format('Y-m-d_H:i:s') . '_' . basename($this->curr_file))) {
                    require_once _base_ . '/lib/lib.upload.php';
                    sl_unlink(DOCEBOIMPORT_BASEDIR . 'processed' . $currentDate->format('Y-m-d_H:i:s') . '_' . basename($this->curr_file));
                    rename($this->curr_file, DOCEBOIMPORT_BASEDIR . 'processed' . $currentDate->format('Y-m-d_H:i:s') . '_' . basename($this->curr_file));
                } else {
                    rename($this->curr_file, DOCEBOIMPORT_BASEDIR . 'processed_' . $currentDate->format('Y-m-d_H:i:s') . '_' . basename($this->curr_file));
                }
            }
        }
        $this->filehandle = null;
        $this->row_index = 0;

        return true;
    }

    /**
     * Return the type of the connector.
     **/
    public function get_type_name()
    {
        return 'cvs-connector';
    }

    /**
     * Return the description of the connector.
     **/
    public function get_type_description()
    {
        return 'connector to cvs';
    }

    /**
     * Return the name of the connection.
     **/
    public function get_name()
    {
        return $this->name;
    }

    public function get_description()
    {
        return $this->description;
    }

    public function is_readonly()
    {
        return (bool) ($this->readwrite & 1);
    }

    public function is_writeonly()
    {
        return (bool) ($this->readwrite & 2);
    }

    public function get_tot_cols()
    {
        return count($this->cols_descriptor);
    }

    public function get_cols_descripor()
    {
        foreach ($this->cols_descriptor as $colname) {
            $arr_cols[] = [DOCEBOIMPORT_COLNAME => $colname,
                DOCEBOIMPORT_COLMANDATORY => false,
                DOCEBOIMPORT_DATATYPE => 'text',
                DOCEBOIMPORT_DEFAULT => '',
            ];
        }

        return $arr_cols;
    }

    public function get_first_row()
    {
        if ($this->filehandle === null) {
            return false;
        }
        if (!@rewind($this->filehandle)) {
            return false;
        }
        $row = fgetcsv($this->filehandle, 1024, $this->separator, ($this->enclosure === '' ? "'" . $this->enclosure . "'" : $this->enclosure));
        ++$this->row_index;
        if ($this->first_row_header == '1') {
            $row = fgetcsv($this->filehandle, 1024, $this->separator, ($this->enclosure === '' ? "'" . $this->enclosure . "'" : $this->enclosure));
            ++$this->row_index;
        }
        if (!is_array($row)) {
            return false;
        }
        if (count($row) == 0) {
            return false;
        }

        return $row;
    }

    public function get_next_row()
    {
        if ($this->filehandle === null) {
            return false;
        }
        $row = fgetcsv($this->filehandle, 1024, $this->separator, ($this->enclosure === '' ? "'" . $this->enclosure . "'" : $this->enclosure));
        if (!is_array($row)) {
            return false;
        }
        if (count($row) == 0) {
            return false;
        }
        ++$this->row_index;

        return $row;
    }

    public function is_eof()
    {
        return feof($this->filehandle);
    }

    public function get_row_index()
    {
        return $this->row_index;
    }

    public function get_tot_mandatory_cols()
    {
        return 0;
    }

    public function add_row($row, $pk = null)
    {
        $arr_out = array_flip($this->cols_descriptor);
        foreach ($arr_out as $colname => $val) {
            if (isset($row[$colname])) {
                $arr_out[$colname] = $row[$colname];
            } else {
                $arr_out[$colname] = '';
            }
            //unset($arr_out[$colname]);
        }
        if (!is_array($arr_out)) {
            return true;
        }
        if (!$arr_out) {
            return true;
        }
        $impl = implode('', $arr_out);

        if (!is_string($impl)) {
            return true;
        }

        if ($impl == '') {
            return true;
        }
        //fputcsv($this->filehandle, $arr_out, $this->separator, $this->enclosure );
        $put = $this->enclosure . implode($this->enclosure . $this->separator . $this->enclosure, $arr_out) . $this->enclosure . "\r\n";
        if (@fwrite($this->filehandle, $put)) {
            return true;
        } else {
            return false;
        }
    }

    public function get_error()
    {
        return $this->last_error;
    }
}

/**
 * The configurator for user report connectors.
 *
 * @version    1.0
 **/
class FormaConnectorCsvUI extends FormaConnectorUI
{
    public $connector = null;
    public $post_params = null;
    public $sh_next = true;
    public $sh_prev = false;
    public $sh_finish = false;
    public $step_next = '';
    public $step_prev = '';

    public function __construct(&$connector)
    {
        $this->connector = $connector;
    }

    public function _get_base_name()
    {
        return 'csvuiconfig';
    }

    public function get_old_name()
    {
        return $this->post_params['old_name'];
    }

    /**
     * All post fields are in array 'csvuiconfig'.
     **/
    public function parse_input($get, $post)
    {
        if (!isset($post[$this->_get_base_name()])) {
            // first call - first step, initialize variables
            $this->post_params = $this->connector->get_config();
            $this->post_params['step'] = '0';
            $this->post_params['field_def_type'] = '1';
            $this->post_params['old_name'] = $this->post_params['name'];
            if ($this->post_params['name'] == '') {
                $this->post_params['name'] = $this->lang->def('_CONN_NAME_EXAMPLE');
            }
            if ($this->post_params['filepattern'] == '') {
                $this->post_params['filepattern'] = $this->lang->def('_FILEPATTERN_EXAMPLE');
            }
            if ($this->post_params['subpattern'] == '') {
                $this->post_params['subpattern'] = '';
            }
            if ($this->post_params['field_def'] === null) {
                $this->post_params['field_def'] = ['field1', 'field2', 'field3'];
            }
        } else {
            // get previous values
            $this->post_params = Util::unserialize(urldecode($post[$this->_get_base_name()]['memory']));
            $arr_new_params = $post[$this->_get_base_name()];
            // overwrite with the new posted values
            foreach ($arr_new_params as $key => $val) {
                if ($key != 'memory') {
                    if ($key == 'field_def') {
                        $val = trim(stripslashes($val), $this->post_params['field_enclosure']);

                        $this->post_params[$key] = explode($this->post_params['field_enclosure'] . $this->post_params['field_delimiter'] . $this->post_params['field_enclosure'], stripslashes($val));
                    } else {
                        $this->post_params[$key] = stripslashes($val);
                    }
                }
            }
        }
        $this->_load_step_info();
    }

    public function _set_step_info($next, $prev, $sh_next, $sh_prev, $sh_finish)
    {
        $this->step_next = $next;
        $this->step_prev = $prev;
        $this->sh_next = $sh_next;
        $this->sh_prev = $sh_prev;
        $this->sh_finish = $sh_finish;
    }

    public function _load_step_info()
    {
        switch ($this->post_params['step']) {
            case '0':
                if ($this->post_params['readwrite'] == '1') {
                    $this->_set_step_info('1', '0', true, false, false);
                } else {
                    $this->_set_step_info('1', '0', true, false, false);
                }
                break;
            case '1':
                $this->_set_step_info('1', '0', false, true, true);
                break;
        }
    }

    public function go_next()
    {
        $this->post_params['step'] = $this->step_next;
        $this->_load_step_info();
    }

    public function go_prev()
    {
        $this->post_params['step'] = $this->step_prev;
        $this->_load_step_info();
    }

    public function go_finish()
    {
        $this->filterParams($this->post_params);
        $this->connector->set_config($this->post_params);
    }

    public function show_next()
    {
        return $this->sh_next;
    }

    public function show_prev()
    {
        return $this->sh_prev;
    }

    public function show_finish()
    {
        return $this->sh_finish;
    }

    public function get_htmlheader()
    {
        return '';
    }

    public function get_html($get = null, $post = null)
    {
        $out = '';
        switch ($this->post_params['step']) {
            case '0':
                $out .= $this->_step0();
                break;
            case '1':
                $out .= $this->_step1();
                break;
        }
        // save parameters
        $out .= $this->form->getHidden($this->_get_base_name() . '_memory',
            $this->_get_base_name() . '[memory]',
            urlencode(Util::serialize($this->post_params)));

        return $out;
    }

    public function _step0()
    {
        // ---- name -----
        $out = $this->form->getTextfield($this->lang->def('_NAME'),
            $this->_get_base_name() . '_name',
            $this->_get_base_name() . '[name]',
            255,
            $this->post_params['name']);
        // ---- description -----
        $out .= $this->form->getSimpleTextarea($this->lang->def('_DESCRIPTION'),
            $this->_get_base_name() . '_description',
            $this->_get_base_name() . '[description]',
            $this->post_params['description']);
        // ---- access type read/write -----
        $out .= $this->form->getRadioSet($this->lang->def('_ACCESSTYPE'),
            $this->_get_base_name() . '_readwrite',
            $this->_get_base_name() . '[readwrite]',
            [$this->lang->def('_READ') => '1',
                $this->lang->def('_WRITE') => '2', ],
            $this->post_params['readwrite']);

        // ---- file pattern ----
        $out .= $this->form->getTextfield($this->lang->def('_FILEPATTERN'),
            $this->_get_base_name() . '_filepattern',
            $this->_get_base_name() . '[filepattern]',
            255,
            $this->post_params['filepattern']);

        // ---- method for define fields ----
        $out .= $this->form->getRadioSet($this->lang->def('_FIELD_DEFINITION_TYPE'),
            $this->_get_base_name() . '_def_type',
            $this->_get_base_name() . '[field_def_type]',
            [$this->lang->def('_MANUAL') => '1',
                $this->lang->def('_BYEXAMPLE') => '2', ],
            $this->post_params['field_def_type']
        );
        $out .= $this->form->getTextfield($this->lang->def('_FIELD_DELIMITER'),
            $this->_get_base_name() . '_field_delimiter',
            $this->_get_base_name() . '[field_delimiter]',
            1,
            $this->post_params['field_delimiter']);
        $out .= $this->form->getTextfield($this->lang->def('_FIELD_ENCLOSURE'),
            $this->_get_base_name() . '_field_enclosure',
            $this->_get_base_name() . '[field_enclosure]',
            1,
            htmlentities($this->post_params['field_enclosure']));

        $out .= $this->form->getTextfield($this->lang->def('_FIELD_SUBPATTERN'),
            $this->_get_base_name() . '_subpattern',
            $this->_get_base_name() . '[subpattern]',
            255,
            htmlentities($this->post_params['subpattern']));

        $out .= $this->form->getRadioSet($this->lang->def('_FIRST_ROW_HEADER'),
            $this->_get_base_name() . '_first_row_header',
            $this->_get_base_name() . '[first_row_header]',
            [$this->lang->def('_YES') => '1',
                $this->lang->def('_NO') => '0', ],
            $this->post_params['first_row_header']
        );

        return $out;
    }

    public function _step1()
    {
        $enclosure = htmlentities($this->post_params['field_enclosure']);
        $out = $this->form->getLineBox($this->lang->def('_FIELD_DELIMITER'),
            $this->post_params['field_delimiter']);
        $out .= $this->form->getLineBox($this->lang->def('_FIELD_ENCLOSURE'),
            $enclosure);
        if ($this->post_params['field_def_type'] == '2') {
            $path = $GLOBALS['where_files_relative'] . '/common/iofiles/' . $this->post_params['subpattern'];
            $pat = str_replace(['*', '?'], ['.*', '.{1}'], $this->post_params['filepattern']);
            $arr_files = preg_ls($path, false, '/' . $pat . '/');
            if (count($arr_files) == 0) {
                $this->post_params['field_def'] = ['File not found: ' . $pat];
            } else {
                $hfile = @fopen($arr_files[0], 'r');
                if ($hfile === false) {
                    $this->post_params['field_def'] = ['File not open: ' . $arr_files[0]];
                } else {
                    $this->post_params['field_def'] = fgetcsv($hfile, 1024, $this->post_params['field_delimiter'], $this->post_params['field_enclosure']);
                    $out .= $this->form->getLineBox($this->lang->def('_FILE_ANALYZED'),
                        basename($arr_files[0]));
                    fclose($hfile);
                }
            }
        }
        $field_def = $enclosure . implode($enclosure . $this->post_params['field_delimiter'] . $enclosure, $this->post_params['field_def']) . $enclosure;

        $out .= $this->form->getTextfield($this->lang->def('_FIELD_DEF'),
            $this->_get_base_name() . '_field_def',
            $this->_get_base_name() . '[field_def]',
            1024,
            $field_def);

        return $out;
    }
}

function csv_factory()
{
    return new FormaConnectorCsv([]);
}

function preg_ls($path = '.', $rec = false, $pat = '/.*/')
{
    $pat = preg_replace('|(/.*/[^S]*)|s', '\\1S', $pat);
    while (substr($path, -1, 1) == '/') {
        $path = substr($path, 0, -1);
    }
    if (!is_dir($path)) {
        $path = dirname($path);
    }
    if ($rec !== true) {
        $rec = false;
    }
    $d = dir($path);
    $ret = [];
    while (false !== ($e = $d->read())) {
        if (($e == '.') || ($e == '..')) {
            continue;
        }
        if ($rec && is_dir($path . '/' . $e)) {
            $ret = array_merge($ret, preg_ls($path . '/' . $e, $rec, $pat));
            continue;
        }
        if (!preg_match($pat, $e)) {
            continue;
        }
        if (strncmp($e, 'processed', 9) === 0) {
            continue;
        }
        $ret[] = $path . '/' . $e;
    }

    return (empty($ret) && preg_match($pat, basename($path))) ? [$path . '/'] : $ret;
}
