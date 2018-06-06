<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

/**
 * @package 	admin-library
 * @category 	interaction
 * @version 	$Id: lib.form.php 1000 2007-03-23 16:03:43Z fabio $
 * @author		Fabio Pirovano
 */

require_once(_base_.'/lib/lib.editor.php');

class Form {

	/**
	 * public static function getFormHeader( $text )
	 *
	 * @param string $text 	the text that will be displayed as header of the form
	 * @return string with the form header html code
	 */
	public static function getFormHeader( $text ) {
		return '<div class="form_header">'.$text.'</div>'."\n";
	}

	/**
	 * public static function openForm( $id , $action, $css_class, $method, $enctype, $other )
	 *
	 * @param string $id 		the form id
	 * @param string $action 	the action of the form
	 * @param string $css_form 	optional css class for this form, if false default, if blacnk not added class=""
	 * @param string $method 	optional method for this form
	 * @param string $enctype 	optional enctype for this form
	 * @param string $other 	optional code for the form tag
	 * @return string 	with the form opening html code
	 */
	public static function openForm( $id , $action, $css_form = false, $method = false, $enctype = '', $other = '', $css_content = '' ) {

		$editor_extra=getEditorExtra();
		$other.=(!empty($editor_extra) ? " ".$editor_extra : "");

		if ($css_form  === false) $css_form = 'std_form';
		if ($method == false) $method = 'post';
		return '<form '
		.( $css_form == '' ? '' : ' class="'.$css_form.'"' )
		.' id="'.$id.'" method="'.$method.'" action="'.$action.'"'
		.( $enctype != '' ? ' enctype="'.$enctype.'"' : '' )
		.$other.'>'."\n"
		.'<div class="' . $css_content . '">'."\n"

		.'<input type="hidden" id="authentic_request_'.$id.'" name="authentic_request" value="'.Util::getSignature().'" />';
	}

	/**
	 * public static function openElementSpace( $css_class )
	 *
	 * @param string $css_class optional css class for the element container
	 * @return string with the html for open the element container
	 */
	public static function openElementSpace( $css_class = 'form_elem' ) {
		return '<div class="'.$css_class.'">'."\n";
	}


	/**
	 * public static function getTextLabel($label, $css_class = '')
	 *
	 * @param string    $label              the text of the label
	 * @param string    $css_class 		the css of the container element
	 */

	public static function getTextLabel($label, $css_class = 'textLabel') {
		//check if the label
		return '<div><label class="'.$css_class.'">'.$label.'</label></div>';
	}


	/**
	 * public static function getTextBox( $text , $css_line = '')
	 *
	 * @param string 	$text 			the text to display
	 * @param string 	$css_line 		the css of the container element
	 * @param boolean 	$inline 		if true use <span> , else <div>
	 * @return string 	with the html code for the text output
	 */
	public static function getTextBox( $text , $css_line = 'form_line_text', $inline = false ) {

		return '<'.( $inline ? 'span' : 'div' ).' class="'.$css_line.'">'
		.$text.'</'.( $inline ? 'span' : 'div' ).'>'."\n";
	}

	/**
	 * @param string 	$span_text 		the text to display on the left
	 * @param string 	$text 			the text to display on the right
	 * @param string 	$css_line 		the css of the container element
	 * @param string 	$css_f_effect 	the css of the left element
	 * @return string 	with the html code for the text output
	 */
	public static function getLineBox( $span_text, $text , $css_line = 'form_line_l', $css_f_effect = 'label_effect' ) {

		return '<div class="'.$css_line.'">'
		.'<p class="'.$css_f_effect.'">'.$span_text.'</p>'
		.$text
		.'</div>'."\n";
	}

	/**
	 * public static function getHidden( $id, $name, $value, $other_param )
	 *
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $value 		the default value for the hidden field
	 * @param string $other_param 	other element for the tag
	 * @return string 	with the html code for the input type="hidden" element
	 */
	public static function getHidden( $id, $name, $value, $other_param = '' ) {
		return '<input type="hidden" id="'.$id.'" name="'.$name.'" value="'.$value.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />'."\n";
	}

	/**
	 * public static function getTextfield( $css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param )
	 *
	 * @param string $css_text 		the css class for the input element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $value 		the default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $maxlenght 	the max number of characters
	 * @param string $other_param 	other element for the tag
	 * @return string 	with the html code for the input type="text" element
	 */
	public static function getInputTextfield( $css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param = '' ) {

		$search = array('"', '<', '>');
		$replace = array('&quot;', '&lt;', '&gt;');
		$value = str_replace($search, $replace, $value);

		return '<input type="text" '
		."\n\t".'class="form-control '.$css_text.'" '
		."\n\t".'id="'.$id.'" '
		.($name !== false ? "\n\t".'name="'.$name.'" ' : "")
		."\n\t".'value="'.$value.'" '
		."\n\t".'maxlength="'.$maxlenght.'" '
		."\n\t".'alt="'.$alt_name.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />';
	}

	/**
	 * public static function getTextfield( $css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param )
	 *
	 * @param string $css_text 		the css class for the input element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $value 		the default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $maxlenght 	the max number of characters
	 * @param string $other_param 	other element for the tag
	 * @return string 	with the html code for the input type="text" element
	 */
	public static function getSearchInputTextfield( $css_text, $id, $name, $placeholder, $value, $alt_name, $maxlenght, $other_param = '' ) {

		$search = array('"', '<', '>');
		$replace = array('&quot;', '&lt;', '&gt;');
		$value = str_replace($search, $replace, $value);

		return '<input type="text" '
			."\n\t".'class="form-control '.$css_text.'" '
			."\n\t".'id="'.$id.'" '
			.($name !== false ? "\n\t".'name="'.$name.'" ' : "")
			."\n\t".'placeholder="'.$placeholder.'" '
			."\n\t".'value="'.$value.'" '
			."\n\t".'maxlength="'.$maxlenght.'" '
			."\n\t".'alt="'.$alt_name.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />';
	}

	/**
	 * public static function getLineTextfield( $css_line, $css_label, $label_name, $css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param, $other_after, $other_before )
	 *
	 * @param string $css_line 		css for the line
	 * @param string $css_label 	css for the label
	 * @param string $label_name 	text contained into the label
	 * @param string $css_text 		the css class for the input element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $value 		the default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $maxlenght 	the max number of characters
	 * @param string $other_param 	other element for the tag
	 * @param string $other_after 	html code added after the input element
	 * @param string $other_before 	html code added before the label element
	 * @return string with the html code for a line with the input type="text" element
	 */
	public static function getLineTextfield( $css_line, $css_label, $label_name, $css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param, $other_after, $other_before ) {

		return '<div class="'.$css_line.'">'
		.$other_before
		.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label></p>'
		.Form::getInputTextfield( $css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param )
		.$other_after
		.'</div>';
	}

