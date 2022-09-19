<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @version    $Id: lib.sessionsave.php 323 2006-05-10 16:35:25Z fabio $
 */
require_once __DIR__ . '/lib.generalsave.php';

class Session_Save extends General_Save
{
    /**
     * initial max random value for mt_rand.
     **/
    public $max_ini_rand = 10;
    /**
     * multiplicative factor in case of fail.
     **/
    public $factor = 5;
    /**
     * maximum set var try number.
     **/
    public $max_try = 4;

    /**
     * session.
     **/
    public $session;

    public function __construct()
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function getName($basename = 'basename', $unique = false)
    {
        if ($unique !== false) {
            $this->session->set($basename, '');
            $this->session->save();

            return $basename;
        }
        $value = $basename . '_' . \Symfony\Component\Uid\Uuid::v4()->toRfc4122();

        if ($this->session->has($value)) {
            return $this->getName($basename, $unique);
        }

        return $value;
    }

    public function nameExists($var_name)
    {
        return $this->session->has($var_name);
    }

    public function save($var_name, &$content, $serialize_for_me = true)
    {
        $this->session->set($var_name, $content);
        $this->session->save();

        return true;
    }

    public function &load($var_name, $deserialize_for_me = true)
    {
        if ($this->nameExists($var_name)) {
            return $this->session->get($var_name);
        }

        return false;
    }

    /**
     * function del( $name ).
     *
     * @param string $var_name the name of the variable to delete
     **/
    public function delete($var_name)
    {
        if ($this->nameExists($var_name)) {
            $this->session->remove($var_name);
            $this->session->save();
        }
    }
}
