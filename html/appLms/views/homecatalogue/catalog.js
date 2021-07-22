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
var glob_serverUrl = "ajax.server.php?r=catalog/";
var dialog;

function initialize(undo_name)
{
	dialog = new YAHOO.widget.Dialog('pop_up_container',
				{
					width : "600px",
					//height : "500px",

					fixedcenter : true,

					visible : true,
					dragdrop: true,
					modal: true,
					close: true,
					visible: false,

					constraintoviewport : true

					//buttons : [{ text:undo_name, handler:function(){this.hide();} } ]
				 });
	dialog.render(document.body);
}

function subscriptionCoursePathPopUp(id_path) {

	var course_info = '&id_path=' + id_path;

	YAHOO.util.Connect.asyncRequest("POST", glob_serverUrl + 'subscribeCoursePathInfo&',
									{
										success: function(o)
										{
											var res = YAHOO.lang.JSON.parse(o.responseText);
											if (res.success)
											{
												dialog.setHeader(res.title);
												dialog.setBody(res.body);
												if(res.footer) dialog.setFooter('<div class="align-right">'+res.footer+'</div>');
												else dialog.setFooter('');

												dialog.center();
												dialog.show();
											}
											else
											{

											}
										},
										failure: function()
										{

										}
									}, course_info);
}

function subscriptionPopUp(id_course, id_date, id_edition, selling)
{
	var course_info = '&id_course=' + id_course + '&id_date=' + id_date + '&id_edition=' + id_edition + '&selling=' + selling;

	YAHOO.util.Connect.asyncRequest("POST", glob_serverUrl + 'subscribeInfo&',
									{
										success: function(o)
										{
											var res = YAHOO.lang.JSON.parse(o.responseText);
											if (res.success)
											{
												/*
												var div = YAHOO.util.Dom.get('pop_up_container');

												div.innerHTML =	'<div class="hd">' + res.title + '</div>'
																+'<div class="bd">'
																+ res.body
																+ '</div>';
												*/
												dialog.setHeader(res.title);
												dialog.setBody(res.body);
												if(res.footer) dialog.setFooter('<div class="align-right">'+res.footer+'</div>');
												else dialog.setFooter('');

												dialog.center();
												dialog.show();
											}
											else
											{

											}
										},
										failure: function()
										{

										}
									}, course_info);
}

function subscribeToCoursePath(id_path) {
	var course_info = '&id_path=' + id_path;
	
	var div_course = YAHOO.util.Dom.get('action_'+id_path);

	var div_feedback = YAHOO.util.Dom.get('feedback');
	if(!div_feedback)
	{
		div_feedback = document.createElement('feedback');

		div_feedback.id = 'feedback';
		div_feedback.className = 'container-feedback container-feedback--catalogue';

		document.body.appendChild(div_feedback);
	}
	
	YAHOO.util.Connect.asyncRequest("POST", glob_serverUrl + 'subscribeToCoursePath&',
									{
										success: function(o)
										{
											var res = YAHOO.lang.JSON.parse(o.responseText);
											if (res.success)
											{
												if(res.new_status != '')
													div_course.innerHTML = res.new_status;

												div_feedback.innerHTML = res.message;

												dialog.hide();
											}
											else
											{
												div_feedback.innerHTML = res.message;

												dialog.hide();
											}
										},
										failure: function()
										{

										}
									}, course_info);
}

function subscribeToCourse(id_course, id_date, id_edition, selling)
{
	var course_info = '&id_course=' + id_course + '&id_date=' + id_date + '&id_edition=' + id_edition;
	var div_course = YAHOO.util.Dom.get('action_' + id_course);
	var div_feedback = YAHOO.util.Dom.get('feedback');
	if(!div_feedback)
	{
		div_feedback = document.createElement('feedback');

		div_feedback.id = 'feedback';
		div_feedback.className = 'container-feedback';

		document.body.appendChild(div_feedback);
	}
	if(selling == 0)
	{
		YAHOO.util.Connect.asyncRequest("POST", glob_serverUrl + 'subscribeToCourse&',
										{
											success: function(o)
											{
												var res = YAHOO.lang.JSON.parse(o.responseText);
												if (res.success)
												{
													if(res.new_status != '' && res.new_status_code == 'subscribed')
														div_course.innerHTML = '<a href="index.php?modname=course&op=aula&idCourse='+id_course+'">'+res.new_status+'</a>';
													else if(res.new_status != '')
														div_course.innerHTML = res.new_status;

													div_feedback.innerHTML = res.message;

													dialog.hide();
												}
												else
												{
													div_feedback.innerHTML = res.message;

													dialog.hide();
												}
											},
											failure: function()
											{

											}
										}, course_info);
	}
	else
	{
		YAHOO.util.Connect.asyncRequest("POST", glob_serverUrl + 'addToCart&',
										{
											success: function(o)
											{
												var res = YAHOO.lang.JSON.parse(o.responseText);
												if (res.success)
												{
													if(res.new_status != '')
														div_course.innerHTML = res.new_status;

													//div_feedback.innerHTML = res.message;

													var cart_element = YAHOO.util.Dom.get('cart_element');

													if(cart_element)
														cart_element.innerHTML = res.cart_element;

													dialog.hide();
                                                     setTimeout(function(){ location.reload(); }, 100);
                                                     
													if(res.num_element > 0)
													{
														var cart = YAHOO.util.Dom.get('cart_box');
														cart.style.display = 'inline';
														cart.focus();

														var cart_overlay = new YAHOO.widget.Overlay('cart_overlay', {
															context: ["cart_action", 'tr', 'br', ["beforeShow", "windowResize"]],
															visible: true
														});

														cart_overlay.setHeader('');
														cart_overlay.setBody(res.cart_message);
														cart_overlay.setFooter('');

														cart_overlay.render(document.body);
														cart_overlay.show();

														var cart_overlay_div = YAHOO.util.Dom.get('cart_overlay');
														cart_overlay_div.style.backgroundColor = '#ffffcc';
														cart_overlay_div.style.padding = '6px 12px 6px 12px';
													}
												}
												else
												{
													div_feedback.innerHTML = res.message;

													dialog.hide();
                                                    
												}
                                            
                                            },
											failure: function()
											{

											}
										}, course_info);
	}
}

function courseSelection(id_course, selling)
{
	var course_info = '&id_course=' + id_course + '&selling=' + selling;

	YAHOO.util.Connect.asyncRequest("POST", glob_serverUrl + 'courseSelection&',
									{
										success: function(o)
										{
											var res = YAHOO.lang.JSON.parse(o.responseText);
											if (res.success)
											{
												dialog.setHeader(res.title);
												dialog.setBody(res.body);
												if(res.footer) dialog.setFooter('<div class="align-right">'+res.footer+'</div>');
												else dialog.setFooter('');

												dialog.center();
												dialog.show();
											}
											else
											{

											}
										},
										failure: function()
										{

										}
									}, course_info);
}

function hideDialog()
{
	dialog.hide();
}