import ModalElement from '../../components/Modal';


class LearningObject {

    constuctor() {

    }

    scormLightbox(src, title, type, modatTarget = '#lms_main_container') {

        const lightbox = new ModalElement('scorm-modal', modatTarget);
        lightbox.Content = `<iframe src="${src}" frameborder="false" width="100%" height="100%" id="overlay_iframe" name="overlay_iframe">`;
        lightbox.Title = title;
        lightbox.OnClose = () => {
            try {
                if (window.frames['overlay_iframe'].uiPlayer) {
                    window.frames['overlay_iframe'].uiPlayer.scormPlayer.api.forceFinish();
                    window.frames['overlay_iframe'].uiPlayer.closePlayer(true, window);
                }
                else if (window.overlay_iframe.uiPlayer) {
                    window.overlay_iframe.uiPlayer.scormPlayer.api.forceFinish();
                    window.overlay_iframe.uiPlayer.closePlayer(true, window);
                }
            } catch (e) {
                console.log(e);
            }
            const container = document.querySelector('*[data-container=' + type + ']');
            if (container) {
                container.dispatchEvent(new CustomEvent('refreshTree', {
                    detail: {selectedId: window.localStorage.getItem('selectedId')}
                }))
            }
        }
        lightbox.Open();
    }

}

export default LearningObject;