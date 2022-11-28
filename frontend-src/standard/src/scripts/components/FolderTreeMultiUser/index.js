import 'regenerator-runtime/runtime'
import Tree from './tree.html.twig';
import FolderTreeBase from './../FolderTreeBase';


class FolderTreeMultiUser extends FolderTreeBase {

  constructor(baseApiUrl, controller, type, endpoint, options = {}) {
    super(baseApiUrl, controller, type, endpoint, options);

    /**
     * Associated Twig view
     */

    this.baseApiUrl = this.getBaseApiUrl('adm/userselector/getData&dataType=org');

    this.Tree = Tree;
  }

  async getTree() {
    await this.getData(this.baseApiUrl, false);
    return this.render();
  }

  static create(controller = {}, baseApiUrl = '') {
    const type = controller.type ? controller.type : 'plugin';
    return new FolderTreeMultiUser(baseApiUrl, controller, type, 'getfoldertree', {
      dragAndDrop: false,
      sortable: false,
      activeStatus: false
    });
  }

  getBaseApiUrl(action) {
    let url = `https://forma.local/appCore/ajax.adm_server.php?r=${action}`;
    return url; 
  }

}

export default FolderTreeMultiUser
