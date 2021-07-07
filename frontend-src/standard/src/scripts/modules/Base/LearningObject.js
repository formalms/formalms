
import ModalElement from '../../components/Modal';


class LearningObject {

  constuctor() {
    
  }

  scormLightbox(el, title) {

    const lightbox = new ModalElement('scorm-modal');
    lightbox.Content = `<iframe src="${el.getAttribute('href')}" frameborder="false" width="100%" height="100%" id="overlay_iframe" name="overlay_iframe">`;
    lightbox.Title = title;
    lightbox.OnClose = () => {
      try {
        window.frames['overlay_iframe'].uiPlayer.closePlayer(true, window);
      } catch (e) {
        window.overlay_iframe.uiPlayer.closePlayer(true, window);
      }
    } 
    lightbox.Open();
  }

}

export default LearningObject;