	/**
	 * public static function getTextfield( $label_name, $id, $name, $maxlenght, $value, $other_after, $other_before )
	 *
	 * @param string $label_name 	text contained into the label
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $maxlenght 	the max number of characters
	 * @param string $value 		optional default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $other_after 	optional html code added after the input element
	 * @param string $other_before 	html code added before the label element
	 * @return string with the html code for the input type="text" element
	 */
	public static function getTextfield( $label_name, $id, $name, $maxlenght, $value = '', $alt_name = '', $other_after = '', $other_before = '' ) {

		if( $alt_name == '' ) $alt_name = strip_tags($label_name);
		return Form::getLineTextfield( 'form_line_l', 'floating', $label_name, 'textfield', $id, $name, $value, $alt_name, $maxlenght, '', $other_after, $other_before );
	}

	public static function loadDatefieldScript($date_format = false) {
		if (defined("IS_AJAX")) return; //we can't print scripts in an ajax request
		if (!isset($GLOBALS['jscal_loaded']) || $GLOBALS['jscal_loaded'] == false) {
			YuiLib::load('calendar');
			if ($date_format == false) { $regset = Format::instance(); $date_format = $regset->date_token; }

			$arr_months = array(
					Lang::t('_MONTH_01', 'calendar'),
					Lang::t('_MONTH_02', 'calendar'),
					Lang::t('_MONTH_03', 'calendar'),
					Lang::t('_MONTH_04', 'calendar'),
					Lang::t('_MONTH_05', 'calendar'),
					Lang::t('_MONTH_06', 'calendar'),
					Lang::t('_MONTH_07', 'calendar'),
					Lang::t('_MONTH_08', 'calendar'),
					Lang::t('_MONTH_09', 'calendar'),
					Lang::t('_MONTH_10', 'calendar'),
					Lang::t('_MONTH_11', 'calendar'),
					Lang::t('_MONTH_12', 'calendar')
			);
			$arr_months_short = array(
					Lang::t('_JAN', 'calendar'),
					Lang::t('_FEB', 'calendar'),
					Lang::t('_MAR', 'calendar'),
					Lang::t('_APR', 'calendar'),
					Lang::t('_MAY', 'calendar'),
					Lang::t('_JUN', 'calendar'),
					Lang::t('_JUL', 'calendar'),
					Lang::t('_AUG', 'calendar'),
					Lang::t('_SEP', 'calendar'),
					Lang::t('_OCT', 'calendar'),
					Lang::t('_NOV', 'calendar'),
					Lang::t('_DEC', 'calendar')
			);
			$arr_days = array(
					Lang::t('_SUNDAY', 'calendar'),
					Lang::t('_MONDAY', 'calendar'),
					Lang::t('_TUESDAY', 'calendar'),
					Lang::t('_WEDNESDAY', 'calendar'),
					Lang::t('_THURSDAY', 'calendar'),
					Lang::t('_FRIDAY', 'calendar'),
					Lang::t('_SATURDAY', 'calendar')
			);
			$arr_days_medium = array(
					Lang::t('_SUN', 'calendar'),
					Lang::t('_MON', 'calendar'),
					Lang::t('_TUE', 'calendar'),
					Lang::t('_WED', 'calendar'),
					Lang::t('_THU', 'calendar'),
					Lang::t('_FRI', 'calendar'),
					Lang::t('_SAT', 'calendar')
			);

			$arr_days_short = array();
			$arr_days_1char = array();
			foreach ($arr_days_medium as $day) {
				$arr_days_short[] = substr($day, 0, 2);
				$arr_days_1char[] = substr($day, 0, 1);
			}

			$script = '<script type="text/javascript">
					if (!YAHOO.dateInput) {
						YAHOO.namespace("dateInput");
						YAHOO.dateInput = {
							dateFormat: "'.$date_format.'",
							setCalendar: function(id, startdate, dateformat) {

									var getLocalDate = function(y, m, d) {
										var zfill = function(n, z) { n = n+""; while (n.length<z) n = "0"+n; return n; };
										var output = dateformat ? dateformat : this.dateFormat;
										output = output.replace("%d", zfill(d, 2));
										output = output.replace("%m", zfill(m, 2));
										output = output.replace("%Y", y);
										output = output.replace("%H", "00");
										output = output.replace("%M", "00");
										return output;
									};

									var calendarSelect = function(t, args) {
										var date = args[0][0];
										YAHOO.util.Dom.get(this.id).value = getLocalDate(date[0], date[1], date[2]);
										this.container.hide();
									};

									var insertAfter = function(new_node, ref_node) {
										var $D = YAHOO.util.Dom;
										var next = $D.getNextSibling(ref_node);
										if (next) {
											$D.get(ref_node).parentNode.insertBefore($D.get(new_node), next);
										} else {
											$D.get(ref_node).parentNode.appendChild($D.get(new_node));
										}
									};

									var elSpan = document.createElement("SPAN");
									elSpan.id = "calendar_button_"+id;
									elSpan.className = "yui-button";
									elSpan.innerHTML = \'<span class="first-child docebo_calendar"><button type="button"></button></span>\';

									var elDiv = document.createElement("DIV");
									elDiv.id = "calendar_menu_"+id;
									elDiv.innerHTML = \'<div id="calendar_container_\'+id+\'"></div>\';

									insertAfter(elDiv, id);
									insertAfter(elSpan, id);

									var oCalendarMenu = new YAHOO.widget.Overlay("calendar_menu_"+id, {visible: false});
									var oCalendarButton = new YAHOO.widget.Button("calendar_button_"+id, {
										label: "   ",
										type: "menu",
										menu: oCalendarMenu
									});
									var oCalendar = new YAHOO.widget.Calendar("calendar_"+id, "calendar_container_"+id);
									if (startdate) {
										oCalendar.cfg.setProperty("pagedate", startdate.substr(0, 2)+"/"+startdate.substr(6, 4));
										oCalendar.cfg.setProperty("selected", startdate);
									}
									oCalendar.cfg.setProperty("MONTHS_SHORT", ["'.implode('","', $arr_months_short).'"]);
									oCalendar.cfg.setProperty("MONTHS_LONG", ["'.implode('","', $arr_months).'"]);
									oCalendar.cfg.setProperty("WEEKDAYS_1CHAR", ["'.implode('","', $arr_days_1char).'"]);
									oCalendar.cfg.setProperty("WEEKDAYS_SHORT", ["'.implode('","', $arr_days_short).'"]);
									oCalendar.cfg.setProperty("WEEKDAYS_MEDIUM", ["'.implode('","', $arr_days_medium).'"]);
									oCalendar.cfg.setProperty("WEEKDAYS_LONG", ["'.implode('","', $arr_days).'"]);
									oCalendar.cfg.setProperty("start_weekday", 1);
									var oArgs = {id: id, container: oCalendarMenu};
									oCalendar.selectEvent.subscribe(calendarSelect, oArgs, true);
									oCalendar.render();

							}
						};
					}
				</script>';

			cout($script, 'scripts');
			$GLOBALS['jscal_loaded'] = true;
		}
	}

