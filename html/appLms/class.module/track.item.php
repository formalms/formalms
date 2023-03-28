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

require_once _lms_ . '/class.module/track.object.php';

class Track_Item extends Track_Object
{
    protected $lobj;



    public function __construct($lobj, $id_user = null, $objectType = 'item')
    {
        $this->lobj = $lobj;
        //search for prev track
        if (is_int($lobj)) {
            $this->idTrack = $lobj;
            $this->objectType = $objectType;
            $environment = false;
        } else {
            $this->idTrack = static::getIdTrack($this->lobj->id_reference, $id_user, $this->lobj->id, true);
            $this->objectType = $this->lobj->obj_type;
            $environment = $this->lobj->environment;
        }

        parent::__construct($this->idTrack, $environment);
        if ($this->idReference == false) {
            $this->createTrack(
                $this->lobj->id_reference,
                $this->idTrack,
                $id_user,
                date('Y-m-d H:i:s'),
                'attempted',
                $this->objectType
            );
        }
    }

    /**
     * Return a idTrack for this object, internal or external.
     *
     * @param <int> $id_reference
     * @param <int> $id_user
     * @param <int> $id_resource
     * @param <bool> $createOnFail create a new entry if not found
     */
    public static function getIdTrack($id_reference, $id_user, $id_resource, $createOnFail = false)
    {
        $db = DbConn::getInstance();

        $query = 'SELECT idTrack '
                . 'FROM %lms_materials_track '
                . 'WHERE idReference = ' . (int) $id_reference . ' '
                . '   AND idUser = ' . (int) $id_user . ' '
                . '   AND idResource = ' . (int) $id_resource . ' ';
        $rs = $db->query($query);

        if ($db->num_rows($rs) > 0) {
            list($idTrack) = $db->fetch_row($rs);

            return [true, $idTrack];
        } elseif ($createOnFail) {
            $query = 'INSERT INTO %lms_materials_track '
                    . '( idResource, idReference, idUser ) VALUES '
                    . '( ' . (int) $id_resource . ', ' . (int) $id_reference . ' , ' . (int) $id_user . ' ) ';
            if (!$db->query($query)) {
                return false;
            }
            $idTrack = $db->insert_id();

            return [false, $idTrack];
        }

        return false;
    }
}
