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

/**
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------

require_once _adm_ . '/lib/resources/lib.resource_model.php';

class ResourceUser extends ResourceModel
{
    public function ResourceUser($prefix = false, $dbconn = null)
    {
        $this->setResourceCode('user');
        parent::ResourceModel($prefix, $dbconn);
    }

    public function checkAvailability($resource_id, $start_date = false, $end_date = false)
    {
        $res = false;

        $found = $this->getResourceEntries((int) $resource_id, $start_date, $end_date);

        if (count($found) < $this->getAllowedSimultaneously()) {
            $res = true;
        }

        return $res;
    }
}
