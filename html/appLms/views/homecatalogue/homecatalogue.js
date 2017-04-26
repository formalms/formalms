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
var glob_serverUrl = "./appLms/ajax.server.php?r=homecatalogue/";
var dialog;

function initialize()
{
	dialog = new YAHOO.widget.Dialog('pop_up_container',
				{
					width : "600px",
					fixedcenter : true,
					visible : true,
					dragdrop: true,
					modal: true,
					close: true,
					visible: false,
					constraintoviewport : true
				 });
	dialog.render(document.body);
}

function courseSelection(id_course, selling)
{
	var course_info = '&id_course=' + id_course + '&selling=' + selling;

	YAHOO.util.Connect.asyncRequest("POST", glob_serverUrl + 'courseSelection&',
			{
					success: function(o)
					{
							var res = YAHOO.lang.JSON.parse(o.responseText);
							if (res.success) {
									dialog.setHeader(res.title);
									dialog.setBody(res.body);
									if(res.footer) dialog.setFooter('<div class="align-right">'+res.footer+'</div>');
									else dialog.setFooter('');

									dialog.show();
							}
							else {

							}
					},
					failure: function()	{

					}
			}, course_info);
}

function hideDialog()
{
	dialog.hide();
}