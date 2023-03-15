


class LearningView {


  constuctor() {
    this.LastTap = null;
    this.TapedTwice = false;
  }


  addEvent(className, callback = null, parent = document) {
    parent.addEventListener('click', (event) => {
      if(event.target.classList.contains(className)) {
        if(callback) {
          callback(event, event.target);
        }
      }
    }, false);
  }


  onClickOrTap(elem, callback, justTap = false) {

    if ( !callback || typeof(callback) !== 'function' ) return;

    var isTouch, startX, startY, distX, distY;


    var onTouchStartEvent = function (event) {
        isTouch = true;
        startX = event.changedTouches[0].pageX;
        startY = event.changedTouches[0].pageY;
    };

    var onTouchEndEvent = function (event) {
        distX = event.changedTouches[0].pageX - startX;
        distY = event.changedTouches[0].pageY - startY;
        if ( Math.abs(distX) >= 7 || Math.abs(distY) >= 10 ) return;
        callback(event);

    };

    var onClickEvent = function (event) {
        if ( isTouch ) {
            isTouch = false;
            return;
        }
        callback(event);
    };

    elem.addEventListener('touchstart', onTouchStartEvent, false);
    elem.addEventListener('touchend', onTouchEndEvent, false);
    if(!justTap) {
      elem.addEventListener('click', (e) => {
        onClickEvent(e)
      }, false);
    }
  }

}

export default LearningView;