	public static function getInputDatetimefield( $css_field, $id, $name, $value = '', $date_format = FALSE, $sel_time = FALSE, $alt_name = '', $other_param = '' ) {
	
		$value =($value == '00-00-0000 00:00' ? '' : $value);
	
		if ($date_format == false) {
			$regset = Format::instance(); $date_format = $regset->date_token;
		}
		if ($css_field == false) $css_field = 'textfield';
	
		Form::loadDatefieldScript($date_format);

		$date = "";
		$iso = Format::dateDb($value, 'datetime');
		if ($value != '' && $value != '0000-00-00 00:00:00') {
			$datetime=new DateTime($iso);
			$timestamp = $datetime->format("U");//mktime(0, 0, 0, (int)substr($iso, 5, 2), (int)substr($iso, 8, 2), (int)substr($iso, 0, 4));
			$date = date("m/d/Y h:m", $timestamp);
		}

		$other_after_b = '<span id="calendar_button_'.$id.'" class="yui-button"><span class="first-child docebo_calendar">'
				.'<button type="button"></button></span></span>'
				.'<div id="calendar_menu_'.$id.'"><div id="calendar_container_'.$id.'"></div></div>';

				if (defined("IS_AJAX")) {
					if (!isset($GLOBALS['date_inputs'])) $GLOBALS['date_inputs'] = array();
					$GLOBALS['date_inputs'][] = array($id, $date, $date_format);
				} else {
					$script = '<script type="text/javascript">'
							.'YAHOO.util.Event.onDOMReady(function() {'
									.'	YAHOO.dateInput.setCalendar("'.$id.'", "'.$date.'", "'.$date_format.'");'
											.'});</script>';
											cout($script, 'scripts'); //script in the scripts page section, this ensure to have it after the YAHOO.dateInput declaration
				}
	
				return  Form::getInputTextfield( $css_field, $id, $name, Format::date($iso, 'datetime'), $alt_name, '30', '')/*.$other_after_b*/;
	
	}
	
	public static function getLineDatetimefield( $css_line, $css_label, $label_name, $css_text, $id, $name, $value, $date_format, $alt_name, $other_param, $other_after, $other_before ) {
	
		return '<div class="'.$css_line.'">'
				.$other_before
				.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label></p>'
						.Form::getInputDatetimefield( $css_text, $id, $name, $value, $date_format, false, $alt_name, $other_param )
						.$other_after
						.'</div>';
	}
	
	public static function getDatetimefield( $label_name, $id, $name, $value = '', $date_format = FALSE, $sel_time = FALSE, $alt_name = '', $other_after = '', $other_before = '', $other_param = '') {
		$regset = Format::instance();
		if($date_format == false) $date_format = $regset->date_token;
		if($alt_name == '') $alt_name = strip_tags($label_name);
		return Form::getLineDatetimefield( 'form_line_l', 'floating', $label_name, 'textfield',
				$id, $name, $value, $date_format, $alt_name, $other_param, $other_after, $other_before);
	
	}

	public static function getInputDatefield( $css_field, $id, $name, $value = '', $date_format = FALSE, $sel_time = FALSE, $alt_name = '', $other_param = '' ) {

		$value =($value == '00-00-0000' ? '' : $value);

		if ($date_format == false) {
			$regset = Format::instance();
			$date_format = $regset->date_token;
		}
		
    $_lang = Docebo::user()->getPreference('ui.lang_code'); 
    $date_format = str_replace(['%d', '%m', '%Y', '-'], ['dd', 'mm', 'yyyy', '-'], $date_format);
    $date_picker_other_param = ' data-provide="datepicker" 
    								 data-date-autoclose=true data-date-language="'.$_lang.
    								 '" data-date-format="'.$date_format.'" '.$other_param;

		$iso = Format::dateDb($value, 'date');

		return Form::getInputTextfield( $css_field, $id, $name, Format::date($iso, 'date'), $alt_name, '30', $date_picker_other_param);
	}


	/**
	 * public static function getLineTextfield( $css_line, $css_label, $label_name, $css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param, $other_after, $other_before )
	 *
	 * @param string $css_line 		css for the line
	 * @param string $css_label 	css for the label
	 * @param string $label_name 	text contained into the label
	 * @param string $css_text 		the css class for the input element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $value 		the default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $maxlenght 	the max number of characters
	 * @param string $other_param 	other element for the tag
	 * @param string $other_after 	html code added after the input element
	 * @param string $other_before 	html code added before the label element
	 * @return string with the html code for a line with the input type="text" element
	 */
	public static function getLineDatefield( $css_line, $css_label, $label_name, $css_text, $id, $name, $value, $date_format, $alt_name, $other_param, $other_after, $other_before ) {

		return '<div class="'.$css_line.'">'
		.$other_before
		.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label></p>'
		.Form::getInputDatefield( $css_text, $id, $name, $value, $date_format, false, $alt_name, $other_param )
		.$other_after
		.'</div>';
	}


	/**
	 * public static function getDatefield( $label_name, $id, $name, $value = '', $date_format = FALSE, $sel_time = FALSE, $alt_name = '', $other_after = '', $other_before = '' )
	 *
	 * @param string $label_name 	text contained into the label
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $maxlenght 	the max number of characters
	 * @param string $value 		optional default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $date_format 	optional string with the date format selected
	 * @param bool	 $sel_time 		optional if true will show also the time selector
	 * @param string $alt_name 		optional with the alt value
	 * @param string $other_after 	optional html code added after the input element
	 * @param string $other_before 	optional html code added before the label element
	 *
	 * @return string with the html code for the input type="text" with a calendar
	 */
	public static function getDatefield( $label_name, $id, $name, $value = '', $date_format = FALSE, $sel_time = FALSE, $alt_name = '', $other_after = '', $other_before = '', $other_param = '') {
		$regset = Format::instance();
		if($date_format == false) $date_format = $regset->date_token;
		if($alt_name == '') $alt_name = strip_tags($label_name);
		return Form::getLineDatefield( 'form_line_l', 'floating', $label_name, 'textfield',
				$id, $name, $value, $date_format, $alt_name, $other_param, $other_after, $other_before);

	}

