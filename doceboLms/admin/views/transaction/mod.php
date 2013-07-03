<?php
	$array_title = array(	'index.php?r=alms/transaction/show' => Lang::t('_TRANSACTION', 'transaction'),
							Lang::t('_MOD', 'transaction'));

	echo	getTitleArea($array_title)
			.'<div class="std_block">';
?>

<div class="transaction_info">
	<h2><?php echo Lang::t('_DETAILS', 'transaction'); ?></h2>
	<ul class="link_list">
		<li><?php echo Lang::t('_USERNAME', 'transaction'); ?> : <?php echo $user_info[ACL_INFO_USERID]; ?></li>
		<li><?php echo Lang::t('_FIRSTNAME', 'transaction'); ?> : <?php echo $user_info[ACL_INFO_FIRSTNAME]; ?></li>
		<li><?php echo Lang::t('_LASTNAME', 'transaction'); ?> : <?php echo $user_info[ACL_INFO_LASTNAME]; ?></li>
		<li><?php echo Lang::t('_PRICE', 'transaction'); ?> : <?php echo $transaction_info['price']; ?></li>
		<li><?php echo Lang::t('_DATE', 'transaction'); ?> : <?php echo Format::date($transaction_info['date_creation'], 'datetime'); ?></li>
		<?php if($transaction_info['paid']) : ?><li><?php echo Lang::t('_ACTIVE', 'transaction'); ?> : <?php echo Format::date($transaction_info['date_activated'], 'datetime'); ?></li><?php endif; ?>
	</ul>
</div>
<br/>
<?php echo	Form::openForm('transaction_mod_form', 'index.php?r=alms/transaction/mod&amp;id_trans='.$id_trans)
			.Form::getHidden('id_user', 'id_user', $transaction_info['id_user'])
			.Form::openElementSpace()
			.$tb->getTable()
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('save', 'save', Lang::t('_SAVE', 'transaction'))
			.($transaction_info['paid'] ? Form::getButton('not_paid', 'not_paid', Lang::t('_SET_NOT_PAID', 'transaction')) : '')
			.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'transaction'))
			.Form::closeButtonSpace()
			.Form::closeForm(); ?>
</div>