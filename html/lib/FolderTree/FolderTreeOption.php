<?php
namespace FormaLms\lib\FolderTree;


class FolderTreeOption { 

  protected string $label;

  protected string $name;

  protected string $value;

  protected bool $selected = false;


    public function getLabel() : string {

        return $this->label;
    }

    public function setLabel($label) : self {

        $this->label = $label;
        return $this;
    }

    public function getName() : string {

        return $this->name;
    }

    public function setName($name) : self {

        $this->name = $name;
        return $this;
    }

    public function getValue() : string {

        return $this->value;
    }

    public function setValue($value) : self {

        $this->value = $value;
        return $this;
    }

    public function getSelected() : bool {

        return $this->selected;
    }

    public function setSelected($selected) : self {

        $this->selected = $selected;
        return $this;
    }

 

}