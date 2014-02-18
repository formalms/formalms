<?php Get::title(array(Lang::t('_CART', 'cart'))); ?>

<div class="std_block">

<?php

$empty_cart = '<a class="ico-wt-sprite subs_cancel" href="javascript:;" onclick="emptyCart();"><span>'.Lang::t('_EMPTY_CART', 'cart').'</span></a>';
$del_sel = '<a class="ico-wt-sprite subs_del" href="javascript:;" onclick="delSelectedElement();"><span>'.Lang::t('_DEL_SELECTED_ELEMENT', 'cart').'</span></a>';

$rel_action = $empty_cart.' '.$del_sel;

$this->widget('table', array(
	'id'			=> 'cart_table',
	'ajaxUrl'		=> 'ajax.server.php?r=cart/getCartList',
	'rowsPerPage'	=> 0,
	'startIndex'	=> 0,
	'results'		=> 0,
	'sort'			=> 'name',
	'dir'			=> 'asc',
	'columns'		=> array(
		array('key' => 'code', 'label' => Lang::t('_CODE', 'cart'), 'sortable' => true),
		array('key' => 'name', 'label' => Lang::t('_NAME', 'cart'), 'sortable' => true),
		array('key' => 'type', 'label' => Lang::t('_COURSE_TYPE', 'cart'), 'sortable' => true),
		array('key' => 'date_begin', 'label' => Lang::t('_DATE_BEGIN', 'cart'), 'sortable' => true),
		array('key' => 'date_end', 'label' => Lang::t('_DATE_END', 'cart'), 'sortable' => true),
		array('key' => 'price', 'label' => Lang::t('_COURSE_PRIZE', 'cart'), 'sortable' => true)
	),
	'fields'		=> array('id', 'code', 'name', 'type', 'date_begin', 'date_end', 'price', 'action'),
	'show'			=> 'table',
	'use_paginator' => false,
	'print_table_below' => false,
	'rel_actions'	=> $rel_action,
	'stdSelection' => true,
));


?>

<?php
	$paypal_return_url =Get::sett('url')._folder_lms_.'/paypal.php?op=ok';
	$paypal_notify_url =Get::sett('url')._folder_lms_.'/paypal.php';
?>

<div class="total_container">
	<p class="total_price cart_right"><?php echo Lang::t('_TOTAL', 'cart'); ?> : <span id="price"><?php echo $total_price ?></span> <?php echo (Get::sett('currency_symbol') !== '' ? Get::sett('currency_symbol') : '&eur;'); ?></p>
	<div class="nofloat"></div>
	<a class="ico-wt-sprite subs_categorize cart_right" href="javascript:;" onclick="makeOrderPopup();"><span class="order_now"><?php echo Lang::t('_ORDER_NOW', 'cart'); ?></span></a>
	<form action="<?php echo $paypal_url; ?>" method="post" id="paypal_form">
		<input type="hidden" name="cmd" value="_xclick" />
		<input type="hidden" name="business" value="<?php echo Get::sett('paypal_mail', ''); ?>">
		<input type="hidden" name="lc" value="<?php echo (Lang::lang_code() === 'italian' ? 'IT' : 'EN'); ?>">
		<input type="hidden" name="item_name" value="<?php echo Lang::t('_ORDER_NUMBER', 'cart'); ?>" id="item_name">
		<input type="hidden" name="item_number" value="0" id="id_transaction">
		<input type="hidden" name="amount" value="0" id="total_price">
		<input type="hidden" name="currency_code" value="<?php echo Get::sett('paypal_currency', 'EUR'); ?>">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="0.00">
		<input type="hidden" name="return" value="<?php echo $paypal_return_url; ?>" id="return_link">
		<input type="hidden" name="cancel_return" value="set_by_js" id="cancel_return_link">
		<input type="hidden" name="notify_url" value="<?php echo $paypal_notify_url; ?>" id="notify_url">
		<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_SM.gif:NonHostedGuest">
	</form>
</div>
<div style="clear: both;"></div>

