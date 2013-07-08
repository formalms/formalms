<?php
echo getTitleArea(array(
	'index.php?r=alms/games/show' => Lang::t('_CONTEST', 'games'),
	Lang::t('_ADD', 'games')
));
?>
<div class="std_block">
	<?php
	echo Form::openForm('add_games', 'index.php?r=alms/games/insert', false, false, 'multipart/form-data')
	. Form::getHidden('title', 'title', 255, Get::req('title', DOTY_MIXED, $data['title']))
	. Form::openElementSpace()
	. Form::getTextfield(Lang::t('_TITLE', 'games'), 'title', 'title', 255, Get::req('title', DOTY_MIXED, $data['title']))
	. Form::getDatefield(Lang::t('_START_DATE', 'games'), 'start_date', 'start_date', Get::req('start_date', DOTY_MIXED, $data['start_date']))
	. Form::getDatefield(Lang::t('_DATE_END', 'games'), 'end_date', 'end_date', Get::req('end_date', DOTY_MIXED, $data['end_date']))
	. Form::getRadioSet(Lang::t('_TYPE', 'games'), 'type_of', 'type_of', array(
		Lang::t('_LONAME_scormorg', 'storage') => 'scorm',
			), Get::req('type_of', DOTY_STRING, $data['type_of']))
	. Form::getRadioSet(Lang::t('_PLAY_CHANCE', 'games'), 'play_chance', 'play_chance', array(
		Lang::t('_UNLIMITED', 'games') => 'play_unlimited',
		Lang::t('_ONLY_ONCE', 'games') => 'play_once',
			),'play_unlimited')
	. Form::getTextarea(Lang::t('_DESCRIPTION', 'games'), 'description', 'description', Get::req('description', DOTY_MIXED, $data['description']))
	. Form::closeElementSpace()
	. Form::openButtonSpace()
	. Form::getButton('save', 'save', Lang::t('_SAVE', 'games'))
	. Form::getButton('undo', 'undo', Lang::t('_UNDO', 'games'))
	. Form::closeButtonSpace()
	. Form::closeForm();
	?>
</div>