import FolderTree from '../components/FolderTree';
import FolderView from '../components/FolderView';

class StudentLearningObject {

  constructor(controller) {
    document.body.classList.add('student-area');
    let baseUrl = this.getBaseApiUrl('lo');
    new FolderTree(baseUrl, controller.controller, controller.selector);
    new FolderView(baseUrl, controller.controller, controller.selector, true);
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