<script type="text/javascript">
	function emptyCart()
	{
		YAHOO.util.Connect.asyncRequest("POST", 'ajax.server.php?r=cart/emptyCart&',
										{
											success: function(o)
											{
												var res = YAHOO.lang.JSON.parse(o.responseText);
												if (res.success)
												{
													var cart_element = YAHOO.util.Dom.get('cart_element');

													cart_element.innerHTML = '0';

													var price = YAHOO.util.Dom.get('price');

													price.innerHTML = '0';

													DataTable_cart_table.refresh();
												}
												else
												{

												}
											},
											failure: function()
											{

											}
										});
	}

	function delSelectedElement()
	{
		var extra = 'elements=' + DataTableSelector_cart_table.toString();

		YAHOO.util.Connect.asyncRequest("POST", 'ajax.server.php?r=cart/delSelectedElement&',
										{
											success: function(o)
											{
												var res = YAHOO.lang.JSON.parse(o.responseText);
												if (res.success)
												{
													var cart_element = YAHOO.util.Dom.get('cart_element');

													cart_element.innerHTML = res.cart_element;

													var price = YAHOO.util.Dom.get('price');

													price.innerHTML = res.price;

													DataTable_cart_table.refresh();
												}
												else
												{

												}
											},
											failure: function()
											{

											}
										}, extra);
	}

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
						constraintoviewport : true,
						buttons : [{ text:'<?php echo Lang::t('_UNDO', 'standard') ?>', handler:function(){this.hide();} } ]
					 });
		dialog.render(document.body);
	}

	YAHOO.util.Event.onDOMReady(function() {initialize("<?php echo Lang::t('_UNDO', 'standard'); ?>");});

	function makeOrderPopup()
	{
		var title = '<?php echo Lang::t('_PAYMENT_SELECT', 'cart'); ?>';
		var body = '<?php	$body = '<a href="index.php?r=cart/makeOrder&amp;wire=1">'.Get::img('standard/wire_payment.png').' '.Lang::t('_WIRE_PAYMENT', 'cart').'</a>';
								if(Get::sett('paypal_mail', '') !== '')
								$body .=	'<br/><br/>'
											.'<a href="javascript:;" onclick="makeOrderPaypal();">'.Get::img('standard/PayPal.gif').' '.Lang::t('_PAY_WITH_PAYPAL', 'cart').'</a>';//'<a class="ico-wt-sprite subs_add" href="javascript:;" onclick="makeOrderPaypal();"><span class="order_now">'.Lang::t('_PAY_WITH_PAYPAL', 'cart').'</span></a>';
							echo $body;
					?>';

		dialog.setHeader(title);
		dialog.setBody(body);
		dialog.show();
	}

	function hideDialog()
	{
		dialog.hide();
	}

	function makeOrder()
	{
		var div_feedback = YAHOO.util.Dom.get('feedback');
		if(!div_feedback)
		{
			div_feedback = document.createElement('feedback');

			div_feedback.id = 'feedback';
			div_feedback.className = 'container-feedback';

			document.body.appendChild(div_feedback);
		}

		YAHOO.util.Connect.asyncRequest("POST", 'ajax.server.php?r=cart/makeOrder&',
										{
											success: function(o)
											{
												var res = YAHOO.lang.JSON.parse(o.responseText);
												if (res.success)
												{
													var cart_element = YAHOO.util.Dom.get('cart_element');

													cart_element.innerHTML = '(0)';

													var price = YAHOO.util.Dom.get('price');

													price.innerHTML = '0';

													div_feedback.innerHTML = res.message;

													DataTable_cart_table.refresh();
												}
												else
												{
													div_feedback.innerHTML = res.message;
												}
											},
											failure: function()
											{

											}
										});
	}

	function makeOrderPaypal()
	{
		YAHOO.util.Connect.asyncRequest("POST", 'ajax.server.php?r=cart/makeOrder&',
										{
											success: function(o)
											{
												var res = YAHOO.lang.JSON.parse(o.responseText);
												if (res.success)
												{
													var item_name = YAHOO.util.Dom.get('item_name');

													item_name.value += ' ' + res.id_transaction; <?php // this must be the transaction id ?>

													// var return_link = YAHOO.util.Dom.get('return_link');
													// return_link.value = res.link; // + '&paypal_ok=1';

													var cancel_return_link = YAHOO.util.Dom.get('cancel_return_link');

													cancel_return_link.value = res.link + '&cancel=1';

													var id_transaction = YAHOO.util.Dom.get('id_transaction');

													id_transaction.value = res.id_transaction;

													var total_price = YAHOO.util.Dom.get('total_price');

													total_price.value = res.total_price;

													var form = YAHOO.util.Dom.get('paypal_form');

													form.submit();
												}
												else
												{
													var div_feedback = YAHOO.util.Dom.get('feedback');
													if(!div_feedback)
													{
														div_feedback = document.createElement('feedback');

														div_feedback.id = 'feedback';
														div_feedback.className = 'container-feedback';

														document.body.appendChild(div_feedback);
													}

													div_feedback.innerHTML = res.message;
												}
											},
											failure: function()
											{

											}
										});
	}
</script>

</div>