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

/*
 * This is the base library for import/export operations in Docebo.
 * You should import this library if you want to develop your own
 * source or destination connector. This file is also imported in
 * modules/ioTask.php
 *
 * @package admin-library
 * @subpackage io-operation
 * @version  $Id:$
 * @version 	$Id: lib.import.php 552 2006-08-02 16:02:38Z fabio $
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/

/* Index for column name */
define('DOCEBOIMPORT_COLNAME', 0);
/* Index for column id */
define('DOCEBOIMPORT_COLID', 1);
/* Index for column data type */
define('DOCEBOIMPORT_DATATYPE', 2);
/* Index for column mandatory flag */
define('DOCEBOIMPORT_COLMANDATORY', 3);
/* Index for column default value */
define('DOCEBOIMPORT_DEFAULT', 4);

/* Unknown data type */
define('DOCEBOIMPORT_DATATYPE_UNKNOWN', -1);
/* This field should be ignored */
define('DOCEBOIMPORT_IGNORE', 'ignorefield');

/**
 * abstract class for define the source of an import.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboImport_Source
{
    /**
     * Return the number of columns.
     *
     * @return int the number of columns in the source
     **/
    public function get_tot_cols()
    {
    }

    /**
     * execute the connection to source.
     **/
    public function connect()
    {
    }

    /**
     * execute the close of the connection.
     **/
    public function close()
    {
    }

    /**
     * @return array the array of columns descriptor
     *               DOCEBOIMPORT_COLNAME => string the name of the column
     *               DOCEBOIMPORT_DATATYPE => the data type of the column
     **/
    public function get_cols_descripor()
    {
    }

    /**
     * @return array fist row of data in source
     **/
    public function get_first_row()
    {
    }

    /**
     * @return array next row of data in source
     **/
    public function get_next_row()
    {
    }

    /**
     * @return bool TRUE if the source is at EOF
     **/
    public function is_eof()
    {
    }

    /**
     * @return int the actual position in source. Base index = 0
     **/
    public function get_row_index()
    {
    }

    /**
     * @return the charset used
     **/
    public function get_charset()
    {
    }
}

/**
 * abstract class for define the destination of an import.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboImport_Destination
{
    /**
     * execute the connection to source.
     **/
    public function connect()
    {
    }

    /**
     * execute the close of the connection.
     **/
    public function close()
    {
    }

    /**
     * @return int the number of columns in the source
     **/
    public function get_tot_cols()
    {
    }

    /**
     * @return array the array of columns descriptor
     *               - DOCEBOIMPORT_COLNAME => string the name of the column
     *               - DOCEBOIMPORT_COLID => string the id of the column (optional,
     *               same as COLNAME if not given)
     *               - DOCEBOIMPORT_COLMANDATORY => bool TRUE if col is mandatory
     *               - DOCEBOIMPORT_DATATYPE => the data type of the column
     *               - DOCEBOIMPORT_DEFAULT => the default value for the column (Optional)
     **/
    public function get_cols_descripor()
    {
    }

    /**
     * @return int the number of mandatory columns to import
     **/
    public function get_tot_mandatory_cols()
    {
    }

    /**
     * @param array data to insert; is an array with keys the names of cols and
     *				values the data
     *
     * @return true if the row was succesfully inserted, FALSE otherwise
     **/
    public function add_row($row)
    {
        $row = $row;
    }

    /**
     * @param string $charset the charset used
     **/
    public function set_charset($charset)
    {
    }

    /**
     * Return the last generated error.
     *
     * @return the description of the last error
     **/
    public function get_error()
    {
    }
}

