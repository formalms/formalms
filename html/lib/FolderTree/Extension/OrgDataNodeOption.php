<?php
namespace FormaLms\lib\FolderTree\Extension;

use FormaLms\lib\FolderTree\FolderTreeOption;


class OrgDataNodeOption extends FolderTreeOption{ 

 

    public function __construct($name, $value, $label, $translationModule = false) {

        require_once _base_.'/i18n/lib.lang.php' ;
    
            $this->setName($name);
            $this->setValue($value);
            if($translationModule) {
                $label = \Lang::t($label, $translationModule);
            }
            $this->setLabel($label);
    }


}