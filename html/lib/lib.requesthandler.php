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

class RequestHandler
{
    private $mvc_app;
    private $mvc_name;
    private $task;

    public function __construct($req, $app)
    {
        $req = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $req);
        $r = explode('/', $req);
        if (count($r) == 2) {
            // Only class and method defined in the path requested
            array_unshift($r, $app);
        }
        if (count($r) == 3) {
            $this->mvc_app = $r[0];
            $this->mvc_name = $r[1];
            $this->task = $r[2];

            return true;
        } else {
            return false;
        }
    }

    private function valid()
    {
        if (isset($this->mvc_app) && isset($this->mvc_name) && isset($this->task)) {
            return true;
        } else {
            return false;
        }
    }

    public function run($ajax = false)
    {
        if ($this->valid()) {
            $mvc_app = $this->mvc_app;
            $mvc_name = $this->mvc_name;
            $task = $this->task;
            if (!$controller = PluginManager::get_feature($mvc_app, $mvc_name)) {
                $mvc_class = ucfirst(strtolower($mvc_name)) . ucfirst(strtolower($mvc_app)) . 'Controller';
                $controller = new $mvc_class($mvc_name);
            }
            if (!$ajax) {
                ob_clean();
            }
            $controller->request($task);
            if (!$ajax) {
                $GLOBALS['page']->add(ob_get_contents(), 'content');
                ob_clean();
            } else {
                aout(ob_get_contents());
            }
        } else {
            throw new Exception('Request not valid');
        }
    }
}