	/**
	 * @param string $css_text 		the css class for the input element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $alt_name 		the alt name for the field
	 * @param string $maxlenght 	the max number of characters
	 * @param string $other_param 	other element for the tag
	 * @return string 	with the html code for the input type="password" element
	 */
	public static function getInputPassword( $css_text, $id, $name, $alt_name, $maxlenght, $other_param, $value = "" ) {
		return '<input type="password" '
		."\n\t".'class="form-control '.$css_text.'" '
		."\n\t".'id="'.$id.'" '
		."\n\t".'name="'.$name.'" '
		."\n\t".'maxlength="'.$maxlenght.'" '
		."\n\t".'value="'.$value.'" '
		."\n\t".'autocomplete="off" '
		."\n\t".'alt="'.$alt_name.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />';
	}

	/**
	 * @param string $css_line 		css for the line
	 * @param string $css_label 	css for the label
	 * @param string $label_name 	text contained into the label
	 * @param string $css_text 		the css class for the input element
	 * @param string $id 			the id of the element
	 * @param string $value 		the default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $maxlenght 	the max number of characters
	 * @param string $other_param 	other element for the tag
	 * @param string $other_after 	html code added after the input element
	 * @param string $other_before 	html code added before the label element
	 * @return string with the html code for a line with the input type="text" element
	 */
	public static function getLinePassword( $css_line, $css_label, $label_name, $css_text, $id, $name, $alt_name, $maxlenght, $other_param, $other_after, $other_before, $value = '' ) {
		return '<div class="'.$css_line.'">'
		.$other_before
		.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label></p>'
		.Form::getInputPassword( $css_text, $id, $name, $alt_name, $maxlenght, $other_param, $value )
		.$other_after
		.'</div>';
	}

	/**
	 * @param string $label_name 	text contained into the label
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $maxlenght 	the max number of characters
	 * @param string $alt_name 		the alt name for the field
	 * @param string $other_after 	optional html code added after the input element
	 * @param string $other_before 	optional html code added before the label element
	 * @return string with the html code for the input type="text" element
	 */
	public static function getPassword( $label_name, $id, $name, $maxlenght, $alt_name = '', $other_after = '', $other_before = '', $value = '' ) {

		if( $alt_name == '' ) $alt_name = strip_tags($label_name);
		return Form::getLinePassword( 'form_line_l', 'floating', $label_name, 'textfield', $id, $name, $alt_name, $maxlenght, '', $other_after, $other_before, $value );
	}

	/**
	 * @param string $css_text 		the css class for the input element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $value 		the default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $other_param 	other element for the tag
	 * @return string 	with the html code for the input type="text" element
	 */
	public static function getInputFilefield( $css_text, $id, $name, $value, $alt_name,  $other_param ) {
		return '<input type="file" '
		."\n\t".'class="'.$css_text.'" '
		."\n\t".'id="'.$id.'" '
		."\n\t".'name="'.$name.'" '
		."\n\t".'value="'.$value.'" '
		."\n\t".'alt="'.$alt_name.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />';
	}

	/**
	 * @param string $css_line 		css for the line
	 * @param string $css_label 	css for the label
	 * @param string $label_name 	text contained into the label
	 * @param string $css_text 		the css class for the input element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $value 		the default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $other_param 	other element for the tag
	 * @param string $other_after 	html code added after the input element
	 * @param string $other_before 	html code added before the label element
	 * @return string with the html code for a line with the input type="text" element
	 */
	public static function getLineFilefield( $css_line, $css_label, $label_name, $css_text, $id, $name, $value, $alt_name, $other_param, $other_after, $other_before, $other_afterbefore = '') {
		$ret = '<div class="'.$css_line.'">'
				.$other_before
				.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label></p>';
		if ($other_afterbefore)
			$ret .= ' '.$other_afterbefore.' <span id="upLLoad">';
		$ret .= Form::getInputFilefield( $css_text, $id, $name, $value, $alt_name, $other_param );
		if ($other_afterbefore)
			$ret .= '</span>';
		$ret .= $other_after
				.'</div>';
		return $ret;
	}

	/**
	 * public static function getFilefield( $label_name, $id, $name, $value = '', $alt_name = '', $other_after = '', $other_before = '' )
	 *
	 * @param string $label_name 	text contained into the label
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $value 		optional default value for the input field
	 * @param string $alt_name 		the alt name for the field
	 * @param string $other_after 	optional html code added after the input element
	 * @param string $other_before 	optional html code added before the label element
	 * @return string with the html code for the input type="text" element
	 */
	public static function getFilefield( $label_name, $id, $name, $value = '', $alt_name = '', $other_after = '', $other_before = '', $other_afterbefore = '', $max_size = null ) {

		if( $alt_name == '' ) $alt_name = strip_tags($label_name);

		$p_size = intval(ini_get('post_max_size'));
        $u_size = intval(ini_get('upload_max_filesize'));
        
        $comparison = array($p_size, $u_size);
        if(!is_null($max_size)) {
            $comparison[] = (int)$max_size;
        }
        $max_kb = min($comparison);
        
		$other_after = ' (Max. '.$max_kb.' Mb) '.$other_after;
		return Form::getLineFilefield( 'form_line_l', 'floating', $label_name, 'fileupload', $id, $name, $value, $alt_name, '', $other_after, $other_before, $other_afterbefore );
	}