/**
 * class for mysql source import.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboImport_DestinationMySQL extends DoceboImport_Destination
{
    public $dbconn = null;
    public $table = null;
    public $mandatory_cols = null;
    public $cols_descriptor = null;
    public $last_error = null;

    /**
     * constructor for mysql destination connection.
     *
     * @param array $params
     *                      - 'dbconn' => connection to database (required)
     *                      - 'table' => table name for insert (required)
     *                      - 'mandatory_cols' => array of names of mandatory columns (Optional).
     *                      if not given it's computed from table
     *                      fields definition
     **/
    public function DoceboImport_DestinationMySQL($params)
    {
        $this->dbconn = $params['dbconn'];
        $this->table = $params['table'];
        if (isset($params['mandatory_cols'])) {
            $this->mandatory_cols = $params['mandatory_cols'];
        }
    }

    public function connect()
    {
        $this->last_error = null;
        $this->cols_descriptor = null;
        if ($this->dbconn === null) {
            $this->last_error = 'dbconn is null';

            return false;
        }
        $query = 'SHOW FIELDS FROM ' . $this->table;
        $rs = sql_query($query, $this->dbconn);
        if ($rs === false) {
            $this->last_error = 'Error on query: ' . $query . ' [' . sql_error($this->dbconn) . ']';

            return false;
        }
        $this->cols_descriptor = [];
        if ($this->mandatory_cols === null) {
            $computed_mandatory_cols = [];
        }
        while ($field_info = sql_fetch_array($rs)) {
            if ($this->mandatory_cols === null) {
                if ($field_info['Null'] != 'YES') {
                    $computed_mandatory_cols[] = $field_info['Field'];
                    $mandatory = true;
                } else {
                    $mandatory = false;
                }
            } else {
                $mandatory = in_array($field_info['Field'], $this->mandatory_cols);
            }
            $this->cols_descriptor[] =
                        [DOCEBOIMPORT_COLNAME => $field_info['Field'],
                                DOCEBOIMPORT_COLMANDATORY => $mandatory,
                                DOCEBOIMPORT_DATATYPE => $field_info['Type'], ];
        }
        if ($this->mandatory_cols === null) {
            $this->mandatory_cols = $computed_mandatory_cols;
        }

        sql_free_result($rs);

        return true;
    }

    public function close()
    {
        $this->cols_descriptor = null;
    }

    public function get_tot_cols()
    {
        if ($this->cols_descriptor === null) {
            return 0;
        }

        return count($this->cols_descriptor);
    }

    public function get_cols_descripor()
    {
        return $this->cols_descriptor;
    }

    public function get_tot_mandatory_cols()
    {
        if ($this->mandatory_cols === null) {
            return 0;
        }

        return count($this->mandatory_cols);
    }

    public function add_row($row)
    {
        if ($this->mandatory_cols === null) {
            return false;
        }
        // ferify all mandatory cols
        $keys = array_keys($row);
        foreach ($this->mandatory_cols as $col_name) {
            if (!in_array($col_name, $keys)) {
                $this->last_error = 'Some mandatory cols is not present';

                return false;
            }
        }
        $fields = "('" . implode("','", $keys) . "')";
        $values = "('" . implode("','", array_values($row)) . "')";

        $query = 'INSERT INTO ' . $this->table
                . ' ' . $fields . ' VALUES ' . $values;
        if (!sql_query($query, $this->dbconn)) {
            $this->last_error = 'Error on query: ' . $query . ' [' . sql_error($this->dbconn) . ']';

            return false;
        } else {
            return true;
        }
    }

    public function get_error()
    {
        return $this->last_error;
    }
}
/**
 *	The base source connector for csv files.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DeceboImport_SourceCSV extends DoceboImport_Source
{
    public $filename = null;
    public $filehandle = null;
    public $first_row_header = true;
    public $separator = ',';
    public $charset = 'UTF-8';
    public $cols_descriptor = null;
    public $row_index = 0;

    /**
     *	This constructor require the source file name.
     *
     * @param array $params the array of params
     *                      - 'filename' => name of the file (required)
     *                      - 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
     *                      - 'separator' => string a char with the fields separator (Optional, default = ,)
     **/
    public function DeceboImport_SourceCSV($params)
    {
        $this->filename = $params['filename'];
        if (isset($params['first_row_header'])) {
            $this->first_row_header = $params['first_row_header'];
        }
        if (isset($params['separator'])) {
            $this->separator = $params['separator'];
        }
        if (isset($params['import_charset'])) {
            $this->charset = $params['import_charset'];
        }
    }

    public function connect()
    {
        $this->close();
        $this->cols_descriptor = null;
        $this->filehandle = @fopen($this->filename, 'r');
        if ($this->filehandle === false) {
            echo 'file not opened: ' . $this->filename;

            return false;
        }
        $separator = (!$this->separator ? ',' : $this->separator); //if no separator has been specified, then use standard separators
        $row = fgetcsv($this->filehandle, 10000, $separator);
        if (is_array($row) && count($row) == 1 && !$this->separator) {
            $separator = ';';
            if (!@rewind($this->filehandle)) {
                return false;
            }
            $row = fgetcsv($this->filehandle, 10000, $separator);
        }
        $this->separator = $separator; //assign detected separator
        if (!is_array($row)) {
            echo 'no rows found on ' . $this->filename;

            return false;
        }
        if (count($row) == 0) {
            echo 'no rows found on ' . $this->filename;

            return false;
        }
        $this->cols_descriptor = [];
        if ($this->first_row_header) {
            foreach ($row as $col_name) {
                $this->cols_descriptor[] = [DOCEBOIMPORT_COLNAME => $col_name,
                                            DOCEBOIMPORT_DATATYPE => DOCEBOIMPORT_DATATYPE_UNKNOWN, ];
            }
            $this->row_index = 1;
        } else {
            // the column names will be the col number 0 ... n
            for ($name = 0; $name < count($row); ++$name) {
                $this->cols_descriptor[] = [DOCEBOIMPORT_COLNAME => (string) $name,
                                            DOCEBOIMPORT_DATATYPE => DOCEBOIMPORT_DATATYPE_UNKNOWN, ];
            }
        }

        return true;
    }

    public function close()
    {
        $this->cols_descriptor = null;
        if ($this->filehandle !== null) {
            if (!@fclose($this->filehandle)) {
                return false;
            }
        }
        $this->filehandle = null;
        $this->row_index = 0;

        return true;
    }

    public function get_tot_cols()
    {
        if ($this->cols_descriptor === null) {
            return 0;
        }

        return count($this->cols_descriptor);
    }

    public function get_cols_descripor()
    {
        return $this->cols_descriptor;
    }

    public function get_first_row()
    {
        if ($this->filehandle === null) {
            return false;
        }
        if (!@rewind($this->filehandle)) {
            return false;
        }
        $row = fgetcsv($this->filehandle, 10000, $this->separator);
        ++$this->row_index;
        if ($this->first_row_header) {
            $row = fgetcsv($this->filehandle, 10000, $this->separator);
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
        $row = fgetcsv($this->filehandle, 10000, $this->separator);
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

    public function get_charset()
    {
        return $this->charset;
    }
}

define('DOCEBOIMPORT_TYPECSV', 0);
define('DOCEBOIMPORT_TYPEMYSQL', 1);
/**
 * class to manage import.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboImport
{
    public $source = null;
    public $destination = null;
    public $import_map = null;
    public $import_tocompare = null;

    /**
     * static function to create a Source standard instance.
     *
     * @static
     *
     * @param int   $type   one of DOCEBOIMPORT_TYPEXXX constant
     * @param array $params params for the DoceboImport_Source constructor
     *
     * @return DoceboImport_Source instance of DoceboImport_Source; NULL if method
     *                             fail
     **/
    public function createImport_Source($type, $params)
    {
        switch ($type) {
            case DOCEBOIMPORT_TYPECSV:
                return new DeceboImport_SourceCSV($params);
        }

        return null;
    }

    /**
     * static function to create a Destination standard instance.
     *
     * @static
     *
     * @param int   $type   one of DOCEBOIMPORT_TYPEXXX constant
     * @param array $params params for the DoceboImport_Destination constructor
     *
     * @return DoceboImport_Destination instance of DoceboImport_Destination;
     *                                  NULL if method fail
     **/
    public function createImport_Destination($type, $params)
    {
        switch ($type) {
            case DOCEBOIMPORT_MYSQL:
                return new DoceboImport_DestinationMySQL($params);
        }

        return null;
    }

    public function setSource(&$source)
    {
        $this->source = &$source;
    }

    public function setDestination(&$destination)
    {
        $this->destination = &$destination;
    }

    /**
     * This method create an HTML UI for create the map of fields from
     * source to destination.
     **/
    public function getUIMap()
    {
        require_once _base_ . '/lib/lib.table.php';
        require_once _base_ . '/lib/lib.form.php';
        $lang = &DoceboLanguage::createInstance('organization_chart', 'framework');
        $form = new Form();
        $table = new Table(Get::sett('visuItem'), $lang->def('_IMPORT_MAP'), $lang->def('_IMPORT_MAP'));

        $src_cols = $this->source->get_cols_descripor();
        $dst_cols = $this->destination->get_cols_descripor();

        $combo_elements = [];
        $combo_elements[DOCEBOIMPORT_IGNORE] = $lang->def('_IMPORT_IGNORE');

        foreach ($dst_cols as $col) {
            if (isset($col[DOCEBOIMPORT_COLID])) {
                $combo_elements[$col[DOCEBOIMPORT_COLID]] = $col[DOCEBOIMPORT_COLNAME];
            } else {
                $combo_elements[$col[DOCEBOIMPORT_COLNAME]] = $col[DOCEBOIMPORT_COLNAME];
            }
        }

        $table_dst_labels = [];
        $table_dst_tocompare = [];
        $table_src_labels = [];
        $table_src_labels_type = [];
        $count = 0;
        foreach ($src_cols as $col) {
            $table_src_labels[] = $col[DOCEBOIMPORT_COLNAME];
            $table_src_labels_type[] = '';
            $table_dst_labels[] = $form->getInputDropdown('dropdown_nowh',
                                                        'import_map_' . $count,
                                                        'import_map[' . $count . ']',
                                                        $combo_elements,
                                                        DOCEBOIMPORT_IGNORE,
                                                        '');
            $table_dst_tocompare[] = $form->getHidden('import_tocompare_' . $count, 'import_tocompare[' . $count . ']', '', true);
            ++$count;
        }

        $table->setColsStyle($table_src_labels_type);
        $table->addHead($table_dst_labels);
        $table->addHead($table_dst_tocompare);
        $table->addHead($this->encode_array($table_src_labels, $this->source->get_charset()));
        $count = 0;
        $row = $this->source->get_first_row();

        while ($row !== false && $count < 10) {
            $table->addBody($this->encode_array($row, $this->source->get_charset()));
            $row = $this->source->get_next_row();
            ++$count;
        }

        return $table->getTable();
    }

    public function getTotRow()
    {
        $count = 0;
        $row = $this->source->get_first_row();

        while ($row !== false) {
            $row = $this->source->get_next_row();
            ++$count;
        }

        return $count;
    }

    public function encode_array(&$row, $charset)
    {
        for ($index = 0; $index < count($row); ++$index) {
            $row[$index] = htmlentities($row[$index], ENT_QUOTES, $charset);
        }

        return $row;
    }

    public function parseMap()
    {
        if (isset($_POST['import_map'])) {
            $this->import_map = $_POST['import_map'];
            $this->import_tocompare = $_POST['import_tocompare'];
        }
    }

    /**
     * Do the import operation. This function reads all row from source and puts
     * its on destination.
     *
     * @return array with input_row_index => error only for rows with error
     *               in index 0 there are the total processed rows
     **/
    public function doImport()
    {
        $out = []; 	// error list
        $dst_cols = $this->destination->get_cols_descripor();
        $row = $this->source->get_first_row();
        $i = 0;
        $open_transaction = false;
        while ($row !== false) {
            $insrow = [];
            $tocompare = [];
            for ($index = 0; $index < count($this->import_map); ++$index) {
                if ($this->import_map[$index] != DOCEBOIMPORT_IGNORE) {
                    $insrow[$this->import_map[$index]] = $row[$index];
                    if (in_array($index, array_keys($this->import_tocompare))) {
                        $tocompare[$this->import_map[$index]] = $row[$index];
                    }
                }
            }

            if ($i == 0) {
                Docebo::db()->start_transaction();
                $open_transaction = true;
            }
            /*
            foreach( $dst_cols as $col ) {
                $col_name = isset($col[DOCEBOIMPORT_COLID])?$col[DOCEBOIMPORT_COLID]:$col[DOCEBOIMPORT_COLNAME];
                if( !isset($insrow[$col_name]) ) {
                    $insrow[$col_name] = (isset($col[DOCEBOIMPORT_DEFAULT]) ? $col[DOCEBOIMPORT_DEFAULT] : '');
                }
            }*/
            $this->destination->set_charset($this->source->get_charset());
            //if( !$this->destination->add_row($insrow) ) {
            if (!$this->destination->add_row($insrow, $tocompare)) {
                $out[$this->source->get_row_index()] = $this->destination->get_error();
            }

            if ($i == 100) {
                $i = 0;
                Docebo::db()->commit();
                $open_transaction = false;
            } else {
                ++$i;
            }
            $row = $this->source->get_next_row();

            // Increment the counter for users created by this admin:
            if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                $admin_pref = new AdminPreference();
                $pref = $admin_pref->getAdminRules(Docebo::user()->getIdSt());
                if ($pref['admin_rules.limit_user_insert'] == 'on') {
                    $user_pref = new UserPreferences(Docebo::user()->getIdSt());
                    $user_created_count = (int) $user_pref->getPreference('user_created_count');
                    ++$user_created_count;
                    $user_pref->setPreference('user_created_count', $user_created_count);
                }
            }
        }
        if ($open_transaction) {
            Docebo::db()->commit();
        }
        $out[0] = ($this->source->first_row_header ? $this->source->get_row_index() - 1 : $this->source->get_row_index());

        return $out;
    }
}
