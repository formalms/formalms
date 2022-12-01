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
    this.render();
    return this.initEvents();
  }

  initEvents() {
    this.container.addEventListener('click', (e) => {
      console.log(e.target.classList, e);
      if(e.target.classList.contains('arrow')) {
        this.getNode(e.target.getAttribute('data-id'));
      }
    });
    return this;
  }

  static create(controller = {}, baseApiUrl = '') {
    const type = controller.type ? controller.type : 'plugin';
    return new FolderTreeMultiUser(baseApiUrl, controller, type, 'getfoldertree', {
      dragAndDrop: false,
      sortable: false,
      activeStatus: false
    });
  }

  async getNode(id) {
    const endpoint = 'https://forma.local/appCore/ajax.adm_server.php?r=adm/userselector/getData';
    console.log(endpoint);
    await window.frontend.helpers.Axios.get(endpoint + `&node_id=${id}`).then((response) => {
      console.log(response);
    });
  }

  getBaseApiUrl(action) {
    let url = `https://forma.local/appCore/ajax.adm_server.php?r=${action}`;
    return url; 
  }

}

export default FolderTreeMultiUser
