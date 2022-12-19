<?php
namespace FormaLms\lib\FolderTree\Extension;

use FormaLms\lib\FolderTree\FolderTreeAction;


class RootOrgDataNodeAction extends FolderTreeAction{ 

  const ROOT_OPTIONS = [ 
                            [
                                'name' => 'descendants',
                                'value' => "0",
                                "label" => '_NO',
                                "module" => 'standard'
                            ],
                            [
                                'name' => 'descendants',
                                'value' => "2",
                                "label" => '_ORG_CHART_INHERIT',
                                "module" => 'organization_chart'
                            ],
                        ];

  public function __construct() {

        $this->setType('radioButton');
        $this->setRootOptions();
        
  }

  private function setRootOptions() {
      foreach (self::ROOT_OPTIONS as $rootOption) {
          $this->addOption(new OrgDataNodeOption($rootOption['name'], $rootOption['value'],$rootOption['label'],$rootOption['module']));
      }
  }

}