	/**
	 * @param string $label_name
	 * @param string $id
	 * @param string $name
	 * @param string $value
	 * @param string $filename
	 * @param string $show_current
	 * @param string $show_del_checkbox
	 * @param string $add_old_info
	 * @param string $old_prefix
	 * @param string $del_arr_name
	 * @param string $alt_name
	 * @param string $other_after
	 * @param string $other_before
	 */
	public static function getExtendedFileField($label_name, $id, $name, $value=FALSE, $filename=FALSE, $show_current=TRUE, $show_del_checkbox=TRUE, $add_old_info=TRUE, $old_prefix=FALSE, $del_arr_name=FALSE, $alt_name = '', $other_after = '', $other_before = '', $max_size = null) {
		$res="";

		$res.='<div class="form_extended_file_field">';


		$res.=Form::getFilefield($label_name, $id, $name, $value, $alt_name, $other_after, $other_before, '', $max_size);

		if ($show_current) {

			if (($value !== FALSE) && (!empty($value))) {
				if ($filename === FALSE) {
					if (substr_count($value, "_") >= 3) {
						$break_apart = explode('_', $value);
						$break_apart[0] = $break_apart[1] = $break_apart[2] = '';
						$filename = substr(implode('_', $break_apart), 3);
					}
					else {
						$filename=$value;
					}
				}

				require_once(_base_.'/lib/lib.mimetype.php');
				$ext=strtolower(end(explode(".", $filename)));
				$img ="<img src=\"".getPathImage('fw').mimeDetect($filename)."\" ";
				$img.="alt=\"".$ext."\" title=\"".$ext."\" />";

				if ($show_del_checkbox) {
					$del_arr_name =($del_arr_name !== FALSE ? $del_arr_name : "file_to_del");
					$check_id=$del_arr_name."_".$id;
					$check_name=$del_arr_name."[".$id."]";
					$checkbox =Form::getInputCheckbox($check_id, $check_name, $value, FALSE, "")." ";
					$checkbox.=Form::getLabel($check_id, Lang::t("_DELETE_FILE", "standard", "framework"), "nofloat");
					$res.=Form::getLineBox($img." ".$filename, $checkbox);
				}
				else {
					$res.=Form::openFormLine();
					$res.= Lang::t("_CURRENT_FILE", "standard", "framework").": ".$img." ".$filename;
					$res.=Form::closeFormLine();
				}
			}
			else {
				$res.=Form::openFormLine();
				$res.= Lang::t("_CURRENT_FILE", "standard", "framework").": ". Lang::t("_NONE", "standard", "framework");
				$res.=Form::closeFormLine();
			}
		}


		if ($add_old_info) {
			$old_prefix =($old_prefix !== FALSE ? $old_prefix : "old");
			$res.="\n";
			$res.=Form::getHidden($old_prefix."_".$id, $old_prefix."_".$name, $value);
		}


		$res.="</div>\n";
		return $res;
	}


	/**
	 * public static function getInputDropdown( $css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param )
	 *
	 * @param string $css_dropdown 	the css class for the select element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param array $all_value 	the possible value of the textfield
	 * @param string $selected 		the element selected
	 * @param string $other_param 	other element for the tag
	 * @param bool $withPlaceholder disable the first element
	 *
	 * @return string with the html code for the select element
	 */
	public static function getInputDropdown( $css_dropdown, $id, $name, $all_value, $selected, $other_param = '', $withPlaceholder = false ) {

		$html_code = '<select class="form-control '.$css_dropdown.'" '
				."\n\t".'id="'.$id.'" '
				."\n\t".'name="'.$name.'"  '.$other_param.'>'."\n";
		$i = 0;
		if( is_array($all_value) ) {
			foreach ($all_value as $key => $value) {

				if ($withPlaceholder && $i == 0){
                    $html_code .= '	<option value="'.$key.'"  disabled'
                        .( (((string)$selected) == "" || (string)$key == (string)$selected) ? ' selected="selected"' : '' )
                        .'>'.$value.'</option>'."\n";
				}
				else {
				$html_code .= '	<option value="'.$key.'"'
						.((string)$key == (string)$selected ? ' selected="selected"' : '' )
						.'>'.$value.'</option>'."\n";
                }
                $i++;
			}
		}
		$html_code .= '</select>';
		return $html_code;
	}

	/**
	 * public static function getLineDropdown( $css_line, $css_label, $label_name, $css_dropdown, $id, $name, $all_value, $selected, $other_param, $other_after, $other_before )
	 *
	 * @param string $css_line 		css for the line
	 * @param string $css_label 	css for the label
	 * @param string $label_name 	text contained into the label
	 * @param string $css_dropdown 	the css class for the select element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $all_value 	all the possible value of the select element
	 * @param string $selected 		the element selected
	 * @param string $other_param 	other element for the tag
	 * @param string $other_after 	html code added after the select element
	 * @param string $other_before 	html code added before the label element
	 *
	 * @return string with the html code for a line with the select element
	 */
	public static function getLineDropdown( $css_line, $css_label, $label_name, $css_dropdown, $id, $name, $all_value, $selected, $other_param, $other_after, $other_before ) {
		return '<div class="'.$css_line.'">'
			.$other_before
			.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label></p>'
			// .'<label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label>'
			.Form::getInputDropdown( $css_dropdown, $id, $name, $all_value, $selected, $other_param )
			.$other_after
			.'</div>';
	}

	/**
	 * public static function getDropdown( $label_name, $id, $name, $all_value , $selected = '', $other_after = '', $other_before = '' )
	 *
	 * @param string $label_name 	text contained into the label
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $all_value 	all the possible value of the select element
	 * @param string $selected 		the element selected
	 * @param string $other_after 	html code added after the select element
	 * @param string $other_before 	html code added before the label element
	 *
	 * @return string with the html code for a line with the select element
	 */
	public static function getDropdown( $label_name, $id, $name, $all_value , $selected = '', $other_after = '', $other_before = '', $other_param = '' ) {

		return Form::getLineDropdown( 'form_line_l', 'floating', $label_name, 'dropdown', $id, $name, $all_value, $selected, $other_param, $other_after, $other_before );
	}

	/**
	 * public static function getInputListbox( $css_listbox, $id, $name, $all_value, $selected, $multiple, $other_param )
	 *
	 * @param string $css_listbox 	the css class for the select element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $all_value 	the possible value of the textfield
	 * @param array  $selected 		the elements selected
	 * @param bool   $multiple		is a multi select listbox
	 * @param string $other_param 	other element for the tag
	 *
	 * @return string with the html code for the select element
	 */
	public static function getInputListbox( $css_listbox, $id, $name, $all_value, $selected, $multiple, $other_param ) {

		$html_code = '<select class="'.$css_listbox.'" '
				."\n\t".'id="'.$id.'" '
				."\n\t".'name="'.$name.'" '
				.(($multiple)?'multiple="multiple" ':'')
				.$other_param.'>'."\n";
		if( is_array($all_value) ) {
			while( list($key, $value) = each($all_value) ) {
				$html_code .= '	<option value="'.$key.'"'
						.(in_array ( $key, $selected) ? ' selected="selected"' : '' )
						.'>'.$value.'</option>'."\n";
			}
		}
		$html_code .= '</select>';
		return $html_code;
	}

	/**
	 * public static function getLineListbox( $css_line, $css_label, $label_name, $css_listbox, $id, $name, $all_value, $selected, $multiple, $other_param, $other_after, $other_before )
	 *
	 * @param string $css_line 		css for the line
	 * @param string $css_label 	css for the label
	 * @param string $label_name 	text contained into the label
	 * @param string $css_listbox 	the css class for the select element
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $all_value 	all the possible value of the select element
	 * @param array  $selected 		the elements selected
	 * @param bool   $multiple		is a multi select listbox
	 * @param string $other_param 	other element for the tag
	 * @param string $other_after 	html code added after the select element
	 * @param string $other_before 	html code added before the label element
	 *
	 * @return string with the html code for a line with the select element
	 */
	public static function getLineListbox( $css_line, $css_label, $label_name, $css_listbox, $id, $name, $all_value, $selected, $multiple, $other_param, $other_after, $other_before ) {
		return '<div class="'.$css_line.'">'
		.$other_before
		.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label></p>'
		.Form::getInputListbox( $css_listbox, $id, $name, $all_value, $selected, $multiple, $other_param )
		.$other_after
		.'</div>';
	}

