<?php
namespace FormaLms\lib\FolderTree;


class FolderTreeOption { 

    protected string $type;

    protected array $options = [];


    public function getType() : string {

        return $this->type;
    }

    public function setType($type) : self {

        $this->type = $type;
        return $this;
    }

    public function getOptions() : string {

        return $this->options;
    }

    public function setOptions($options) : self {

        $this->options = $options;
        return $this;
    }

}