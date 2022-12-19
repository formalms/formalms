import 'regenerator-runtime/runtime'
import Tree from './tree.html.twig';
import FolderTreeBase from './../FolderTreeBase';


class FolderTreeMultiUser extends FolderTreeBase {

  constructor(baseApiUrl, controller, type, endpoint, options = {}) {
    super(baseApiUrl, controller, type, endpoint, options);

    /**
     * Associated Twig view
     */
    this.Tree = Tree;
  }

  static create(controller = {}, baseApiUrl = '') {
    const type = controller.type ? controller.type : 'plugin';
    return new FolderTreeMultiUser(baseApiUrl, controller, type, 'getfoldertree', {
      dragAndDrop: false,
      sortable: false,
      activeStatus: false
    });
  }

}

export default FolderTreeMultiUser