	/**
	 * public static function getListbox( $label_name, $id, $name, $all_value , $selected = FALSE, $multiple = TRUE, $other_after = '', $other_before = '' )
	 *
	 * @param string $label_name 	text contained into the label
	 * @param string $id 			the id of the element
	 * @param string $name 			the name of the element
	 * @param string $all_value 	all the possible value of the select element
	 * @param array  $selected 		the elements selected
	 * @param bool   $multiple		is a multi select listbox
	 * @param string $other_after 	optional html code added after the input element
	 * @param string $other_before 	optional html code added before the label element
	 *
	 * @return string with the html code for the input type="text" element
	 */
	public static function getListbox( $label_name, $id, $name, $all_value, $selected = FALSE, $multiple = TRUE, $other_after = '', $other_before = '', $other_param = '' ) {
		return Form::getLineListbox('form_line_l',
				'floating',
				$label_name,
				'listbox',
				$id,
				$name,
				$all_value,
				($selected === FALSE)?array():$selected,
				$multiple,
				$other_param,
				$other_after,
				$other_before );
	}

	/**
	 * @param string 	$id 			the id of the element
	 * @param string 	$name 			the name of the element
	 * @param string 	$value 			default value for the input field
	 * @param boolean 	$is_checked 	true if checkbox is checked
	 * @param string 	$other_param 	added in the html code
	 *
	 * @return string with the html code for the input type="checkbox" element
	 */
	public static function getInputCheckbox( $id, $name, $value, $is_checked, $other_param ) {

		return '<input class="check" type="checkbox" id="'.$id.'" name="'.$name.'" value="'.$value.'"'
		.( $is_checked ? ' checked="checked"' : '' )
		.( $other_param != '' ? ' '.$other_param : '' ).' />';
	}

	/**
	 * public static function getLineCheckbox( $css_line, $css_label, $label_name, $id, $name, $value, $is_selected )
	 *
	 * @param string 	$css_line 		the css class for the line
	 * @param string 	$css_label 		the css label for the label
	 * @param string 	$label_name 	text contained into the label
	 * @param string 	$id 			the id of the element
	 * @param string 	$name 			the name of the element
	 * @param string 	$value 			default value for the input field
	 * @param boolean 	$is_checked 	true if checkbox is checked
	 * @param string 	$other_param 	added in the html code
	 *
	 * @return string with the html code for the input type="checkbox" element
	 */
	public static function getLineCheckbox( $css_line, $css_label, $label_name, $id, $name, $value, $is_checked, $other_param, $other_after, $other_before ) {

		return '<div class="'.$css_line.'">'
		.$other_before
		.Form::getInputCheckbox( $id, $name, $value, $is_checked, $other_param )
		.' <label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label>'
		.$other_after
		.'</div>';
	}

	/**
	 * public static function getCheckbox(  $label_name, $id, $name, $is_checked = false, $value = 1 )
	 *
	 * @param string 	$label_name 	text contained into the label
	 * @param string 	$id 			the id of the element
	 * @param string 	$name 			the name of the element
	 * @param string 	$value 			value for the input field
	 * @param boolean 	$is_checked 	optional,if true the checkbox is checked, default is false
	 * @param string 	$other_param 	added in the html code
	 *
	 * @return string with the html code for the input type="checkbox" element
	 */
	public static function getCheckbox( $label_name, $id, $name, $value, $is_checked = false, $other_param = '', $other_after = '', $other_before = '' ) {

		return Form::getLineCheckbox( 'form_line_l', 'label_normal', $label_name, $id, $name, $value, $is_checked, $other_param, $other_after, $other_before );
	}

	/**
	 * @param string 	$label_name 	text contained into the label
	 * @param string 	$id 			the id of the element
	 * @param string 	$name 			the name of the element
	 * @param string 	$value 			value for the input field
	 * @param boolean 	$is_checked
	 *
	 * @return string with the html code for the input type="checkbox" element
	 */
	public static function getCheckboxSet( $group_name, $id, $name, $all_value , $selected = false, $other_after = '', $other_before = '' ) {

		if($selected == false) $selected = array();
		$count = 0;
		$out = '<div class="form_line_l">'
				.$other_before
				.'<p><span class="label_effect">'.$group_name.'</span></p>'
				.'<div class="grouping">';
		foreach( $all_value as  $val_item => $label_item ) {

			$out .= '<p>'.Form::getInputCheckbox( $id.'_'.$val_item,
							$name.'['.$val_item.']',
							1,
							isset($selected[$val_item]),
							'' )
					.' <label class="label_padded" for="'.$id.'_'.$val_item.'">'
					.$label_item.'</label>'
					.'</p>';
			$count++;
		}
		$out .= '</div>'.$other_after
				.'</div>';

		return $out;
	}

	/**
	 * @param string 	$id 			the id of the element
	 * @param string 	$name 			the name of the element
	 * @param string 	$value 			default value for the input field
	 * @param boolean 	$is_checked 	true if radio is checked
	 * @param string 	$other_param 	added in the html code
	 *
	 * @return string with the html code for the input type="radio" element
	 */
	public static function getInputRadio( $id, $name, $value, $is_checked, $other_param,$class = 'radio' ) {

		return '<input class="'.$class.'" type="radio" id="'.$id.'" name="'.$name.'" value="'.$value.'"'
		.( $is_checked ? 'checked="checked"' : '' )
		.( $other_param != '' ? ' '.$other_param : '' ).' />';
	}

	/**
	 * public static function getLineRadio( $css_line, $css_label, $label_name, $id, $name, $value, $is_selected )
	 *
	 * @param string 	$css_line 		the css class for the line
	 * @param string 	$css_label 		the css label for the label
	 * @param string 	$label_name 	text contained into the label
	 * @param string 	$id 			the id of the element
	 * @param string 	$name 			the name of the element
	 * @param string 	$value 			default value for the input field
	 * @param boolean 	$is_checked 	true if radio is checked
	 * @param string 	$other_param 	added in the html code
	 *
	 * @return string with the html code for the input type="radio" element
	 */
	public static function getLineRadio( $css_line, $css_label, $label_name, $id, $name, $value, $is_checked, $other_param = '' ) {

		return '<div class="'.$css_line.'">'
		.Form::getInputRadio( $id, $name, $value, $is_checked, $other_param )
		.' <label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label>'
		.'</div>';
	}

