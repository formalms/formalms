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

namespace FormaLms\lib\plugins;

use InvalidArgumentException;

class PluginService
{

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }


    public function extendTrait(string $trait, $args = [], string $eventPrefix)
    {

        if (!trait_exists($trait)) {
            throw new InvalidArgumentException("Trait $trait not exists");
        }

        $traitObj = 'DynamicTraitClass_' . uniqid();

        eval("
        class $traitObj {
            use \\$trait;
        }
    ");

        $traitClass = new $traitObj();


        foreach (get_class_methods($traitClass) as $method) {
            \Events::listen("$eventPrefix.method.$method", static function ($event) use ($method, $args, $traitClass) {
                $refMethod = new \ReflectionMethod($traitClass, $method);


                $paramNames = [];
                foreach ($refMethod->getParameters() as $param) {
                    $paramNames[] = $param->getName();
                }


                $argsAssoc = is_array($args[0] ?? null) ? $args[0] : [];


                $finalArgs = [];
                foreach ($paramNames as $name) {
                    if (array_key_exists($name, $argsAssoc)) {
                        $finalArgs[] = $argsAssoc[$name];
                    }
                }


                return $refMethod->invokeArgs($traitClass, $finalArgs);
            });
        }
    }
}
