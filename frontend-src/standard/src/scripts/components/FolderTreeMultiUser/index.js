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
      const id = e.target.getAttribute('data-id');
      if(e.target.classList.contains('actions') && !this.openFolders.includes(id)) {
        this.getNode(id, () => {
          this.render();
        });
      } else {
        if(this.openFolders.includes(id)) {
          this.openFolders.splice(this.openFolders.indexOf(id), 1);
          this.insertChildren(this.data, id, []);
          this.render();
        }
      }
    });
    return this;
  }

  insertChildren(source, targetId, children) {
    (source ? source : this.data).forEach((child) => {
      if(child.id == targetId) {
        child.children = children;
      } else {
        if(child.children.length) {
          return this.insertChildren(child.children, targetId, children);
        }
      }
    })
  }

  static create(controller = {}, baseApiUrl = '') {
    const type = controller.type ? controller.type : 'plugin';
    return new FolderTreeMultiUser(baseApiUrl, controller, type, 'getfoldertree', {
      dragAndDrop: false,
      sortable: false,
      activeStatus: false
    });
  }

  async getNode(id, cb) {
    const endpoint = 'https://forma.local/appCore/ajax.adm_server.php?r=adm/userselector/getData';
    this.openFolders.push(id);
    this.container.querySelector(`.loader_${id}`).classList.remove('hidden');
    await window.frontend.helpers.Axios.get(endpoint + `&node_id=${id}`).then((response) => {
      this.insertChildren(this.data, id, response.data.data);
      if(cb) {
        cb();
      }
    });
  }

  getBaseApiUrl(action) {
    let url = `https://forma.local/appCore/ajax.adm_server.php?r=${action}`;
    return url; 
  }

}

export default FolderTreeMultiUser
