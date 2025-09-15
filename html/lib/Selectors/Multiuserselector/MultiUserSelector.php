<?php

namespace FormaLms\lib\Selectors\Multiuserselector;

use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\DataSelector;


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

class MultiUserSelector
{
    protected $dataSelectors = array();
    protected $accessProcessor = null;
    protected $db;

    protected $requestParams = [];

    public const NAMESPACE = 'FormaLms\lib\Selectors\Multiuserselector\DataSelectors\\';

    public const PROCESSOR_NAMESPACE = 'FormaLms\lib\Processors\Access\\';

    public const PROCESSOR_SUFFIX = 'AccessProcessor';

    public const ALL_USER_ACCESS = 1; //è l'idst che corrisponde al nodo master di org di forma che non è selezionabile in alcun modo dallo user selector e solo tramite il checkbox apposito


    public function __construct(array $requestParams)
    {
        $this->db = \FormaLms\db\DbConn::getInstance();
        $this->requestParams = $requestParams;

    }

    public function setDataSelectors(string $dataSelector, string $key): self
    {
        $className = self::NAMESPACE . $dataSelector;
        try {
            $this->dataSelectors[$key] = new $className();
        } catch (\Exception $e) {
            dd($e);
        }

        return $this;
    }

    public function setAccessProcessor(string $type): self
    {

        $className = self::PROCESSOR_NAMESPACE . ucfirst($type) . self::PROCESSOR_SUFFIX;

        try {
            $this->accessProcessor = new $className($this->requestParams);
        } catch (\Exception $e) {
            dd($e);
        }

        return $this;

    }

    public function getDataSelectors(): array
    {
        return $this->dataSelectors;
    }

    public function associate($instanceId, $selection)
    {

        $return = $this->accessProcessor->applyAssociation($instanceId, $selection);

        return $return;
    }


    public function getAccessList($instanceId, $parsing = false)
    {

        $selection = $this->accessProcessor->getAccessList($instanceId);

        if ($parsing) {

            $selection = $this->parseSelection($selection);
        }

        return $selection;
    }


    public function getAccessProcessor(): object
    {
        return $this->accessProcessor;
    }

    public function getSelectedAllValue(): int
    {
        return self::ALL_USER_ACCESS;
    }

    public function retrieveDataSelector($key): ?DataSelector
    {
        return $this->dataSelectors[$key];
    }

    public function parseSelection($selectedIds)
    {
        $selection = [];

        $selectString = count($selectedIds) && $selectedIds[0] != "" ? implode(",", $selectedIds) : 0;

        // Execute separate queries for each entity type to avoid ID overlap issues
        $entityTypes = [
            'user' => 'SELECT idst FROM core_user WHERE idst IN ( ' . $selectString . ' )',
            'role' => 'SELECT idst FROM core_role WHERE idst IN ( ' . $selectString . ' )',
            'org' => 'SELECT idst FROM core_group WHERE idst IN ( ' . $selectString . ' ) AND groupid LIKE \'%/oc%\'',
            'group' => 'SELECT idst FROM core_group WHERE idst IN ( ' . $selectString . ' ) AND groupid NOT LIKE \'%/oc%\''
        ];

        // Initialize all entity types
        $selection = [
            'user' => [],
            'role' => [],
            'org' => [],
            'group' => []
        ];

        // Execute each query separately to properly handle overlapping IDs
        foreach ($entityTypes as $entityType => $query) {
            $results = $this->db->query($query) ?? [];
            $ids = [];

            if ($results) {
                foreach ($results as $result) {
                    $ids[] = $result['idst'];
                }
            }

            $selection[$entityType] = $ids;
        }

        return $selection;
    }

    public function postProcess($params)
    {
        if (PHP_VERSION_ID < 80000) {
            return $this->accessProcessor->postProcess(extract($params));
        } else {
            return $this->accessProcessor->postProcess(...$params);
        }
    }

    public function getInstanceParams(int $instanceId)
    {
        return $this->accessProcessor->getInstanceParams($instanceId);
    }


}
