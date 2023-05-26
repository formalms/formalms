<?php

namespace FormaLms\lib\Processors\Access;

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

 abstract class AccessProcessor {


    protected string $includes;

    protected string $className;

    protected string $returnType;

    protected string $returnView;

    protected string $subFolderView;

    protected array $additionalPaths;

    protected bool $useNamespace = false;

    protected $accessModel;

    protected array $params;

    protected $session;

    public const NAME = 'default';

    public const SESSION_KEY = 'selectedUsers';


    public function __construct() {

        //dd('userselector.processors.'.static::NAME);
        $attributes = \Util::config('multiuserselector.processors.'.static::NAME);
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        foreach($attributes as $attributeKey => $attribute) {
            $this->$attributeKey = $attribute;
        }

        $this->injectAccessModel();
    }

    private function injectAccessModel(): self
    {
     
        if ($this->useNamespace()) {
            $className = $this->includes().$this->getClassname();
        } else {
            require_once $this->includes();
            $className = $this->getClassname();
        }

        $this->accessModel = new $className();

        return $this;
    }

    private function getClassName() : string {

        return $this->className;

    }

    private function useNamespace() : bool{

        return $this->useNamespace;
    }

    private function includes() : string {
        return $this->includes;

    }

    protected function response(?string $url = null) : array{
        $response['type'] = $this->getReturnType();

        if($response['type'] == 'redirect') {
            $response['redirect'] = $url;
        } else {
            $response['view'] = $this->getReturnView();
            $response['subFolderview'] = $this->getSubFolderView();
            $response['additionalPaths'] = $this->getAdditionalPaths();
            $response['params'] = $this->getParams();
        }

        return $response;

    }

    private function getReturnType() : string {
        return $this->returnType;

    }

    private function getReturnView() : string {
        return $this->returnView;

    }


    private function getSubFolderView() : string {
        return $this->subFolderView;

    }

    private function getAdditionalPaths() : string {
        return $this->additionalPaths;

    }

    
    public function getSessionData(string $instance): array
    {
        return $this->session->get($instance . '_' . self::SESSION_KEY) ? $this->parseSelection($this->session->get($instance . '_' . self::SESSION_KEY)) : [];
    }


    public function setSessionData(string $instance, array $selection): bool
    {
        $this->session->set($instance . '_' . self::SESSION_KEY, $selection);
        $this->session->save();

        return true;
    }

    public function setSessionMultiParam(array $params, string $prefix = 'tempData'): bool
    {
        $this->session->set($prefix, $params);
        $this->session->save();

        return true;
    }

    public function getSessionMultiParam(string $prefix = 'tempData'): array
    {
        return $this->session->get($prefix);
 
    }

    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
 
    }

    public function getParams(array $params): array
    {
        return $this->params;
 
    }

    abstract public function getAccessList(int $resourceId) : array;

    abstract public function setAccessList(int $resourceId, array $selection) : array;

 }

