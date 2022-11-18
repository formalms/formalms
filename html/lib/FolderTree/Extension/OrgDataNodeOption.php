<?php
namespace FormaLms\lib\FolderTree\Extension;

use FormaLms\lib\FolderTree\FolderTreeAction;


class DefaultOrgDataNodeOption extends FolderTreeOption{ 

 

  public function __construct() {

        $this->setType('radioButton');
        $this->setDefaultOptions();
        
  }

  private function setDefaultOptions() {
      foreach (self::DEFAULT_OPTIONS as $defaultOption) {
          $this->addOption(new FolderTreeOption($defaultOption['name'], $defaultOption['value'],$defaultOption['label']));
      }
  }