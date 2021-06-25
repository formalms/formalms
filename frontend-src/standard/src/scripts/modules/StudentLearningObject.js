import FolderTree from '../components/FolderTree';
import FolderView from '../components/FolderView';
import Lightbox from '../components/LightBox';

class StudentLearningObject {

  constructor(controller) {
    const _this = this;
    document.body.classList.add('student-area');
    let baseUrl = this.getBaseApiUrl('lo');
    new FolderTree(baseUrl, controller.controller, controller.selector);
    _this.folderViewInstance = new FolderView(baseUrl, controller.controller, controller.selector);

    _this.folderViewInstance.filterDBClickEvents.push((el) => {
      if(el.querySelector('.fv-is-scormorg')) {
        new Lightbox().open('scorm-modal', function(modal) {
          modal.Title = el.querySelector('.folderView__label').innerHTML;
          modal.InjectIframe(el.querySelector('.fv-is-play').getAttribute('href'), {
            width: '100%',
            height: '100%',
            id: 'overlay_iframe',
            name: 'overlay_iframe'
          });
        }, function() {
          try {
            window.frames['overlay_iframe'].uiPlayer.closePlayer(true, window);
          } catch (e) {
            window.overlay_iframe.uiPlayer.closePlayer(true, window);
          }
        });
        return false;
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
