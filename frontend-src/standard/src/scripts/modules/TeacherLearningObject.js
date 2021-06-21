import FolderTree from '../components/FolderTree';
import FolderView from '../components/FolderView';
import CreateItem from '../components/CreateItem';
import CopyItem from '../components/CopyItem';
import ContextMenu from '../components/ContextMenu';

class TeacherLearningObject {

  constructor(controllers) {
    const _this = this;
    document.body.classList.add('teacher-area');
    _this.controllers = controllers;

    _this.controllers.forEach(controller => {
      let baseUrl = this.getBaseApiUrl(controller.controller);
      new FolderTree(baseUrl, controller.controller, controller.selector);
      new FolderView(baseUrl, controller.controller, controller.selector);
      new CreateItem(baseUrl, controller.selector);

      controller.tab.addEventListener('click', _this.clickOnTab.bind(this));
    });
    new CopyItem(this.getBaseApiUrl('lomanager'));
    document.addEventListener('refreshContextMenu', (e) => {
      _this.refreshContextMenu(e.detail.controller);
    });
  }

  refreshContextMenu(controller) {
    const _this = this;

    _this.contextMenu = new ContextMenu(this.getBaseApiUrl(controller));
    _this.contextMenu.set('.folderTree__link:not(.ft-is-root), .folderView__li');

    document.addEventListener('contextmenu', (event) => {
      if (event.target.classList.contains('folderTree__rename__input') || event.target.classList.contains('folderTree__rename__btn') ) {
        document.querySelector('.context-menu').classList.remove('menu-visible');
      }
    });
  }

  clickOnTab(event) {
    const _this = this;
    const target = event.target;
    const el = target.closest('.tab-link');
    const linktype = el.getAttribute('data-type');
    const linkcontroller = el.getAttribute('data-controller');
    const tabs = document.querySelectorAll('.tab-link');
    const tab = target.closest('.tab-link');
    const oldTabContainer = document.querySelector('.tab-content > .active');
    const tabContainer = document.querySelector('.tab-content > .tab-pane[data-container=' + linktype + ']');

    _this.currentType = linktype;
    _this.currentController = linkcontroller;

    if (tabs) {
      tabs.forEach((tab) => {
        tab.classList.remove('active');
      });
    }
    if (tab) {
      tab.classList.add('active');
    }

    if (oldTabContainer) {
      oldTabContainer.classList.remove('active');
    }
    if (tabContainer) {
      tabContainer.classList.add('active');
    }
    _this.setCurrentTab();
  }

  setCurrentTab() {
    $.ajax({
      type: 'POST',
      url: this.getBaseApiUrl(this.currentController, 'setCurrentTab'),
    });
  }

  getBaseApiUrl(controller, action) {
    let url = `${window.frontend.config.url.appLms}/index.php?r=lms/${controller}`;
    if (action) {
      url += `/${action}`;
    }

    return url;
  }

}

export default TeacherLearningObject
