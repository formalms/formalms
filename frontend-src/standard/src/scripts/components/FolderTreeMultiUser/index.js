import 'regenerator-runtime/runtime'
import Tree from './tree.html.twig';
import FolderTreeBase from './../FolderTreeBase';

class FolderTreeMultiUser extends FolderTreeBase {

  constructor(baseApiUrl, controller, type, endpoint, options = {}, defaultData = []) {
    super(baseApiUrl, controller, type, endpoint, options, null, false);
    this.baseApiUrl = this.getBaseApiUrl('adm/userselector/getData&dataType=org');
    this.extraData = {formData: defaultData};
    this.Tree = Tree;
  }

  getFormData() {
    return this.extraData.formData;
  }

  async getTree() {
    await this.getData(this.baseApiUrl, false);
    this.render();
    return this.initEvents();
  }

  initEvents() {
    this.container.addEventListener('click', (e) => {
      const id = e.target.getAttribute('data-id');
      // Click on radio
      if(e.target.classList.contains('radiosel')) {
        const value = e.target.value;
        const dataOpt = e.target.getAttribute('data-opt');
        // Remove all selected of this node
        const idsToRemove = dataOpt.split('_');
        this.extraData.formData = this.extraData.formData.filter((id) => idsToRemove.indexOf(id) !== -1 ? false : true);
        // Add the selected
        if(value) {
          if(this.extraData.formData.indexOf(value) === -1) {
            this.extraData.formData.push(value);
          }
        }
        this.render();
      }
      // Click on folders
      if((e.target.classList.contains('actions') || e.target.classList.contains('arrow')) && !this.openFolders.includes(id)) {
        this.getNode(id, () => {
          this.render();
        });
      } else {
        if(this.openFolders.includes(id)) {
          this.openFolders.splice(this.openFolders.indexOf(id), 1);
          this.insertChildren(this.data, id, []);
          // Remove opened children
          for (var i = this.openFolders.length - 1; i >= 0; i--) {
              const openFolderId = this.openFolders[i];
              if(!this.data.find((item) => item.id === openFolderId)) {
                this.openFolders.splice(this.openFolders.indexOf(openFolderId), 1)
              }
          }
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

  static create(controller = {}, baseApiUrl = '', defaultData = []) {
    const type = controller.type ? controller.type : 'plugin';
    return new FolderTreeMultiUser(baseApiUrl, controller, type, 'getfoldertree', {
      dragAndDrop: false,
      sortable: false,
      activeStatus: false
    }, defaultData);
  }

  async getNode(id, cb) {
    const endpoint = `${window.frontend.config.url.appCore}/ajax.adm_server.php?r=adm/userselector/getData`; 
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
