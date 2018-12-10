<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class CoreMenu {

    public static function getList($platform, $only_active = true) {

        if(!is_array($platform)) {
            $platform = array($platform);
        }

        $platform = implode(', ', array_map(function($pl) {
            return "'$pl'";
        }, $platform));

        if($only_active) {
            $only_active = "AND m.is_active = true";
        }

        $query =
<<<SQL
SELECT  m.idMenu, m.idParent, m.sequence, m.name, m.image, m.is_active, mu.idUnder
      , mu.module_name, mu.default_op, mu.mvc_path, mu.associated_token, mu.of_platform
FROM %adm_menu AS m
    LEFT JOIN %adm_menu_under AS mu ON (m.idMenu = mu.idMenu)
WHERE 1 = 1
    AND m.of_platform IN ($platform)
    $only_active
ORDER BY m.sequence
SQL;

        $res = sql_query($query);
        
        $menu = array();
        while($row = sql_fetch_object($res)) {
            $menu[] = $row;
        }

        $menu = self::buildMenuArray($menu);

        return $menu;
    }
    
    private static function buildMenuArray($menu, $parent = 0) {

        $_menu = array();
        foreach($menu as &$item) {
            if((int)$item->idParent === $parent) {
                $item->submenu  = self::buildMenuArray($menu, (int)$item->idMenu);
                $item->role     = self::role($item);
                $item->url      = self::url($item);
                $_menu[] = $item;
            }
        }
        return $_menu;
    }

    private static function role($menu_item) {

        switch($menu_item->of_platform){
            case 'lms':
                $role = '/lms/course/public';
                break;
            case 'alms':
                $role = '/lms/admin';
                break;
            case 'framework':
                $role = '/framework/admin';
                break;
            default:
                $role = '';
                break;
        }

        if($menu_item->module_name) {            
            $role .= "/$menu_item->module_name";
        }

        if($menu_item->associated_token) {
            $role .= "/$menu_item->associated_token";
        }

        return $role;
    }

    private static function url($menu_item) {

        switch($menu_item->of_platform){
            case 'lms':
                $to = 'lms';
                $of_platform = 'lms';
                break;
            case 'alms':
                $to = 'adm';
                $of_platform = 'lms';
                break;
            case 'framework':
                $to = 'adm';
                $of_platform = 'framework';
                break;
            default:
                $to = false;
                $of_platform = null;
                break;
        }

        $url = Get::abs_path($to);

        $query_url = array();
        if($menu_item->mvc_path) {
            $query_url['r'] = $menu_item->mvc_path;
        } elseif($menu_item->module_name) {
            $query_url['modname'] = $menu_item->module_name;
            $query_url['op'] = $menu_item->default_op;
        }
        if($to === 'lms') {
            $query_url['sop'] = "unregistercourse";
        }
        if(!$menu_item->mvc_path) {
            $query_url['of_platform'] = $of_platform;
        }
        $query_url = urldecode(http_build_query($query_url, '', '&'));
        if($query_url) $url .= "/index.php?$query_url";

        return $url;
    }

    public static function get($id) {

        $query =
<<<SQL
SELECT  m.idMenu, m.idParent, m.sequence, m.name, m.image, m.is_active, mu.idUnder
      , mu.module_name, mu.default_op, mu.mvc_path, mu.associated_token, mu.of_platform
FROM %adm_menu AS m
    LEFT JOIN %adm_menu_under AS mu ON (m.idMenu = mu.idMenu)
WHERE 1 = 1
    AND m.idMenu = $id
ORDER BY m.sequence
SQL;

        $menu = sql_fetch_object(sql_query($query));
        $menu->role = "/lms/course/public/$menu->module_name/$menu->associated_token";
        $item->url  = self::url($item);
        return $menu;
    }

    public static function set($id, $values) {

        $sets = array();
        foreach($values as $field => $value) {
            $sets[] = "$field = '$value'";
        }
        $sets = implode(', ', $sets);
        
        $query =
<<<SQL
UPDATE %adm_menu
SET $sets
WHERE idMenu = $id
SQL;

        return (bool)sql_query($query);
    }

    public static function addMenuChild($name, $mvcPath, $parent=false, $icon='', $is_active=true, $idPlugin=null){

        // Check if $name contains only alphanumeric characters or undescores.
        if(preg_match('/[^a-z_\-0-9]/i', $name)){
            return false;
        }

        $idPlugin = (int)$idPlugin;

        $idParent = 'NULL';
        
        $is_active = ($is_active) ? 'true' : 'false';

        // Get idMenu
        if($parent){
            $idParentQuery = " SELECT idMenu FROM core_menu WHERE name = '$parent' ";
            $idParentResult = sql_query($idParentQuery);
            if($idParentResult){
                if($idParentRow = sql_fetch_row($idParentResult)){
                    $idParent = $idParentRow[0];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        // Get sequence
        $where = ' idParent ';
        if($idParent!='NULL'){
            $where .= "= $idParent ";
        } else{
            $where .= "IS NULL ";
        }
        $sequence = null;
        $sequenceQuery = " SELECT max(sequence)+1, count(sequence) as count FROM core_menu WHERE $where ";
        $sequenceResult = sql_query($sequenceQuery);
        if($sequenceResult){
            if($sequenceRow = sql_fetch_row($sequenceResult)){
                if($sequenceRow[1]>0){
                    $sequence = $sequenceRow[0];
                } else {
                    $sequence = 1;
                }
                
            } else {
                return false;
            }
        } else {
            return false;
        }

        // Insert into core_menu
        $queryMenu = "INSERT INTO 
            %adm_menu(
                idparent,
                name,
                sequence,
                is_active,
                image,
                idPlugin
            )
        VALUES
            (
                $idParent,
                '$name',
                $sequence,
                '$is_active',
                '$icon',
                $idPlugin
            )
        ";
        
        // Insert into core_menu_under
        if(sql_query($queryMenu)){
            $idMenu = sql_insert_id();
            $queryMenuUnder = "INSERT INTO 
                %adm_menu_under(
                    idMenu,
                    default_name,
                    default_op,
                    associated_token,
                    of_platform,
                    sequence,
                    class_file,
                    class_name,
                    mvc_path
                ) 
            VALUES
                (
                    $idMenu,
                    '$name',
                    '',
                    'view',
                    NULL,
                    1,
                    '',
                    '',
                    '$mvcPath'
                )
            ";
            if(sql_query($queryMenuUnder)){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
