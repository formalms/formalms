<?php
namespace FormaLms\lib\FolderTree;


class FolderTreeAction { 

    protected string $type;

    protected array $options = [];


    public function getType() : string {

        return $this->type;
    }

    public function setType($type) : self {

        $this->type = $type;
        return $this;
    }

    public function getOptions() : array {

        return $this->options;
    }

    public function setOptions($options) : self {

        $this->options = $options;
        return $this;
    }

    public function addOption($option) : self {

        $this->options[] = $option;
        return $this;
    }

}