	/**
	 * public static function getRadio(  $label_name, $id, $name, $is_checked = false, $value = 1 )
	 *
	 * @param string 	$label_name 	text contained into the label
	 * @param string 	$id 			the id of the element
	 * @param string 	$name 			the name of the element
	 * @param string 	$value 			value for the input field
	 * @param boolean 	$is_checked 	optional,if true the radio is checked, default is false
	 *
	 * @return string with the html code for the input type="radio" element
	 */
	public static function getRadio( $label_name, $id, $name, $value, $is_checked = false ) {

		return Form::getLineRadio( 'form_line_l', 'label_normal', $label_name, $id, $name, $value, $is_checked );
	}

	/**
	 * public static function getRadioSet(  $label_name, $id, $name, $all_value , $selected = '', $other_after = '', $other_before = '' )
	 *
	 * @param string 	$label_name 	text contained into the label
	 * @param string 	$id 			the id of the element
	 * @param string 	$name 			the name of the element
	 * @param string 	$value 			value for the input field
	 * @param boolean 	$is_checked 	optional,if true the radio is checked, default is false
	 *
	 * @return string with the html code for the input type="radio" element
	 */
	public static function getRadioSet( $group_name, $id, $name, $all_value , $selected = '', $other_after = '', $other_before = '' ) {
		$count = 0;
		$out = '<div class="form_line_l">'
				.$other_before
				.'<p><span class="label_effect">'.$group_name.'</span></p>'
				.'<div class="grouping">';
		foreach( $all_value as $label_item => $val_item ) {
			$out .= Form::getInputRadio( 	$id.'_'.$count,
					$name,
					$val_item,
					$val_item == $selected,
					'' );
			$out .= ' <label class="label_padded" for="'.$id.'_'.$count.'">'
					.$label_item.'</label> <br />';
			$count++;
		}
		$out .= '</div>'.$other_after.'</div>';

		return $out;
	}

	public static function getRadioHoriz( $group_name, $id, $name, $all_value , $selected = '', $other_after = '', $other_before = '' ) {
		$count = 0;
		$out = '<div class="form_line_l">'
				.$other_before
				.'<p><span>'.$group_name.'</span></p>';
		//.'<span class="grouping">';
		foreach( $all_value as $label_item => $val_item ) {
			$out .= Form::getInputRadio( 	$id.'_'.$count,
					$name,
					$val_item,
					$val_item == $selected,
					'' );
			$out .= ' <label class="label_padded" for="'.$id.'_'.$count.'">'
					.$label_item.'</label> &nbsp;';
			$count++;
		}
		$out .= $other_after.'</div>';

		return $out;
	}

	/**
	 * public static function getOpenCombo( $group_name, $css_line, $other_before )
	 *
	 * @param string 	$group_name 	text contained into the group intestation
	 * @param string 	$css_line 		optional the css class of the line
	 * @param string 	$other_before 	optional html code added before the label element
	 * @return string with the html code for open a group of combo element (checkbox, radio, ...)
	 */
	public static function getOpenCombo( $group_name, $css_line = 'form_line_l', $other_before = '' ) {

		return'<div class="'.$css_line.'">'
		.$other_before
		.'<p><span class="label_effect">'.$group_name.'</span></p>'
		.'<div class="grouping">';
	}

	/**
	 * public static function getcloseCombo( $other_after )
	 * @param string 	$other_after 	optional html code added after the input element
	 *
	 * @return string with the html code for close a combo group
	 */
	public static function getCloseCombo( $other_after = '' ) {

		return '</div>'
		.$other_after
		.'</div>';
	}

	/**
	 * @param string 	$legend 		text contained into the legend tag
	 * @param string 	$id_field 		id of the fieldset
	 * @param string 	$css_line 		optional the css class of the fieldset
	 *
	 * @return string 	with the html code for open a fieldset
	 */
	public static function getOpenFieldset( $legend, $id_field = '', $css_line = 'fieldset-std'  ) {

		return'<fieldset'.( $id_field != '' ? ' id="'.$id_field.'"' : '' ).' class="'.$css_line.'">'
		.( $legend != '' ? '<legend>'
				.$legend.'</legend>' : '' )
		.'<div class="fieldset-content"'.( $id_field != '' ? ' id="content_'.$id_field.'"' : '' ).'>';
	}

	public static function openCollasableFieldset( $legend, $id_field = '') {

		return'<fieldset'.( $id_field != '' ? ' id="'.$id_field.'"' : '' ).' class="fieldset-std fieldset-close">'
		.( $legend != '' ? '<legend>'
				.'<a class="filedset-av" href="javascript: ;" onclick="( this.parentNode.parentNode.className != \'fieldset-std fieldset-close\' '
				.' ? this.parentNode.parentNode.className = \'fieldset-std fieldset-close\' '
				.' : this.parentNode.parentNode.className = \'fieldset-std fieldset-open\' )">'
				.$legend.'</a></legend>' : '' )
		.'<div class="fieldset-content"'.( $id_field != '' ? ' id="content_'.$id_field.'"' : '' ).'>';
	}

	/**
	 * @return string with the html code for close a fieldset
	 */
	public static function getCloseFieldset( ) {

		return '</div>'
		.'</fieldset>';
	}

	/**
	 * public static function getTextarea( $label_name, $id, $name, $maxlenght, $value, $other_after )
	 *
	 * this public static function is a temporary substitute for a more complete one
	 */
	public static function getTextarea($label_name, $id, $name, $value = '', $extra_param_for = false, $id_form = '',
									   $css_line = 'form_line_l', $css_label = 'floating', $css_text = 'textarea',$simple=false) {

		$html_code = '<div class="'.$css_line.'">'
				.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.'</label></p><br />'
				.'<div class="nofloat"></div>'
				.loadHtmlEditor($id_form, $id, $name, $value, $css_text, $extra_param_for,$simple)
				.'</div>';
		return $html_code;
	}

	public static function getInputTextarea($id ,$name , $value = '', $css_text = false, $rows = 5, $cols = 22, $maxlength = '', $other_param = '' ) {

		if($css_text === false) $css_text = 'textarea';

		return '<textarea class="form-control '.$css_text.'" id="'.$id.'" name="'.$name.'" rows="'.$rows.'" cols="'.$cols.'" maxlength="'.$maxlength.'" '.$other_param.'>'.$value.'</textarea>';
	}

