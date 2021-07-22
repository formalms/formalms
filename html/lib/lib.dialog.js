//prepare dialog box for delete button in forms
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

function initDialogForm(e) {
	var params=this; //object with form parameters

	var container=document.createElement('DIV');
	container.id="confirm_container";

	var hd=document.createElement('DIV');
	hd.className='hd';
	hd.innerHTML=params.title;

	var bd=document.createElement('DIV');
	bd.className='bd';

	var bd_text=document.createElement('DIV');
	bd_text.id="confirm_dialog_text";

	var dialogForm=document.createElement('FORM');
	dialogForm.id='dialog_form_new';
	dialogForm.action=params.dialogFormAction;
	dialogForm.method='post';

	container.appendChild(hd);
	dialogForm.appendChild(bd_text);
	bd.appendChild(dialogForm);
	container.appendChild(bd);
	document.body.appendChild(container);

	var clickFunction=function(e) {
		YAHOO.util.Event.stopEvent(e);
		dialogBox.elDelete=this;
		var text;
		try { text=params.composeBody(this); } catch(e) { text=params.composeBody.toString(); }
		YAHOO.util.Dom.get("confirm_dialog_text").innerHTML=text;
		//dialogBox.setBody(text);
		dialogBox.show();
	};

	//utility functions
	var extractIdFromName=function(o) {
		//input ids are in the form "name_index", we need index
		return o.id.replace(params.idFilter, '');
	};

	var createInput=function(form, name, value) {
		var temp=document.createElement('INPUT');
		temp.type="hidden";
		temp.name=name.toString();
		temp.value=value;
		var auth=document.createElement('INPUT');
		auth.type="hidden";
		auth.name='authentic_request';
		auth.value=params.authentication;
		form.appendChild(temp);
		form.appendChild(auth);
	};

	//event listener to OK button of dialog
	var okFunction=function(e) {
		dialogBox.hide();
		//prepare some additional input data and append it to the form
		//this is a workaround, since we need post parameters for existent php code
		var form=YAHOO.util.Dom.get(params.formId);
		var id=extractIdFromName(this.elDelete);
//		createInput(dialogForm, this.elDelete.name, '');
		createInput(dialogForm, params.idParamName, id);
		createInput(dialogForm, params.confirmParamName+'['+id+']', '');
		dialogForm.submit();
		//dialogForm==this ??
	};

	//event listener to cancel button of dialog
	var cancelFunction=function(e) {
		dialogBox.hide();
	};

	//'input[name^=filter]'
	//search all target nodes and apply the event listener for popup
	var nodes=YAHOO.util.Selector.query(params.elementsFilter); //('input[id^='+params.filter+']');
	for (var i=0; i<nodes.length; i++) {
		YAHOO.util.Event.addListener(nodes[i], 'click', clickFunction);
	}

	var dialogBox=new YAHOO.widget.Dialog("confirm_container", { //("confirm_popup", {
		width:'400px',
		fixedcenter: true,
		visible: false,
		draggable: true,
		modal: true,
		close: true,
		zindex: 10002,
		constraintoviewport: true,
		buttons: [
					{ text: this.okButton, handler: okFunction, isDefault: true },
					{ text: this.cancelButton, handler: cancelFunction }
		]
	} );
	//dialogBox.setHeader(params.title);
	dialogBox.render();
}


//class for tables with links instad of inputs
function initDialogHref(e) {
	var params=this; //object with form parameters

	var container=document.createElement('DIV');
	container.id="confirm_container";
	document.body.appendChild(container);



	var clickFunction=function(e) {
		YAHOO.util.Event.stopEvent(e);
		dialogBox.elDelete=this;
		dialogBox.setBody(params.composeBody(this));
		dialogBox.show();
	};

	//utility functions
	var extractIdFromName=function(o) {
		//input ids are in the form "name_index", we need index
		return o.id.replace(params.idFilter, '');
	};

	//event listener to OK button of dialog
	var okFunction=function(e) {
		dialogBox.hide();
		window.location.href=this.elDelete.href + "&confirm=1";
	};

	//event listener to cancel button of dialog
	var cancelFunction=function(e) {
		dialogBox.hide();
	};

	//'input[name^=filter]'
	//search all target nodes and apply the event listener for popup
	var nodes=YAHOO.util.Selector.query(params.elementsFilter); //('input[id^='+params.filter+']');
	for (var i=0; i<nodes.length; i++) {
		YAHOO.util.Event.addListener(nodes[i], 'click', clickFunction);
	}

	var dialogBox=new YAHOO.widget.SimpleDialog("confirm_popup", {
		width:'400px',
		fixedcenter: true,
		visible: false,
		draggable: true,
		modal: true,
		close: true,
		zindex: 10002,
		constraintoviewport: true,
		buttons: [
					{ text: this.okButton, handler: okFunction, isDefault: true },
					{ text: this.cancelButton, handler: cancelFunction }
		]
	} );
	dialogBox.setHeader(params.title);
	dialogBox.render("confirm_container");
}


//******************************************************************************

function initDialogFormSimple(e) {
  var params=this; //object with form parameters

	var container=document.createElement('DIV');
	container.id="simple_confirm_container";
	document.body.appendChild(container);

	var clickFunction=function(e) {
		YAHOO.util.Event.stopEvent(e);
		dialogBox.elDelete=this;
		dialogBox.setBody(params.composeBody(this));
		dialogBox.show();
	};

	//event listener to OK button of dialog
	var okFunction=function(e) {
		dialogBox.hide();
		var form=YAHOO.util.Dom.get(params.formId);
		var input=document.createElement('INPUT');
		input.type="hidden";
		input.value=dialogBox.elDelete.value;
		input.name=dialogBox.elDelete.name;
		form.appendChild(input);
		var auth=document.createElement('INPUT');
		auth.type="hidden";
		auth.name='authentic_request_'.form.id;
		auth.value=params.authentication;
		form.appendChild(auth);
		form.submit();
	};

	//event listener to cancel button of dialog
	var cancelFunction=function(e) {
		dialogBox.hide();
	};

	//search all target nodes and apply the event listener for popup
	var nodes=YAHOO.util.Selector.query(params.elementsFilter);
	for (var i=0; i<nodes.length; i++) {
		YAHOO.util.Event.addListener(nodes[i], 'click', clickFunction);
	}

	var dialogBox=new YAHOO.widget.SimpleDialog("simple_confirm_popup", {
		width:'400px',
		fixedcenter: true,
		visible: false,
		draggable: true,
		modal: true,
		close: true,
		zindex: 10002,
		constraintoviewport: true,
		buttons: [
					{ text: this.okButton, handler: okFunction, isDefault: true },
					{ text: this.cancelButton, handler: cancelFunction }
		]
	} );
	dialogBox.setHeader(params.title);
	dialogBox.render("simple_confirm_container");
}