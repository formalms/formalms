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

class CoreMenu
{
    // Constants for addMenu $menu array keys
    const MENU_NAME = 'name';
    const MENU_IMAGE = 'image';
    const MENU_IS_ACTIVE = 'isActive';
    const MENU_COLLAPSE = 'collapse';
    const MENU_ID_PARENT = 'idParent';
    const MENU_OF_PLATFORM = 'ofPlatform';
    const MENU_SEQUENCE = 'sequence';
    
    // Constants for addMenu $menuUnder array keys
    const MENU_UNDER_DEFAULT_NAME = 'defaultName';
    const MENU_UNDER_MODULE_NAME = 'moduleName';
    const MENU_UNDER_ASSOCIATED_TOKEN = 'associatedToken';
    const MENU_UNDER_OF_PLATFORM = 'ofPlatform';
    const MENU_UNDER_SEQUENCE = 'sequence';
    const MENU_UNDER_CLASS_FILE = 'classFile';
    const MENU_UNDER_CLASS_NAME = 'className';
    const MENU_UNDER_MVC_PATH = 'mvcPath';

    public static function getList($platform, $only_active = true)
    {
        if (!is_array($platform)) {
            $platform = [$platform];
        }

        $platform = implode(', ', array_map(function ($pl) {
            return "'$pl'";
        }, $platform));

        if ($only_active) {
            $only_active = 'AND m.is_active = true';
        }

        $query =
            <<<SQL
SELECT  m.idMenu, m.idParent, m.sequence, m.name, m.image, m.is_active, mu.idUnder
      , mu.module_name, mu.default_op, mu.mvc_path, mu.associated_token, mu.of_platform, mu.sequence as menu_under_sequence
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

        $menu = [];
        while ($row = sql_fetch_object($res)) {
            $menu[] = $row;
        }

        $menu = self::buildMenuArray($menu);

        return $menu;
    }

    public static function updateSequence($idMenu, $sequence)
    {
        $query = <<<SQL
        UPDATE `%adm_menu` SET `sequence` = $sequence WHERE `idMenu` = $idMenu 
SQL;

        $res = sql_query($query);
    }

    private static function buildMenuArray($menu, $parent = 0)
    {
        $_menu = [];
        foreach ($menu as &$item) {
            if ((int)$item->idParent === $parent) {
                $item->submenu = self::buildMenuArray($menu, (int)$item->idMenu);
                $item->role = self::role($item->of_platform, $item->module_name, $item->associated_token);
                $item->url = self::url($item->of_platform, $item->mvc_path, $item->module_name, $item->default_op);
                $_menu[] = $item;
            }
        }

        return $_menu;
    }

