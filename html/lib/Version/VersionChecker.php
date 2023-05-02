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


class VersionChecker
{
    public const FILE_VERSION = '4.0.0';
    public const TEMPLATE_MIN_VERSION = '4.0.0';
    public const PHP_MIN_VERSION = '7.4.0';
    public const PHP_MAX_VERSION = '8.1.99';
    public const DB_VERSION = '4.0.0';
    public const MATURITY = 'LTS';
    public const MIN_SUPPORTED_VERSION = '3.3.3';
    public const MIN_UPGRADE_VERSION = '4.0.0';

    public const MIN_MYSQL_VERSION = '5.7';
    public const MAX_MYSQL_VERSION = '8.1';
    public const MIN_MARIADB_VERSION = '10.0';
    public const MAX_MARIADB_VERSION = '11.0';

     /**
     * Method to retrieve file version
     *
     * @return string
     */
    public static function getFileVersion() : string {

        return self::FILE_VERSION;
    }

     /**
     * Method to retrieve maturity
     *
     * @return string
     */
    public static function getMaturity() : string {

        return self::MATURITY;
    }

     /**
     * Method to retrieve DB version
     *
     * @return string
     */
    public static function getDbVersion() : string {

        return self::DB_VERSION;
    }

     /**
     * Method to retrieve complete Froma version
     *
     * @return string
     */
    public static function getCompleteVersion() : string {

        return static::getDbVersion() . ' - ' . static::getMaturity();
    }

     /**
     * Method to retrieve php version
     *
     * @return string
     */
    public static function getPhpVersion() : string {

        return phpversion();
    }

    /**
     * Method to know if php version is matching
     *
     * @return array
     */
    public static function matchPhpVersion() : array {

        $result['match'] = true;

        if (version_compare(PHP_VERSION, self::PHP_MIN_VERSION, '<')) {
            $result['message'] = 'err';
            $result['match'] = false;
        } elseif (version_compare(PHP_VERSION, self::PHP_MAX_VERSION, '>')) {
            $result['message'] = 'warn';
        } else {
            $result['message'] = 'ok';
        }

        return $result;
    }

    /**
     * Method to get the sql client version in array
     *
     * @return string
     */
    public static function getSqlClientVersionArray() : array {
        $sqlClientVersion = [];
        preg_match('/([0-9]+\.[\.0-9]+)/', sql_get_client_info(), $sqlClientVersion);

        return $sqlClientVersion;
    }

    /**
     * Method to get the sql client version 
     *
     * @return string
     */
    public static function getSqlClientVersion() : string {

        $sqlClientVersion = static::getSqlClientVersionArray();
        $version = empty($sqlClientVersion[1]) ? 'unknown' : $sqlClientVersion[1];

        return $version;
    }

     /**
     * Method to get the sql server version in array
     *
     * @param ?string $external sql server version
     *
     * @return array
     */
    public static function getSqlVersionArray(?string $external = null) : array {
        
        $sqlServerVersion = [];
        $check = sql_get_server_info();

        if($external) {
            $check = $external;
        }

        try {
        
            preg_match('/([0-9]+\.[\.0-9]+)/', $check, $sqlServerVersion);
        } catch (\Exception $exception) {
            $sqlServerVersion = [];
        }

        return $sqlServerVersion;

    }

      /**
     * Method to get the sql server version
     *
     * @param ?string $external sql server version
     *
     * @return bool
     */
    public static function getSqlVersion(?string $external = null) : string {

        $sqlClientVersion = static::getSqlVersionArray($external);
        $version = empty($sqlClientVersion[1]) ? 'unknown' : $sqlClientVersion[1];

        return $version;
    }

     /**
     * Method to compare the sql server version
     *
     * @param string $sqlVersion sql server version
     *
     * @return bool
     */
    public static function compareSqlVersion(string $sqlVersion) : bool {

        $result = false;
        $checkMysql = version_compare($sqlVersion, self::MIN_MYSQL_VERSION) >= 0 && version_compare($sqlVersion, self::MAX_MYSQL_VERSION) < 0;
        $checkMariaDB = version_compare($sqlVersion, self::MIN_MARIADB_VERSION) >= 0 && version_compare($sqlVersion, self::MAX_MARIADB_VERSION) < 0;

        if($checkMysql || $checkMariaDB) {
            $result = true;
        }

        return $result;
    }

     /**
     * Method to compare the sql clientversion
     *
     * @param string $sqlClientVersion sql client version
     *
     * @return bool
     */
    public static function compareSqlClientVersion(string $sqlClientVersion) : bool {

        return (bool) (version_compare($sqlClientVersion, PHP_VERSION) >= 0);
   
    }

    /**
     * Method to compare the upgrade version to isntall the migrations table
     *
     * @return bool
     */
    public static function compareUpgradeVersion() : bool {

       
        return (bool) static::getUpgradeSupportedVersion() == 0 && static::getUpgradeFileVersion() <= 0;
   
    }

    /**
     * Method to get the upgrade version comparing it to file version
     *
     * @return int
     */
    public static function getUpgradeFileVersion() : int {

        return version_compare(static::getInstalledVersionArray()['coreVersion'], static::getFileVersion());
   
    }

