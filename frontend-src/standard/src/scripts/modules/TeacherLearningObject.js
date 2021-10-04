import FolderTree from '../components/FolderTree';
import FolderView from '../components/FolderView';
import CreateItem from '../components/CreateItem';
import CopyItem from '../components/CopyItem';
import ContextMenu from '../components/ContextMenu';
import LearningObject from './Base/LearningObject';

class TeacherLearningObject extends LearningObject {

    constructor(controllers) {
        super();
        const _this = this; // deprecato, da rimuovere
        document.body.classList.add('teacher-area');
        _this.controllers = controllers;

        _this.controllers.forEach(controller => {
            let baseUrl = this.getBaseApiUrl(controller.controller);
            let folderTreeInstance = new FolderTree(baseUrl, controller.controller, controller.selector);

            _this.folderViewInstance = new FolderView(baseUrl, controller.controller, controller.selector, null, folderTreeInstance);
            new CreateItem(baseUrl, controller.selector);
            // Event on fv-is-scormorg
            if (controller.scormPlayerEnabled) {
                this.folderViewInstance.filterDBClickEvents.push((el) => {
                    if (el.querySelector('.fv-is-scormorg')) {
                        this.scormLightbox(el.querySelector('.fv-is-play'), el.querySelector('.folderView__label').innerHTML, controller.selector);
                        return false;
                    } else {
                        return true;
                    }
                });

                // Event on fv-is-play
                this.folderViewInstance.addEvent('fv-is-play', (e, el) => {
                    if (el.parentNode.parentNode.querySelector('.fv-is-scormorg')) {
                        e.preventDefault();
                        this.scormLightbox(el, el.parentElement.parentElement.querySelector('.folderView__label').innerHTML, controller.selector);
                    }
                }, document.querySelector(`[data-container="${controller.selector}"]`));
            }
            controller.tab.addEventListener('click', _this.clickOnTab.bind(this));
        });

        _this.copyItem = new CopyItem(this.getBaseApiUrl('lomanager'));
        document.addEventListener('refreshContextMenu', (e) => {
            _this.refreshContextMenu(e.detail.controller);
        });
    }

    refreshContextMenu(controller) {
        const _this = this;
        const activeTab = document.querySelector('.tab-content > .active');
        _this.copyItem.setCurrentType(activeTab.getAttribute('data-container'));
        _this.contextMenu = new ContextMenu(this.getBaseApiUrl(controller));
        _this.contextMenu.set('.folderTree__link:not(.ft-is-root), .folderView__li');

        document.addEventListener('contextmenu', (event) => {
            if (event.target.classList.contains('folderTree__rename__input') || event.target.classList.contains('folderTree__rename__btn')) {
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
        window.type = linktype;
        _this.copyItem.setCurrentType(linktype);
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
