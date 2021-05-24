import Config from '../config/config';
import FolderTree from '../components/FolderTree';
import FolderView from '../components/FolderView';

class StudentLearningObject {

  constructor(controller) {
    let baseUrl = this.getBaseApiUrl(controller.controller);
    new FolderTree(baseUrl, controller.controller, controller.selector);
    new FolderView(baseUrl, controller.controller, controller.selector);
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
