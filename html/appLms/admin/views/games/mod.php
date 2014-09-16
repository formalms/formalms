<?php
echo getTitleArea(array(
	'index.php?r=alms/games/show' => Lang::t('_CONTEST', 'games'),
	Lang::t('_MOD', 'games')
));
?>
<div class="std_block">
	<?php
	echo Form::openForm('add_comm', 'index.php?r=alms/games/update', false, false, 'multipart/form-data')
	. Form::getHidden('id_game', 'id_game', Get::req('id_game', DOTY_INT, $data['id_game']))
	. Form::openElementSpace()
	. Form::getTextfield(Lang::t('_TITLE', 'games'), 'title', 'title', 255, Get::req('title', DOTY_MIXED, $data['title']))
	. Form::getDatefield(Lang::t('_START_DATE', 'games'), 'start_date', 'start_date', Get::req('start_date', DOTY_MIXED, $data['start_date']))
	. Form::getDatefield(Lang::t('_DATE_END', 'games'), 'end_date', 'end_date', Get::req('end_date', DOTY_MIXED, $data['end_date']))
	. Form::getRadioSet(Lang::t('_PLAY_CHANCE', 'games'), 'play_chance', 'play_chance', array(
		Lang::t('_UNLIMITED', 'organization') => 'play_unlimited',
		Lang::t('_ONLY_ONCE', 'organization') => 'play_once',
		), Get::req('play_chance', DOTY_STRING, $data['play_chance']) )
	. Form::getTextarea(Lang::t('_DESCRIPTION', 'games'), 'description', 'description', Get::req('description', DOTY_MIXED, $data['description']))
	. Form::getHidden('type_of', 'type_of', Get::req('type_of', DOTY_MIXED, $data['type_of']))
	. Form::closeElementSpace()
	. Form::openButtonSpace()
	. Form::getButton('save', 'save', Lang::t('_SAVE', 'games'))
	. Form::getButton('undo', 'undo', Lang::t('_UNDO', 'games'))
	. Form::closeButtonSpace()
	. Form::closeForm();
	?>
</div>