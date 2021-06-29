
import Lightbox from '../../components/LightBox';


class LearningObject {

  constuctor() {
    
  }

  scormLightbox(el, title) {
    new Lightbox().open('scorm-modal', function(modal) {
      modal.Title = title;
      modal.InjectIframe(el.getAttribute('href'), {
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
  }

}

export default LearningObject;