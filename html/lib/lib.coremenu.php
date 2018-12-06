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
      , mu.module_name, mu.default_op, mu.mvc_path, mu.associated_token
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
    
    private function buildMenuArray($menu, $parent = 0) {

        $_menu = array();
        foreach($menu as &$item) {
            if((int)$item->idParent === $parent) {
                $subMenu = self::buildMenuArray($menu, (int)$item->idMenu);
                if(count($subMenu) || (!is_null($item->idUnder) && checkPerm($item->associated_token, true, $item->module_name, true))) {
                    $item->subMenu = $subMenu;
                    $_menu[$item->sequence] = $item;
                }
            }
        }
        return $_menu;
    }

    public function get($id) {

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

        return sql_fetch_object(sql_query($query));
    }

    public function set($id, $values) {

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
}
