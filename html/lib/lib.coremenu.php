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
    LEFT JOIN %adm_plugin AS p ON (m.idPlugin = p.plugin_id)
    LEFT JOIN %adm_menu_under AS mu ON (m.idMenu = mu.idMenu)
WHERE 1 = 1
    AND m.of_platform IN ($platform)
    AND ( m.idPlugin IS NULL OR p.active = 1 )
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
                $item->role     = self::role($item->of_platform, $item->module_name, $item->associated_token);
                $item->url      = self::url($item->of_platform, $item->mvc_path, $item->module_name, $item->default_op);
                $_menu[] = $item;
            }
        }
        return $_menu;
    }

    private static function role($of_platform, $module_name, $associated_token) {

        switch($of_platform){
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

        if($module_name) {            
            $role .= "/$module_name";
        }

        if($associated_token) {
            $role .= "/$associated_token";
        }

        return $role;
    }

    private static function url($of_platform, $mvc_path, $module_name, $default_op) {

        switch($of_platform){
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
        if($mvc_path) {
            $query_url['r'] = $mvc_path;
        } elseif($module_name) {
            $query_url['modname'] = $module_name;
            $query_url['op'] = $default_op;
        }
        if($to === 'lms') {
            $query_url['sop'] = "unregistercourse";
        }
        if(!$mvc_path) {
            $query_url['of_platform'] = $of_platform;
        }
        $query_url = urldecode(http_build_query($query_url, '', '&'));
        if($query_url) $url .= "index.php?$query_url";

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
SQL;

        $menu = sql_fetch_object(sql_query($query));
        $menu->role = self::role($menu->of_platform, $menu->module_name, $menu->associated_token);
        $menu->url  = self::url($menu->of_platform, $menu->mvc_path, $menu->module_name, $menu->default_op);
        return $menu;
    }

    public static function getByMVC($mvc_path) {

        $query =
<<<SQL
SELECT  m.idMenu, m.idParent, m.sequence, m.name, m.image, m.is_active, mu.idUnder
      , mu.module_name, mu.default_op, mu.mvc_path, mu.associated_token, mu.of_platform
FROM %adm_menu AS m
    LEFT JOIN %adm_menu_under AS mu ON (m.idMenu = mu.idMenu)
WHERE 1 = 1
    AND mu.mvc_path = '$mvc_path'
SQL;

        $menu = sql_fetch_object(sql_query($query));
        $menu->role = self::role($menu->of_platform, $menu->module_name, $menu->associated_token);
        $menu->url  = self::url($menu->of_platform, $menu->mvc_path, $menu->module_name, $menu->default_op);
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

    /**
     * Add new menu item and create the required role.
     * 
     * @param array $menu
     *    string $name
     *    string|null $image
     *    int|null $sequence
     *    bool|null $isActive
     *    bool|null $collapse
     *    int|null $idParent
     *    string|null $ofPlatform
     * @param array|null $menuUnder
     *    string $defaultName
     *    string $moduleName
     *    string $associatedToken
     *    string|null $defaultOp
     *    string|null $ofPlatform
     *    int|null $sequence
     *    string|null $classFile
     *    string|null $className
     *    string|null $mvcPath
     * @param array $roleMembers
     * @param int|null $idPlugin
     * @return int|false
     */
    public static function addMenu($menu, $menuUnder = null, $roleMembers = array(), $idPlugin = null) {

        $values = array();
        $values['name'] = "'{$menu['name']}'";
        $values['image'] = isset($menu['image']) ? "'{$menu['image']}'" : "''";
        if(isset($menu['isActive'])) $values['is_active'] = $menu['isActive'] ? "'true'" : "'false'";
        if(isset($menu['collapse'])) $values['collapse'] = $menu['collapse'] ? "'true'" : "'false'";
        if(isset($menu['idParent'])) $values['idParent'] = $menu['idParent'];
        if(isset($menu['ofPlatform'])) $values['of_platform'] = "'{$menu['ofPlatform']}'";
        if(!is_null($idPlugin)) $values['idPlugin'] = $idPlugin;
        if(isset($menu['sequence'])) {
            $values['sequence'] = $menu['sequence'];
        } else {
            $of_platform = $values['of_platform'];
            $querySequence = "SELECT  max(sequence) max_sequence, of_platform FROM %adm_menu WHERE  of_platform = $of_platform GROUP BY of_platform";
            if(! ($resSequence = sql_query($querySequence))) {
                return false;
            } else {
                $rowSequence = sql_fetch_assoc($resSequence);
                if($rowSequence['max_sequence'] >= 100) {
                    $values['sequence'] = $rowSequence['max_sequence'] + 1;
                } else {
                    $values['sequence'] = 100;
                }
            }
        }

        $query = "INSERT INTO %adm_menu (" . implode(', ', array_keys($values)) . ") VALUE (" . implode(', ', array_values($values)) . ")";

        if(!sql_query($query)) {
            return false;
        }

        $id = sql_insert_id();

        if($menuUnder) {
            $values = array();
            $values['idMenu'] = $id;
            $values['default_name'] = "'{$menuUnder['defaultName']}'";
            $values['module_name'] = "'{$menuUnder['moduleName']}'";
            $values['associated_token'] = "'{$menuUnder['associatedToken']}'";
            if(isset($menuUnder['ofPlatform'])) $values['of_platform'] = "'{$menuUnder['ofPlatform']}'";
            if(isset($menuUnder['sequence'])) $values['sequence'] = $menuUnder['sequence'];
            if(isset($menuUnder['classFile'])) $values['class_file'] = "'{$menuUnder['classFile']}'";
            if(isset($menuUnder['className'])) $values['class_name'] = "'{$menuUnder['className']}'";
            if(isset($menuUnder['mvcPath'])) $values['mvc_path'] = "'{$menuUnder['mvcPath']}'";

            $query = "INSERT INTO %adm_menu_under (" . implode(', ', array_keys($values)) . ") VALUE (" . implode(', ', array_values($values)) . ")";

            if(!sql_query($query)) {
                self::delete($id);
                return false;
            }

            $role = self::role($menuUnder['ofPlatform'], $menuUnder['moduleName'], $menuUnder['associatedToken']);
            $am = Docebo::user()->getACLManager();
            if(!$am->getRole($role)) {
                $idst = $am->registerRole($role, '', $idPlugin);
                foreach($roleMembers as $roleMember) {
                    $am->addToRole($idst, $roleMember);
                }
            }
        }

        return $id;
    }

    public static function delete($id) {

        $query = "DELETE FROM %adm_menu WHERE idMenu = $id";
        return (bool)sql_query($query);
    }

    /**
     * Add new menu item.
     * 
     * @deprecated
     *
     * @param string $name
     * @param string $mvcPath
     * @param string $of_platform
     * @param string $under_of_platform
     * @param boolean $parent
     * @param string $icon
     * @param boolean $is_active
     * @param int $idPlugin
     * @return void
     */
    public static function addMenuChild($name, $mvcPath, $of_platform, $under_of_platform, $parent=false, $icon='', $is_active=true, $idPlugin=null){

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
                of_platform,
                is_active,
                image,
                idPlugin
            )
        VALUES
            (
                $idParent,
                '$name',
                $sequence,
                '$of_platform',
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
                    '$under_of_platform',
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
