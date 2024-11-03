<?php
namespace FormaLms\lib\FolderTree\Extension;

use FormaLms\lib\FolderTree\FolderTreeNode;
use FormaLms\lib\FolderTree\Extension\DefaultOrgDataNodeAction;


class OrgDataNode extends FolderTreeNode{ 

  public function __construct(string $id, string $name, bool $hasChildren, bool $isRoot = false) {

        $this->setId($id);
        $this->setName($name);
        $this->setHasChildren($hasChildren);
        if($isRoot) {
          $this->addAction(new RootOrgDataNodeAction());
        } else {
          $this->addAction(new DefaultOrgDataNodeAction());
        }
        

  }

}