/**
 * Slide right instantiation and action.
 */

 var slideRight;
 var slideRightBt;
 
 $(document).ready(function(){
       var slideRight = new Menu({
       wrapper: '#o-wrapper',
       type: 'slide-right',
       menuOpenerClass: '.c-button',
       maskId: '#c-mask'
       });
       
       var slideRightBtn = document.querySelector('#c-button--slide-right');
       slideRightBtn.addEventListener('click', function(e) {
       e.preventDefault;
       slideRight.open();
       });
     }
 )