    /**
     * Method to get the upgrade version comparing it to supported version
     *
     * @return int
     */
    public static function getUpgradeSupportedVersion() : int {

        return version_compare(static::getInstalledVersionArray()['coreVersion'], self::MIN_SUPPORTED_VERSION);
   
    }

    /**
     * Method to compare php version with supported ones
     *
     * @return bool
     */
    public static function comparePhpVersion() : bool {

        return (bool) version_compare(PHP_VERSION, self::PHP_MIN_VERSION, '<') || version_compare(PHP_VERSION, self::PHP_MAX_VERSION, '>');
   
    }

    /**
     * Method to compare template with supported version
     *
     * @return bool
     */
    public static function checkTemplateversion($templateVersion) : bool {

        return (bool) (version_compare(self::MIN_TEMPLATE_VERSION, $templateVersion) <= 0);

    }

      /**
     * Method to compare version installed with the one to install
     *
     * @return int
     */
    public static function compareDbVersions($dbVersion) : int {

        return version_compare(static::getDbVersion(), $dbVersion);

    }

     /**
     * Method to get minimum template version
     *
     * @return string
     */
    public static function getMinimumTemplateVersion() : string {

        return self::TEMPLATE_MIN_VERSION;
    }


     /**
     * Method to get installed version of Forma in array
     *
     * @return string
     */
    public static function getInstalledVersionArray() :array{

        DbConn::getInstance();
        $row = sql_query("SELECT param_value FROM `core_setting` WHERE param_name='core_version'");
        list($version) = sql_fetch_row($row);
      
        $result['coreVersion'] = (string) $version;
        $lastMigration = trim(FormaLms\lib\Database\FormaMigrator::getInstance()->executeCommand('current'));
        $arrayLastMigration = explode('\\',$lastMigration);
      
        $result['subVersion'] = null;
        $result['maturity'] = static::getMaturity();
 
        $versionCompare = version_compare($version, self::MIN_SUPPORTED_VERSION);

        if( count($arrayLastMigration) > 1 && $versionCompare > 0) {
             $nameLastMigration = end($arrayLastMigration);
             $subVersion = static::getVersionFromFilename($nameLastMigration);
             $result['coreVersion'] = static::getFileVersion();
             $result['subVersion'] = $subVersion;
        }

        return $result;
        
    }

      /**
     * Method to get installed version of Forma
     *
     * @return string
     */
    public static function getInstalledVersion() :string{

        $parts = static::getInstalledVersionArray();
       
        if($parts['subVersion']) {
            return implode(' - ', static::getInstalledVersionArray());
        } else {
            return $parts['coreVersion'];
        }
        
    }


    
     /**
     * Method to get file migration version of Forma
     *
     * @return string
     */
    public static function getCurrentVersion() :string{

        //the last line in folder
        $migrationDirectory = array_pop(FormaLms\lib\Database\FormaMigrator::getInstance()->getConfiguration()->getMigrationDirectories());
       
        $migrations = scandir($migrationDirectory, SCANDIR_SORT_DESCENDING);
        $lastMigration = $migrations[0];


        $version = static::getVersionFromFilename($lastMigration);
      
        return static::getFileVersion() . ' - ' . $version . ' - ' .self::getMaturity();
        
    }

     /**
     * Parse sub-versioning from migration file
     *
     * @return string
     */

    public static function getVersionFromFilename(string $filename) :string{

        return substr($filename, 9, 2) .'.'.substr($filename, 11, 2).'.'.substr($filename, 13, 2);
    }

     /**
     * Cehck if system must be updated
     *
     * @return bool
     */
     public static function needsUpgrade() :bool{

        $response = trim(FormaLms\lib\Database\FormaMigrator::getInstance()->executeCommand('uptodate'));

        if(preg_match('/[OK]/', $response )) {
            return false;
        } else {
            return true;
        }

    }

     /**
     * Method to compare version file and db version
     *
     *
     * @return array
     */
    public static function compareVersions()
    {
        $result = [];

        //devo capire se la versione è supportata 
        $compareMinVersion = static::getUpgradeSupportedVersion(); 

     
        if (0 > $compareMinVersion) {
            //not supported
            $result['upgradeTrigger'] = 0;
            $result['upgradeClass'] = 'err';
            $result['upgradeResult'] = _NOT_SUPPORTED_VERSION;
        } else {
            $compareResult = VersionChecker::getUpgradeFileVersion();
          
            if (0 > $compareResult) {
                //ok the upgrade is possible
                $result['upgradeTrigger'] = 1;
                $result['upgradeClass'] = 'ok';
                $result['upgradeResult'] = _OK_UPGRADE;
            } elseif (0 < $compareResult) {
                //installed version is major than detected
                $result['upgradeTrigger'] = 0;
                $result['upgradeClass'] = 'err';
                $result['upgradeResult'] = _NO_DOWNGRADE;
            } else {
                //nothing to do
                $result['upgradeTrigger'] = 0;
                $result['upgradeClass'] = 'none';
                $result['upgradeResult'] = _NO_UPGRADE;
            }
        }

        return $result;
    }

}