    private static function role($of_platform, $module_name, $associated_token)
    {
        switch ($of_platform) {
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

        if ($module_name) {
            $role .= "/$module_name";
        }

        if ($associated_token) {
            $role .= "/$associated_token";
        }

        return $role;
    }

    private static function url($of_platform, $mvc_path, $module_name, $default_op)
    {
        switch ($of_platform) {
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

        $url = FormaLms\lib\Get::abs_path($to);

        $query_url = [];
        if ($mvc_path) {
            $query_url['r'] = $mvc_path;
        } elseif ($module_name) {
            $query_url['modname'] = $module_name;
            $query_url['op'] = $default_op;
        }
        if ($to === 'lms') {
            $query_url['sop'] = 'unregistercourse';
        }
        if (!$mvc_path) {
            $query_url['of_platform'] = $of_platform;
        }
        $query_url = urldecode(http_build_query($query_url, '', '&'));
        if ($query_url) {
            $url .= "index.php?$query_url";
        }

        return $url;
    }

    public static function get($id)
    {
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
        $menu->url = self::url($menu->of_platform, $menu->mvc_path, $menu->module_name, $menu->default_op);

        return $menu;
    }

    public static function getByMVC($mvc_path)
    {
        $query =
            <<<SQL
SELECT  m.idMenu, m.idParent, m.sequence, m.name, m.image, m.is_active, mu.idUnder
      , mu.module_name, mu.default_op, mu.mvc_path, mu.associated_token, mu.of_platform
FROM %adm_menu AS m
    LEFT JOIN %adm_menu_under AS mu ON (m.idMenu = mu.idMenu)
WHERE 1 = 1
    AND mu.mvc_path = '$mvc_path'
SQL;

        $menu = (object) sql_fetch_object(sql_query($query));
        if ($menu) {
            $menu->role = self::role($menu->of_platform, $menu->module_name, $menu->associated_token);
            $menu->url = self::url($menu->of_platform, $menu->mvc_path, $menu->module_name, $menu->default_op);
        }
        return $menu;
    }

    public static function set($id, $values)
    {
        $sets = [];
        foreach ($values as $field => $value) {
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
     * Add new menu item and create the required role using the plug-in ID reference.
     *
     * @param array $menu Associative array with the following keys:
     *                    - 'name' (string): Menu name
     *                    - 'image' (string|null): Image path (optional)
     *                    - 'sequence' (int|null): Display order (optional)
     *                    - 'isActive' (bool|null): Active/inactive state (optional)
     *                    - 'collapse' (bool|null): Whether the menu is collapsible (optional)
     *                    - 'idParent' (int|null): Parent menu ID (optional)
     *                    - 'ofPlatform' (string|null): Platform reference (optional)
     *
     * @param array|null $menuUnder Associative array with the following keys (optional):
     *                              - 'defaultName' (string): Default name
     *                              - 'moduleName' (string): Module name
     *                              - 'associatedToken' (string): Associated token
     *                              - 'defaultOp' (string|null): Default operation (optional)
     *                              - 'ofPlatform' (string|null): Platform reference (optional)
     *                              - 'sequence' (int|null): Display order (optional)
     *                              - 'classFile' (string|null): Class file path (optional)
     *                              - 'className' (string|null): Class name (optional)
     *                              - 'mvcPath' (string|null): MVC path (optional)
     *
     * @param array $roleMembers Array of role members
     *
     * @return int|false The created menu ID or false on failure
     */
    public static function addMenu($menu, $menuUnder = null, $roleMembers = [], $idPlugin = null)
    {
        $values = [];
        $values['name'] = "'{$menu[self::MENU_NAME]}'";
        $values['image'] = isset($menu[self::MENU_IMAGE]) ? "'{$menu[self::MENU_IMAGE]}'" : "''";
        if (isset($menu[self::MENU_IS_ACTIVE])) {
            $values['is_active'] = $menu[self::MENU_IS_ACTIVE] ? "'true'" : "'false'";
        }
        if (isset($menu[self::MENU_COLLAPSE])) {
            $values['collapse'] = $menu[self::MENU_COLLAPSE] ? "'true'" : "'false'";
        }
        if (isset($menu[self::MENU_ID_PARENT])) {
            $values['idParent'] = $menu[self::MENU_ID_PARENT];
        }
        if (isset($menu[self::MENU_OF_PLATFORM])) {
            $values['of_platform'] = "'{$menu[self::MENU_OF_PLATFORM]}'";
        }
        if (!is_null($idPlugin)) {
            $values['idPlugin'] = $idPlugin;
        }
        if (isset($menu[self::MENU_SEQUENCE])) {
            $values['sequence'] = $menu[self::MENU_SEQUENCE];
        } else {
            $of_platform = $values['of_platform'];
            $querySequence = "SELECT  max(sequence) max_sequence, of_platform FROM %adm_menu WHERE  of_platform = $of_platform GROUP BY of_platform";
            if (!($resSequence = sql_query($querySequence))) {
                return false;
            } else {
                $rowSequence = sql_fetch_assoc($resSequence);
                if ($rowSequence['max_sequence'] >= 100) {
                    $values['sequence'] = $rowSequence['max_sequence'] + 1;
                } else {
                    $values['sequence'] = 100;
                }
            }
        }

        $query = 'INSERT INTO %adm_menu (' . implode(', ', array_keys($values)) . ') VALUE (' . implode(', ', array_values($values)) . ')';

        if (!sql_query($query)) {
            return false;
        }

        $id = sql_insert_id();

        if ($menuUnder) {
            $values = [];
            $values['idMenu'] = $id;
            $values['default_name'] = "'{$menuUnder[self::MENU_UNDER_DEFAULT_NAME]}'";
            $values['module_name'] = "'{$menuUnder[self::MENU_UNDER_MODULE_NAME]}'";
            $values['associated_token'] = "'{$menuUnder[self::MENU_UNDER_ASSOCIATED_TOKEN]}'";
            if (isset($menuUnder[self::MENU_UNDER_OF_PLATFORM])) {
                $values['of_platform'] = "'{$menuUnder[self::MENU_UNDER_OF_PLATFORM]}'";
            }
            if (isset($menuUnder[self::MENU_UNDER_SEQUENCE])) {
                $values['sequence'] = $menuUnder[self::MENU_UNDER_SEQUENCE];
            }
            if (isset($menuUnder[self::MENU_UNDER_CLASS_FILE])) {
                $values['class_file'] = "'{$menuUnder[self::MENU_UNDER_CLASS_FILE]}'";
            }
            if (isset($menuUnder[self::MENU_UNDER_CLASS_NAME])) {
                $values['class_name'] = "'{$menuUnder[self::MENU_UNDER_CLASS_NAME]}'";
            }
            if (isset($menuUnder[self::MENU_UNDER_MVC_PATH])) {
                $values['mvc_path'] = "'{$menuUnder[self::MENU_UNDER_MVC_PATH]}'";
            }

            $query = 'INSERT INTO %adm_menu_under (' . implode(', ', array_keys($values)) . ') VALUE (' . implode(', ', array_values($values)) . ')';

            if (!sql_query($query)) {
                self::delete($id);

                return false;
            }

            $role = self::role($menuUnder[self::MENU_UNDER_OF_PLATFORM], $menuUnder[self::MENU_UNDER_MODULE_NAME], $menuUnder[self::MENU_UNDER_ASSOCIATED_TOKEN]);
            $am = \FormaLms\lib\Forma::getAclManager();;
            $role_info = $am->getRole(false, $role);
            if (empty($role_info)) {
                $idst = $am->registerRole($role, '', $idPlugin);
            } else {
                $idst = $role_info[0];
            }
            foreach ($roleMembers as $roleMember) {
                $am->addToRole($idst, $roleMember);
            }
        }

        return $id;
    }

    public static function delete($id)
    {
        $query = "DELETE FROM %adm_menu WHERE idMenu = $id";

        return (bool)sql_query($query);
    }

    /**
     * Add new menu item.
     *
     * @param string $name
     * @param string $mvcPath
     * @param string $of_platform
     * @param string $under_of_platform
     * @param bool $parent
     * @param string $icon
     * @param bool $is_active
     * @param int $idPlugin
     *
     * @return void
     * @deprecated will be removed in future 4.2.x
     *
     */
    public static function addMenuChild($name, $mvcPath, $of_platform, $under_of_platform, $parent = false, $icon = '', $is_active = true, $idPlugin = null)
    {
        // Check if $name contains only alphanumeric characters or undescores.
        if (preg_match('/[^a-z_\-0-9]/i', $name)) {
            return false;
        }

        $idPlugin = (int)$idPlugin;

        $idParent = 'NULL';

        $is_active = ($is_active) ? 'true' : 'false';

        // Get idMenu
        if ($parent) {
            $idParentQuery = " SELECT idMenu FROM core_menu WHERE name = '$parent' ";
            $idParentResult = sql_query($idParentQuery);
            if ($idParentResult) {
                if ($idParentRow = sql_fetch_row($idParentResult)) {
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
        if ($idParent != 'NULL') {
            $where .= "= $idParent ";
        } else {
            $where .= 'IS NULL ';
        }
        $sequence = null;
        $sequenceQuery = " SELECT max(sequence)+1, count(sequence) as count FROM core_menu WHERE $where ";
        $sequenceResult = sql_query($sequenceQuery);
        if ($sequenceResult) {
            if ($sequenceRow = sql_fetch_row($sequenceResult)) {
                if ($sequenceRow[1] > 0) {
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
        if (sql_query($queryMenu)) {
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
            if (sql_query($queryMenuUnder)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
