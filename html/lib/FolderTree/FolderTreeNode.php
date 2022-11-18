<?php
namespace FormaLms\lib\FolderTree;


class FolderTreeNode { 

    protected string $id;

    protected string $name;

    protected array $children = [];

    protected ?bool $isPrerequisitesSatisfied = false; 

    protected bool $active = true; 

    protected bool $locked = false; 

    protected array $actions = [];

    protected bool $hasChildren = false;

    public function getId() : string {

        return $this->id;
    }

    public function setId($id) : self {

        $this->id = $id;
    
        return $this;
    }

    public function getName() : string {

        return $this->id;
    }

    public function setName($name) : self {

        $this->name = $name;
        return $this;
    }

    public function getChildren() : string {

        return $this->children;
    }

    public function setChildren($children) : self {

        $this->children = $children;
        return $this;
    }

    public function addChild($child) : self {

        $this->children[] = $child;
        return $this;
    }

    public function getIsPrerequisitesSatisfied() : ?bool {

        return $this->isPrerequisitesSatisfied;
    }

    public function setIsPrerequisitesSatisfied($isPrerequisitesSatisfied) : self {

        $this->isPrerequisitesSatisfied = $isPrerequisitesSatisfied;
        return $this;
    }

    public function getActive() : bool {

        return $this->active;
    }

    public function setActive($active) : self {

        $this->active = $active;
        return $this;
    }

    public function getLocked() : bool {

        return $this->locked;
    }

    public function setLocked($locked) : self {

        $this->locked = $locked;
        return $this;
    }

    public function getActions() : string {

        return $this->actions;
    }

    public function setActions($actions) : self {

        $this->actions = $actions;
        return $this;
    }

    public function addAction(FolderTreeAction $action) : self {

        $this->actions[] = $action;
        return $this;
    }

    public function getHasChildren() : bool {

        return $this->active;
    }

    public function setHasChildren($hasChildren) : self {

        $this->hasChildren = $hasChildren;
        return $this;
    }
}