import Config from '../config/config';
import FolderTree from '../components/FolderTree';
import FolderView from '../components/FolderView';
import CreateItem from '../components/CreateItem';
import CopyItem from '../components/CopyItem';

class TeacherLearningObject {

  constructor(controllers) {
    this.controllers = controllers;

    this.controllers.forEach(controller => {
      new FolderTree(this.getBaseApiUrl(controller.controller), controller.controller, controller.selector);
      new FolderView(this.getBaseApiUrl(controller.controller), controller.controller, controller.selector);
      new CreateItem(this.getBaseApiUrl(controller.controller), controller.selector);

      controller.tab.addEventListener('click', this.clickOnTab);
    });
    new CopyItem(this.getBaseApiUrl('lomanager'));
  }

  clickOnTab(event) {
    const target = event.target;
    const el = target.closest('.tab-link');
    const linktype = el.getAttribute('data-type');
    const linkcontroller = el.getAttribute('data-controller');
    const tabs = document.querySelectorAll('.tab-link');
    const tab = target.closest('.tab-link');
    const oldTabContainer = document.querySelector('.tab-content > .active');
    const tabContainer = document.querySelector('.tab-content > .tab-pane[data-container=' + linktype + ']');

    this.currentType = linktype;
    this.currentController = linkcontroller;

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
    this.setCurrentTab();
  }

  setCurrentTab() {
    $.ajax({
      type: 'POST',
      url: this.getBaseApiUrl(this.currentController, 'setCurrentTab'),
    });
  }

  getBaseApiUrl(controller, action) {
    let url = `${Config.apiUrl}lms/${controller}`;
    if (action) {
      url += `/${action}`;
    }

    return url;
  }

}

export default TeacherLearningObject
