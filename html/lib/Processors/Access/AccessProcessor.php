<?php

namespace FormaLms\lib\Processors\Access;

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

 abstract class AccessProcessor {


    protected string $includes;

    protected string $className;

    protected string $returnType;

    protected string $returnView;

    protected string $subFolderView;

    protected array $additionalPaths;

    protected bool $useNamespace = false;

    protected string $redirect = '';

    protected ?string $folder = null;

    protected $accessModel;

    protected array $params = [];

    protected $session;

    public $requestParams;

    public const NAME = 'default';

    public const SESSION_KEY = 'tempData';
    public const USER_KEY = 'selectedUsers';

    public function __construct(array $requestParams = []) {

        //dd('userselector.processors.'.static::NAME);
        $attributes = \Util::config('multiuserselector.processors.'.static::NAME);
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        foreach($attributes as $attributeKey => $attribute) {
            $this->$attributeKey = $attribute;
        }
        $this->requestParams = $requestParams;

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

    protected function response() : array{
        $response['type'] = $this->getReturnType();

        if($response['type'] == 'redirect') {
            $response['redirect'] = $this->getRedirect();
            $response['folder'] = $this->getFolder() ?? false;

        } else {
            $response['view'] = $this->getReturnView();
            $response['subFolderView'] = $this->getSubFolderView();
            $response['additionalPaths'] = $this->getAdditionalPaths();
            $response['params'] = $this->getParams();
        }

        return $response;
    }

    private function getReturnType() : string {
        return $this->returnType;
    }

    public function setReturnView(string $view) : self {
        $this->returnView = $view;
        return $this;
    }

    private function getReturnView() : string {
        return $this->returnView;
    }

    private function getRedirect() : string {
        return $this->redirect;
    }

    public function setRedirect(string $redirect) : self {
        $this->redirect = $redirect;
        return $this;
    }

    public function setFolder(string $folder) : self {
        $this->folder = $folder;
        return $this;
    }

    public function getFolder() : ?string {
        return $this->folder;
    }

    public function setReturnType(string $returnType) : self {
        $this->returnType = $returnType;
        return $this;
    }

    public function setSubFolderView(string $subFolderView) : self {
        $this->subFolderView = $subFolderView;
        return $this;
    }

    private function getSubFolderView() : string {
        return $this->subFolderView;
    }

    public function setAdditionalPaths(array $additionalPaths) : self {
        $this->additionalPaths = $additionalPaths;
        return $this;
    }

    private function getAdditionalPaths() : array {
        return $this->additionalPaths;
    }

    public function getSessionData(string $instance, bool $users = false): array
    {
        $sessionData = $this->session->get($instance . '_' . self::SESSION_KEY) ?? [];
       
        if($users) {
            $sessionData = array_key_exists(self::USER_KEY, $sessionData) ? $sessionData[self::USER_KEY] : [];
        }
        
        return $sessionData;
    }


    public function setSessionData(string $instance, array $params): bool
    {
        $this->session->set($instance . '_' . self::SESSION_KEY, $params);
        $this->session->save();

        return true;
    }


    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
 
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function applyAssociation($resourceId, array $selection) : array {

        $this->setaccessList($resourceId, $selection);

        return $this->response();
    }

    abstract public function getAccessList($resourceId) : array;

    abstract public function setAccessList($resourceId, array $selection) : self;

 }

