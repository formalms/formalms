<?php
defined('IN_FORMA') or exit('Direct access is forbidden.');

class DbHelper {

}


function sql_query($query, $conn = false)
{
    $db = \FormaLms\db\DbConn::getInstance($conn);
    $re = $db->query($query);

    return $re;
}

function sql_limit_query($query, $from, $results, $conn = false)
{
    $db = \FormaLms\db\DbConn::getInstance($conn);
    $re = $db->query_limit($query, $from, $results);

    return $re;
}

function sql_insert_id($conn = false)
{
    $db = \FormaLms\db\DbConn::getInstance($conn);
    $re = $db->insert_id();

    return $re;
}

function sql_num_rows($res)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->num_rows($res);

    return $re;
}

function sql_fetch_row($res)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->fetch_row($res);

    return $re;
}

function sql_fetch_assoc($res)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->fetch_assoc($res);

    return $re;
}

function sql_fetch_array($res)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->fetch_array($res);

    return $re;
}

function sql_fetch_object($res, $class_name = null, $params = null)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->fetch_obj($res, $class_name, $params);

    return $re;
}

function sql_escape_string($res)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->escape_string($res);

    return $re;
}

function sql_error($link = null)
{
    $db = \FormaLms\db\DbConn::getInstance($link);
    $re = $db->error();

    return $re;
}

function sql_free_result($res)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->free_result($res);

    return $re;
}

function sql_get_client_info($link = null)
{
    $db = \FormaLms\db\DbConn::getInstance($link);
    $re = $db->get_client_info();

    return $re;
}

function sql_get_server_info($link = null)
{
    $db = \FormaLms\db\DbConn::getInstance($link);
    $re = $db->get_server_info();

    return $re;
}

function sql_get_server_version($link = null)
{
    $db = \FormaLms\db\DbConn::getInstance($link);
    $re = $db->query('SELECT VERSION() as version');

    $result = \FormaLms\db\sql_fetch_assoc($re);

    return $result['version'];
}

function sql_data_seek($result, $row_number)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->data_seek($result, $row_number);

    return $re;
}

function sql_errno($link = null)
{
    $db = \FormaLms\db\DbConn::getInstance($link);
    $re = $db->errno();

    return $re;
}

function sql_affected_rows($link = null)
{
    $db = \FormaLms\db\DbConn::getInstance($link);
    $re = $db->affected_rows();

    return $re;
}

function sql_field_seek($result, $fieldnr)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->field_seek($result, $fieldnr);

    return $re;
}

function sql_num_field($res)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->num_fields($res);

    return $re;
}

function sql_fetch_field($result)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->fetch_field($result);

    return $re;
}

function sql_real_escape_string()
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->real_escape_string();

    return $re;
}

function sql_connect($db_host, $db_user, $db_pass, $db_name = false)
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->connect($db_host, $db_user, $db_pass, $db_name);

    return $re;
}

function sql_select_db($db_name, $link = false)
{
    $db = \FormaLms\db\DbConn::getInstance($link);
    $re = $db->select_db($db_name);

    return $re;
}

function sql_close()
{
    $db = \FormaLms\db\DbConn::getInstance();
    $re = $db->close();

    return $re;
}