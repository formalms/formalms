<?php
namespace FormaLms\lib\FolderTree\Extension;

use FormaLms\lib\FolderTree\FolderTreeAction;


class DefaultOrgDataNodeAction extends FolderTreeAction{ 

  const DEFAULT_OPTIONS = [ 
                            [
                              'name' => 'descendants',
                              'value' => "0",
                              "label" => '_NO'
                            ],
                            [
                              'name' => 'descendants',
                              'value' => "1",
                              "label" => '_YES'
                            ],
                            [
                              'name' => 'descendants',
                              'value' => "2",
                              "label" => '_ORG_CHART_INHERIT'
                            ],
                        ];

  public function __construct() {

        $this->setType('radioButton');
        $this->setDefaultOptions();
        
  }

  private function setDefaultOptions() {
      foreach (self::DEFAULT_OPTIONS as $defaultOption) {
          $this->addOption(new OrgDataNodeOption($defaultOption['name'], $defaultOption['value'],$defaultOption['label'],'standard'));
      }
  }

}