
import ModalElement from '../../components/Modal';


class LearningObject {

  constuctor() {
    
  }

  scormLightbox(el, title, type) {

    const lightbox = new ModalElement('scorm-modal');
    lightbox.Content = `<iframe src="${el.getAttribute('href')}" frameborder="false" width="100%" height="100%" id="overlay_iframe" name="overlay_iframe">`;
    lightbox.Title = title;
    lightbox.OnClose = () => {
      try {
        window.frames['overlay_iframe'].uiPlayer.closePlayer(true, window);
      } catch (e) {
        if(window.overlay_iframe.uiPlayer) { 
          window.overlay_iframe.uiPlayer.closePlayer(true, window);
        }
      }
      if(type) {
        console.log(type);
      }
      const container = document.querySelector('*[data-container=' + type + ']');
      container.dispatchEvent(new CustomEvent('refreshTree', {
        detail: { selectedId: window.localStorage.getItem('selectedId') } 
      }))
    } 
    lightbox.Open();
  }

}

export default LearningObject;