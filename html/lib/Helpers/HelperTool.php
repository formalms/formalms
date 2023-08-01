<?php

namespace FormaLms\lib\Helpers;

use ReflectionClass;
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

class HelperTool
{

    /**
     * Method to turn camel case string to snake case string.
     * 
     * @param string $string String to turn
     * @param string $separator Separator to identify in the string (Default = _)
     * 
     * @return string
     */
    public static function camelCaseToSnake($string, $separator = '_') {
        return strtolower(preg_replace(
            '/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', $separator, $string));
    }

    /**
     * Method to turn snake case string to camel case string.
     * 
     * @param string $string String to turn
     * @param string $separator Separator to identify in the string (Default = _)
     * 
     * @return string
     */
    public static function snakeToCamelCase($string, $separator = '_') {
        return lcfirst(str_replace(' ', '', ucwords(str_replace($separator, ' ', $string))));
    }


    public static function classMapping($obj) {

        $refl = new ReflectionClass($obj);

        // Retrieve the properties and strip the ReflectionProperty objects down
        // to their values, accessing even private members.
        return array_merge(...array_map(function($prop) use ($obj) {
            $prop->setAccessible(true);
            return [$prop->getName() => $prop->getValue($obj)];
        }, $refl->getProperties()));
    }


    
    public static function dropForeignKeyIfExistsQueryBuilder(string $foreignKey, string $table) : string{

        return 'SET @dbname = DATABASE();
        SET @index = "' . $foreignKey . '";
        
        SET @preparedStatement = (SELECT IF(
          (
            SELECT count(*) FROM information_schema.TABLE_CONSTRAINTS 
          WHERE table_schema = @dbname AND CONSTRAINT_TYPE = "FOREIGN KEY"
            AND constraint_name = @index
          ) > 0,
          "ALTER TABLE `' . $table . '`
                DROP FOREIGN KEY `' . $foreignKey . '`",
          "SELECT 1"
            )
        );
        PREPARE dropForeignKeyIfExists FROM @preparedStatement;
        EXECUTE dropForeignKeyIfExists;
        DEALLOCATE PREPARE dropForeignKeyIfExists;';
    }

    public static function dropIndexIfExistsQueryBuilder() : string{

        return HelperTool::dropProcedure('drop_index_if_exists') . '
        CREATE PROCEDURE drop_index_if_exists ( IN theIndexName VARCHAR ( 128 ), IN theTable VARCHAR ( 128 ) ) 
            BEGIN
                DECLARE existing INT;
        SELECT
            COUNT(*) INTO existing 
        FROM
            information_schema.statistics 
        WHERE
            TABLE_SCHEMA = DATABASE () 
            AND table_name = theTable 
            AND index_name = theIndexName;
            
            IF existing > 0 THEN
                
                SET @s = CONCAT( "DROP INDEX `", theIndexName, "` ON `", theTable, "`" );
                PREPARE stmt FROM @s;
                EXECUTE stmt;
            
            END IF;
        
        END;';
    }

    public static function dropProcedure($procedure) : string{

        return 'DROP PROCEDURE
                IF
                EXISTS `'. $procedure .'`;';
    }


}
