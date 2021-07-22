/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

YAHOO.namespace("Animation");

YAHOO.Animation.BlindIn = function (id, params) {

	var YD = YAHOO.util.Dom;
	var elem = YD.get(id);

	var starting_pos = YD.getStyle(id, 'position');
	var starting_over = YD.getStyle(id, 'overflow');
	// make it invisible
	YD.setStyle(id, 'overflow', 'hidden');
	YD.setStyle(id, 'visibility', 'hidden');
    // put the element out of the layout
	YD.setStyle(id, 'position', 'absolute');

	if(YD.getStyle(id, 'display') == 'none') YD.setStyle(id, 'display', 'block');

	//auto height
 	YD.setStyle(id, 'height', '');

	// get the effective height
	var t_height = elem.offsetHeight;

	t_height -= parseInt(YD.getStyle(id, 'padding-top'));
	t_height -= parseInt(YD.getStyle(id, 'padding-bottom'));

	// close it
 	YD.setStyle(id, 'height', '0');
 	// re-put visible
	YD.setStyle(id, 'visibility', 'visible');
	//re-put in the page layout
	YD.setStyle(id, 'position', starting_pos);
	//do animation
	var myAnim = new YAHOO.util.Anim(id,{
  		height: { to: t_height }
	}, 1, YAHOO.util.Easing.easeOut);
	myAnim.duration = 1;

	myAnim.animate();
}

YAHOO.Animation.BlindOut = function (id) {

	var YD = YAHOO.util.Dom;
	YD.setStyle(id, 'overflow', 'hidden');

	var params = { 'id':id, 'pt':0, 'pb':0,'mt':0,'mb':0};

	params.pt = YD.getStyle(id, 'padding-top');
	params.pb = YD.getStyle(id, 'padding-bottom');
	params.mt = YD.getStyle(id, 'margin-top');
	params.mb = YD.getStyle(id, 'margin-bottom');

	if(YD.getStyle(id, 'display') == 'none') YD.setStyle(id, 'display', 'block');
	var myAnim = new YAHOO.util.Anim(id,{
		height: { to: 0 },
		'padding-top': { to: 0 },
		'padding-bottom': { to: 0 },
		'margin-top': { to: 0 },
		'margin-bottom': { to: 0 }
	}, 1, YAHOO.util.Easing.easeOut);
	myAnim.duration = 1;

	myAnim.onComplete.subscribe(function (type, info, args) {
		YAHOO.util.Dom.setStyle(args.id, 'display', 'none');

		YAHOO.util.Dom.setStyle(id, 'padding-top', params.pt);
		YAHOO.util.Dom.setStyle(id, 'padding-bottom', params.pb);
		YAHOO.util.Dom.setStyle(id, 'margin-top', params.mt);
		YAHOO.util.Dom.setStyle(id, 'margin-bottom', params.mb);

	}, params);
	myAnim.animate();
}

YAHOO.Animation.BlindToggle = function (id) {
	if(YAHOO.util.Dom.getStyle(id, 'display') == 'none') YAHOO.Animation.BlindIn(id);
	else YAHOO.Animation.BlindOut(id);
}

YAHOO.Animation.FadeOut = function (id) {
	var YD = YAHOO.util.Dom;

	if(YD.getStyle(id, 'display') == 'none') YD.setStyle(id, 'display', 'block');
	var myAnim = new YAHOO.util.Anim(id,{
		opacity: { to: 0 }
	}, 1, YAHOO.util.Easing.easeOut);
	myAnim.duration = 1;
	var params = { 'id':id};
	myAnim.onComplete.subscribe(function (type, info, args) {
		YAHOO.util.Dom.setStyle(args.id, 'display', 'none');
	}, params);
	myAnim.animate();
}

YAHOO.Animation.FadeIn = function (id) {
	var YD = YAHOO.util.Dom;

	if(YD.getStyle(id, 'display') == 'none') YD.setStyle(id, 'display', 'block');
	var myAnim = new YAHOO.util.Anim(id,{
		opacity: { to: 1 }
	}, 1, YAHOO.util.Easing.easeOut);
	myAnim.duration = 1;

	myAnim.animate();
}

// alpha version
YAHOO.Animation.SlideIn = function (id) {
	YAHOO.util.Dom.setStyle(id, 'position', 'absolute');
	YAHOO.util.Dom.setStyle(id, 'visibility', 'hidden');
	YAHOO.util.Dom.get(id).innerHTML = '<div style="position:relative; bottom:0; overflow:hidden">'
	+'asd asd asd<br />asd asd asd<br />asd asd asd<br />asd asd asd<br />asd asd asd<br />'
	+'let\'s try it out'
	+'</div>';
	YAHOO.util.Dom.get(id).style.height = '';

	var t_height = YAHOO.util.Dom.get(id).offsetHeight;

	YAHOO.util.Dom.get(id).innerHTML = ''
  		+ '<div style="position:absolute; bottom:0; width: 100%; background: #cdf">'
		+ YAHOO.util.Dom.get(id).innerHTML
	    + '</div>';
	var t_width = parseInt(YAHOO.util.Dom.getStyle(id, 'width'));
	YAHOO.util.Dom.setStyle(id, 'height', '0')
	YAHOO.util.Dom.setStyle(id, 'visibility', 'visible')
	YAHOO.util.Dom.setStyle(id, 'position', 'relative');

	var myAnim = new YAHOO.util.Anim(id,{
  		height: { to: t_height }
	}, 1, YAHOO.util.Easing.easeOut);
	myAnim.duration = 1;
	myAnim.animate();
}