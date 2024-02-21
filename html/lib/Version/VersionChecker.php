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

namespace FormaLms\lib\Version;

use Exception;
use FormaLms\lib\Helpers\HelperTool;

class VersionChecker
{

    protected $fileVersion;
    protected $maturity;
    protected $dbVersion;
    protected $phpMinVersion;
    protected $phpMaxVersion;
    protected $minMysqlVersion;
    protected $maxMysqlVersion;
    protected $minMariadbVersion;
    protected $maxMariadbVersion;
    protected $minSupportedVersion;
    protected $minUpgradeVersion;

     /**
     * Method to retrieve file version
     *
     * @return string
     */
    public function __construct() {

        $versionConfigs = \Util::config('version');
        foreach($versionConfigs as $configParam => $configValue) {
            $property = HelperTool::snakeToCamelCase($configParam);
            if(!property_exists($this, $property)) {
                throw new Exception('$property not existing in Version Checker');
            }
            $this->$property = $configValue;
        }
        
        
    }


    public static function configExists()
    {
        return $GLOBALS['cfg']['configExists'] ?? false;
    }

    /**
     * Method to retrieve file version
     *
     * @return string
     */
    public static function getFileVersion(): string
    {

        return (new self)->fileVersion;
    }

    /**
     * Method to retrieve maturity
     *
     * @return string
     */
    public static function getMaturity(): string
    {

        return (new self)->maturity;
    }

    /**
     * Method to retrieve DB version
     *
     * @return string
     */
    public static function getDbVersion(): string
    {

        return (new self)->dbVersion;
    }

    /**
     * Method to retrieve complete Froma version
     *
     * @return string
     */
    public static function getCompleteVersion(): string
    {

        return static::getDbVersion() . ' - ' . static::getMaturity();
    }

    /**
     * Method to retrieve php version
     *
     * @return string
     */
    public static function getPhpVersion(): string
    {

        return phpversion();
    }

