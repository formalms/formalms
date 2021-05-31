import Config from '../config/config';
import FolderTree from '../components/FolderTree';
import FolderView from '../components/FolderView';

class StudentLearningObject {

  constructor(controller) {
    let baseUrl = this.getBaseApiUrl('lo');
    new FolderTree(baseUrl, controller.controller, controller.selector);
    new FolderView(baseUrl, controller.controller, controller.selector, true);
  }

  getBaseApiUrl(controller, action) {
    let url = `${Config.apiUrl}lms/${controller}`;
    if (action) {
      url += `/${action}`;
    }

    return url;
  }

}

export default StudentLearningObject