	public static function getSimpleTextarea($label_name, $id ,$name , $value = '', $css_line = false, $css_label = false, $css_text = false, $rows = 5, $cols = 22, $afterlabel = '', $maxlength = null ) {

		if($css_line === false) $css_line = 'form_line_l';
		if($css_label === false) $css_label = 'floating';
		if($css_text === false) $css_text = 'textarea';
        $maxlength_info = '';
		if($maxlength)
		{
            $maxlength_info = "<small>(".Lang::t('_MAX_LENGTH_TEXT_AREA', 'course')." <em>".$maxlength."</em>)</small> <br/><small>".Lang::t('_TOTAL_CHARS', 'course').": <em class='charNum'> ".strlen($value)."</em></small> ";

            $script = cout('<script type="text/javascript">
			$("#'.$id.'").keyup(function(){
				el = $(this);
				$(el).prev("p").find(".charNum").text(el.val().length);
				if(el.val().length > '.$maxlength.'){
					$(el).prev("p").find(".charNum").css( "color", "red");
				} else {
				    $(el).prev("p").find(".charNum").css( "color", "#000000");
				}
			}); </script>', 'scripts');
		}

		return '<div class="'.$css_line.'">'
		.'<p><label class="'.$css_label.'" for="'.$id.'">'.$label_name.' '.$maxlength_info.'</label></p>'
		.Form::getInputTextarea($id ,$name , $value, $css_text, $rows, $cols, $maxlength)
		.''.$afterlabel.'</div>'.$script;
	}



	public static function getSimpleText($label_name, $text, $height = 0, $css_line = false, $css_label = false) {
		if($css_line === false) $css_line = 'form_line_l';
		if($css_label === false) $css_label = 'floating';

		return '<div class="'.$css_line.'">'."\n"
		.'<p><label class="'.$css_label.'">'.$label_name.'</label></p>'
		.'<div class="inline_block"'.($height>0 ? ' style="max-height:'.$height.';overflow:auto"' : '').'>'.$text.'</div>'
		.'</div>'."\n";
	}



	/**
	 * public static function getBreakRow()
	 *
	 * @return string with the html for a line break
	 */
	public static function getBreakRow( ) {

		return '<div class="nofloat"></div><br />';
	}

	public static function openFormLine($id=FALSE) {
		return '<div class="form_line_l"'.($id !== FALSE ? ' id="'.$id.'"' : "").'>';
	}

	public static function closeFormLine() {
		return '</div>';
	}

	public static function getLabel( $for, $label_name, $css_label = 'floating' /*'label_bold'*/ ) {
		return '<label class="'.$css_label.'"'.($for ? ' for="'.$for.'"' : '').'>'.$label_name.'</label>';
	}

	/**
	 * public static function closeElementSpace()
	 * @return string contains the close tag for element container
	 */
	public static function closeElementSpace( ) {
		return '<div class="nofloat"></div>'
		.'</div>';
	}

	/**
	 * public static function openButtonSpace()
	 * @param string $css_div the css class
	 *
	 * @return string contains the open tag for button element
	 */
	public static function openButtonSpace($css_div = false) {
		return '<div class="'.( $css_div == false ? 'form_elem_button' : $css_div ).'">';
	}

	/**
	 * public static function getReset( $id, $name, $value, $css_class )
	 *
	 * @param string $id 			the id of the reset button
	 * @param string $name 			the name of the reset button
	 * @param string $value 		the value of the reset button
	 * @param string $css_button 	optional css class for the button
	 *
	 * @return string contains the close tag for reset element
	 */
	public static function getReset( $id, $name, $value, $css_button = 'button' ) {
		return '<input type="reset" '
		."\n\t".'class="'.$css_button.'" '
		."\n\t".'id="'.$id.'" '
		."\n\t".'name="'.$name.'" '
		."\n\t".'value="'.$value.'" />';
	}

	/**
	 * public static function getButton( $id, $name, $value, $css_class )
	 *
	 * @param string $id 			the id of the submit button
	 * @param string $name 			the name of the submit button
	 * @param string $value 		the value of the submit button
	 * @param string $css_button 	optional css class for the button
	 * @param string $other_param 	other element for the tag
	 *
	 * @return string contains the close tag for button element
	 */
	public static function getButton( $id, $name, $value, $css_button = FALSE, $other_param = '', $use_js = true, $is_submit = true ) {

		if($css_button == 'yui-button') {
			// return 	'<span id="'.$id.'_span" class="yui-button yui-submit-button">'
			return 	'<span id="'.$id.'_span">'
					.'<span class="first-child">'
					// .'<input type="'.($is_submit ? 'submit' : 'button').'" id="'.$id.'" '.($name ? 'name="'.$name.'" ' : '').'value="'.$value.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />'
					.'<input type="'.($is_submit ? 'submit' : 'button').'" class="btn btn-default" id="'.$id.'" '.($name ? 'name="'.$name.'" ' : '').'value="'.$value.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />'
					.'</span>'
					.'</span>';

		}
		$css_button = ($css_button === FALSE ? 'button' : $css_button);
		return '<input type="'.($is_submit ? 'submit' : 'button').'" '
				// ."\n\t".'class="'.$css_button.'" '
				//."\n\t".'class="'.$css_button.' btn btn-default" '
                ."\n\t".'class="'.$css_button.' btn btn-default" '    // #10984
				."\n\t".'id="'.$id.'" '
				."\n\t".'name="'.$name.'" '
				."\n\t".'value="'.$value.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />';
	}


	/**
	 * public static function getInputButton( $id, $name, $value, $type, $css_class, $other_param )
	 *
	 * @param string $id 			the id of the submit button
	 * @param string $name 			the name of the submit button
	 * @param string $value 		the value of the submit button
	 * @param string $type		the type of the button (submit, button, reset)
	 * @param string $css_button 	optional css class for the button
	 * @param string $other_param 	other element for the tag
	 *
	 * @return string contains the close tag for button element
	 */
	public static function getInputButton( $id, $name, $value, $type = 'button', $css_button = FALSE, $other_param = '' ) {
		$css_button = ($css_button === FALSE ? 'button' : $css_button);
		if (($type!='submit') && ($type!='button') && ($type!='reset')) $type = 'button';
		return '<input type="'.$type.'" '
		."\n\t".'class="'.$css_button.'" '
		."\n\t".'id="'.$id.'" '
		."\n\t".'name="'.$name.'" '
		."\n\t".'value="'.$value.'"'.( $other_param != '' ? ' '.$other_param : '' ).' />';
	}

	/**
	 * public static function closeButtonSpace()
	 *
	 * @return string contains the close tag for button element
	 */
	public static function closeButtonSpace( ) {
		return '</div>';
	}

	/**
	 * public static function closeForm()
	 *
	 * @return string contains the close tag for the form
	 */
	public static function closeForm() {

		return '</div>'
		.'</form>';
	}
}

?>
