


class LearningView {

  constuctor() {

  }


  addEvent(className, callback = null, parent = document) {
    parent.addEventListener('click', (event) => {
      console.log('CLICK');
      if(event.target.classList.contains(className)) {
        if(callback) {
          callback(event, event.target);
        }
      }
    }, false);
  }

}

export default LearningView;