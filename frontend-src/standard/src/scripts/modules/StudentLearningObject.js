import FolderTree from '../components/FolderTree';
import FolderView from '../components/FolderView';
import LearningObject from './Base/LearningObject';

class StudentLearningObject extends LearningObject {

    constructor(controller) {
        super();
        //const _this = this; deprecato
        document.body.classList.add('student-area');
        let baseUrl = this.getBaseApiUrl('lo');
        let folderTreeInstance = new FolderTree(baseUrl, controller.controller, controller.selector);

        this.folderViewInstance = new FolderView(baseUrl, controller.controller, controller.selector, null, folderTreeInstance,'','','click');

        // Event on fv-is-scormorg
        this.folderViewInstance.filterDBClickEvents.push((el) => {
            if (el.querySelector('.fv-is-scormorg')) {
                let src = el.querySelector('.fv-is-play').getAttribute('href');
                this.scormLightbox(src, el.querySelector('.folderView__label').innerHTML, controller.selector);
                return false;
            } else {
                return true;
            }
        });

        // Event on fv-is-play
        this.folderViewInstance.addEvent('fv-is-play', (e, el) => {
            if (el.parentNode.parentNode.querySelector('.fv-is-scormorg')) {
                e.preventDefault();
                let src = el.getAttribute('href');
                this.scormLightbox(src, el.parentElement.parentElement.querySelector('.folderView__label').innerHTML, controller.selector);
            }
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

export default StudentLearningObject