    /**
     * Method to know if php version is matching
     *
     * @return array
     */
    public static function matchPhpVersion(): array
    {

        $result['match'] = true;
        $istance = new self;
        if (version_compare(PHP_VERSION, $istance->phpMinVersion, '<')) {
            $result['message'] = 'err';
            $result['match'] = false;
        } elseif (version_compare(PHP_VERSION, $istance->phpMaxVersion, '>')) {
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
    public static function getSqlClientVersionArray(): array
    {
        $sqlClientVersion = [];
        preg_match('/([0-9]+\.[\.0-9]+)/', sql_get_client_info(), $sqlClientVersion);

        return $sqlClientVersion;
    }

    /**
     * Method to get the sql client version
     *
     * @return string
     */
    public static function getSqlClientVersion(): string
    {

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
    public static function getSqlVersionArray(?string $external = null): array
    {

        $sqlServerVersion = [];
        $check = strtolower(sql_get_server_info());

        if ($external) {
            $check = strtolower($external);
        }
        try {

            //check if mariadb is involved
            if(preg_match('/mariadb/', $check)) {
                $tmpArray = [];
                preg_match('/([\.0-99]+-mariadb)/', $check, $tmpArray);
                $tmpVersions = explode('-', $tmpArray[0]);
                $sqlServerVersion = array_reverse($tmpVersions);
            } else {
                 preg_match('/([0-9]+\.[\.0-9]+)/', $check, $sqlServerVersion);
            }

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
    public static function getSqlVersion(?string $external = null): string
    {

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
    public static function compareSqlVersion(string $sqlVersion): bool
    {

        $result = false;
        $istance = new self;
        $checkMysql = version_compare($sqlVersion, $istance->minMysqlVersion) >= 0 && version_compare($sqlVersion, $istance->maxMysqlVersion) < 0;
        $checkMariaDB = version_compare($sqlVersion, $istance->minMariadbVersion) >= 0 && version_compare($sqlVersion, $istance->maxMariadbVersion) < 0;

        if ($checkMysql || $checkMariaDB) {
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
    public static function compareSqlClientVersion(string $sqlClientVersion): bool
    {

        return (bool)(version_compare($sqlClientVersion, PHP_VERSION) >= 0);

    }

    /**
     * Method to compare the upgrade version to isntall the migrations table
     *
     * @return bool
     */
    public static function compareUpgradeVersion(): bool
    {

        return (bool)static::getUpgradeSupportedVersion() >= 0 && static::getUpgradeFileVersion() <= 0;

    }

    /**
     * Method to get the upgrade version comparing it to file version
     *
     * @return int
     */
    public static function getUpgradeFileVersion(): int
    {

        return version_compare(static::getInstalledVersionArray()['subVersion'], static::getCurrentVersionArray()['subVersion']);
   
    }

    /**
     * Method to get the upgrade version comparing it to supported version
     *
     * @return int
     */
    public static function getUpgradeSupportedVersion(): int
    {

        return version_compare(static::getInstalledVersionArray()['coreVersion'], (new self)->minSupportedVersion);
   
    }

    /**
     * Method to compare php version with supported ones
     *
     * @return bool
     */
    public static function comparePhpVersion(): bool
    {

        $istance = new self;
        return (bool) version_compare(PHP_VERSION, $istance->phpMinVersion, '<') || version_compare(PHP_VERSION, $istance->phpMaxVersion, '>');
   
    }

    /**
     * Method to compare template with supported version
     *
     * @return bool
     */
    public static function checkTemplateversion($templateVersion): bool
    {

        return (bool) (version_compare((new self)->minTemplateVersion, $templateVersion) <= 0);

    }

    /**
     * Method to compare version installed with the one to install
     *
     * @return int
     */
    public static function compareDbVersions($dbVersion): int
    {

        return version_compare(static::getDbVersion(), $dbVersion);

    }

    /**
     * Method to get minimum template version
     *
     * @return string
     */
    public static function getMinimumTemplateVersion(): string
    {
        $standardDir = _base_ . '/templates/standard';
        $manifestStandard = simplexml_load_string(file_get_contents($standardDir . '/manifest.xml'));
        return $manifestStandard->forma_version;
    }


    /**
     * Method to get installed version of Forma in array
     *
     * @return string
     */
    public static function getInstalledVersionArray() : array{

        \FormaLms\db\DbConn::getInstance();
        $row = sql_query("SELECT param_value FROM `core_setting` WHERE param_name='core_version'");
        [$version] = sql_fetch_row($row);
        $result = [];
      
        $result['coreVersion'] = (string) $version;
        $lastMigration = trim(\FormaLms\lib\Database\FormaMigrator::getInstance()->executeCommand('current'));
        $arrayLastMigration = explode('\\', $lastMigration);

     
        $result['subVersion'] = '';
        $result['maturity'] = static::getMaturity();
 
        $versionCompare = version_compare($version, (new self)->minSupportedVersion);

        if (count($arrayLastMigration) > 1 && $versionCompare > 0) {
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
    public static function getInstalledVersion() : string{

        
        $parts = static::getInstalledVersionArray();

        if($parts['subVersion']) {
            return implode(' - ', $parts);
        } else {
            return $parts['coreVersion'];
        }
    }


    /**
     * Method to get file migration version of Forma
     *
     * @return string
     */
    public static function getCurrentVersion(): string
    {

         
        $parts = static::getCurrentVersionArray();
    
        if($parts['subVersion']) {
            return implode(' - ', $parts);
        } else {
            return $parts['coreVersion'];
        }
        
    }

     /**
     * Method to get file migration version of Forma array
     *
     * @return string
     */
    public static function getCurrentVersionArray() : array{

        $directories = \FormaLms\lib\Database\FormaMigrator::getInstance()->getConfiguration()->getMigrationDirectories();
        //the last line in folder
        $migrationDirectory = array_pop($directories);

        $migrations = scandir($migrationDirectory, SCANDIR_SORT_DESCENDING);
        $lastMigration = $migrations[0];


        $version = static::getVersionFromFilename($lastMigration);
      
        return ['coreVersion' => static::getFileVersion() , 'subVersion' => $version , 'maturity' => self::getMaturity()];
        
    }

    /**
     * Parse sub-versioning from migration file
     *
     * @return string
     */

    public static function getVersionFromFilename(string $filename): string
    {

        return substr($filename, 9, 2) . '.' . substr($filename, 11, 2) . '.' . substr($filename, 13, 2);
    }

    /**
     * Cehck if system must be updated
     *
     * @return bool
     */
    public static function needsUpgrade(): bool
    {

        $response = trim(\FormaLms\lib\Database\FormaMigrator::getInstance()->executeCommand('uptodate'));
  
        if (preg_match("/^\[OK\]/", $response)) {
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
    public static function compareVersions($install = true)
    {

        $result = [];

        if (self::configExists()) {

            //devo capire se la versione è supportata
            $compareMinVersion = static::getUpgradeSupportedVersion();


            if (0 > $compareMinVersion) {
                //not supported
                $result['upgradeTrigger'] = 0;
                $result['upgradeClass'] = 'err';
                if($install) {
                    $result['upgradeResult'] = _NOT_SUPPORTED_VERSION;
                }
                
            } else {
                $compareResult = VersionChecker::getUpgradeFileVersion();

                if (0 > $compareResult) {
                    //ok the upgrade is possible
                    $result['upgradeTrigger'] = 1;
                    $result['upgradeClass'] = 'ok';
                    if($install) {
                        $result['upgradeResult'] = _OK_UPGRADE;
                    }
                } elseif (0 < $compareResult) {
                    //installed version is major than detected
                    $result['upgradeTrigger'] = 0;
                    $result['upgradeClass'] = 'err';
                    if($install) {
                        $result['upgradeResult'] = _NO_DOWNGRADE;
                    }
                } else {
                    //nothing to do
                    $result['upgradeTrigger'] = 0;
                    $result['upgradeClass'] = 'none';
                    if($install) {
                        $result['upgradeResult'] = _NO_UPGRADE;
                    }
                }
            }
        }
        else {
            $result['upgradeTrigger'] = 0;
            $result['upgradeClass'] = 'none';
            if($install) {
                $result['upgradeResult'] = _NO_UPGRADE;
            }
        }

        return $result;
    }

}