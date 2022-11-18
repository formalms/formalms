<?php
namespace FormaLms\lib\FolderTree\Extension;

use FormaLms\lib\FolderTree\FolderTreeNode;
use FormaLms\lib\FolderTree\Extension\DefaultOrgDataNodeAction;


class OrgDataNode extends FolderTreeNode{ 

  public function __construct(string $id, string $name, bool $hasChildren) {

        $this->setId($id);
        $this->setName($name);
        $this->setHasChildren($hasChildren);
        $this->addAction(new DefaultOrgDataNodeAction());

  }

}