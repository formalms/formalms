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

/*
 * @package admin-core
 * @subpackage io-operation
 * @version 	$id$
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
**/

/* base directory for search io files **/
define('FORMAIMPORT_BASEDIR', _files_ . '/common/iofiles/');

/* Index for column name */
define('FORMAIMPORT_COLNAME', 0);
/* Index for column id */
define('FORMAIMPORT_COLID', 1);
/* Index for column data type */
define('FORMAIMPORT_DATATYPE', 2);
/* Index for column mandatory flag */
define('FORMAIMPORT_COLMANDATORY', 3);
/* Index for column default value */
define('FORMAIMPORT_DEFAULT', 4);

/* Unknown data type */
define('FORMAIMPORT_DATATYPE_UNKNOWN', -1);
/* This field should be ignored */
define('FORMAIMPORT_IGNORE', 'ignorefield');
/* Indicate that a connection don't have data to process **/
define('FORMA_IMPORT_NOTHINGTOPROCESS', 1);

/**
 * abstract class for define connection to data source.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnector
{
    public $cache_inserted = false;

    /**
     * constructor.
     *
     * @param array params
     **/
    public function __construct($params)
    {
    }

    /**
     * set configuration.
     *
     * @param array $params
     **/
    public function set_config($params)
    {
    }

    /**
     * get configuration.
     *
     * @return array
     **/
    public function get_config()
    {
    }

    /**
     * get configuration UI.
     *
     * @return FormaConnectorUI
     **/
    public function get_configUI()
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
     * Return the type of the connector.
     **/
    public function get_type_name()
    {
    }

    /**
     * Return the description of the connector.
     **/
    public function get_type_description()
    {
    }

    /**
     * Return the name of the connection.
     **/
    public function get_name()
    {
    }

    /**
     * Return the description of the connection.
     **/
    public function get_description()
    {
    }

    /**
     * indicate if this connection is read only.
     *
     * @return bool TRUE is connector is read only
     **/
    public function is_readonly()
    {
    }

    /**
     * indicate if this connection is write only.
     *
     * @return bool TRUE is connector is read only
     **/
    public function is_writeonly()
    {
    }

    /**
     * indicate if this source produce raw rows.
     * raw rows producer don't need map.
     **/
    public function is_raw_producer()
    {
        return false;
    }

    /**
     * Return the number of columns.
     *
     * @return int the number of columns in the source
     **/
    public function get_tot_cols()
    {
    }

    /**
     * @return array the array of columns descriptor
     *               - FORMAIMPORT_COLNAME => string the name of the column
     *               - FORMAIMPORT_COLID => string the id of the column (optional,
     *               same as COLNAME if not given)
     *               - FORMAIMPORT_COLMANDATORY => bool TRUE if col is mandatory
     *               - FORMAIMPORT_DATATYPE => the data type of the column
     *               - FORMAIMPORT_DEFAULT => the default value for the column (Optional)
     *               For readonly connectos only 	FORMAIMPORT_COLNAME and FORMAIMPORT_DATATYPE
     *               are required
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
     * @return array next row of data in source (the field must be in the same order of the get_cols_descripor)
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
     * @return int the number of mandatory columns to import
     **/
    public function get_tot_mandatory_cols()
    {
    }

    /**
     * This function perform the data insertion in to the source.
     *
     * @param array $row data to insert; is an array with keys the names of cols and
     *                   values the data (must contain the pk)
     * @param array $pk  set of primary keys (col_name => value pairs)
     *
     * @return true if the row was succesfully inserted, FALSE otherwise
     **/
    public function add_row($row, $pk)
    {
        $row = $row;
    }

    /**
     * This method delete a record based on a set of keys.
     *
     * @param array $pk an array of col_name => value pairs
     *
     * @return true if successfully deleted, FALSE otherwise
     **/
    public function delete_bypk($pk)
    {
        $pk = $pk;
    }

    /**
     * This method delete all records not present in array of keys.
     *
     * @param array $arr_pk an array of pk of col_name => value pairs
     *
     * @return true if successfully deleted, FALSE otherwise
     **/
    public function delete_all_filtered($pk)
    {
        $pk = $pk;
    }

    /**
     * The delete_all_notinserted remove all records not inserted
     * during this connection.
     *
     * @return int number of removed items
     */
    public function delete_all_notinserted()
    {
    }

    /**
     * Use this method to set/unset the cache of inserted row
     * in this connection.
     *
     * @param bool $set
     */
    public function enable_cache_inserted($set)
    {
        $this->cache_inserted = $set;
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
 * abstract class for define connector's UI configurator.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnectorUI
{
    public $form = null;
    public $lang = null;

    /**
     * This method is to set the $lang object.
     *
     * @param FormaLang $lang
     **/
    public function set_lang(&$lang)
    {
        $this->lang = $lang;
    }

    /**
     * This method is to set the $form object.
     *
     * @param Form $form
     **/
    public function set_form(&$form)
    {
        $this->form = $form;
    }

    /**
     * This function is colled by connection manager before any other function.
     * After this function the connection manager can call all other function,.
     *
     * @param array $get  the url get parameters
     * @param array $post the post paramteres
     **/
    public function parse_input($get, $post)
    {
    }

    /**
     * With this function we can test if the configurator want a next button.
     *
     * @return bool TRUE if we must produce a next button
     **/
    public function show_next()
    {
    }

    /**
     * With this function we can test if the configurator want a prev button.
     *
     * @return bool TRUE if we must produce a prev button
     **/
    public function show_prev()
    {
    }

    /**
     * With this function we can test if the configurator want a finish button.
     *
     * @return bool TRUE if we must produce a finish button
     **/
    public function show_finish()
    {
    }

    /**
     * With this function the container can tell connector configurator that
     *	the user as pressed next button.
     **/
    public function go_next()
    {
    }

    /**
     * With this function the container can tell connector configurator that
     *	the user as pressed prev button.
     **/
    public function go_prev()
    {
    }

    /**
     * With this function the container can tell connector configurator that
     *	the user as pressed finish button.
     **/
    public function go_finish()
    {
    }

    /**
     * return a string for set header for UI configurator.
     *
     * @return string the html to put in HTML header
     **/
    public function get_htmlheader()
    {
    }

    /** return the html that draw interface of configurator.
     *  @return string html that draw interface of configurator
     * */
    public function get_html($get, $post)
    {
    }

    public function filterParams(&$params)
    {
        $filter_input = new FilterInput();
        $filter_input->tool = FormaLms\lib\Get::cfg('filter_tool', 'htmlpurifier');
        $params = $filter_input->clean($params);
    }
}
