<?php echo getTitleArea(Lang::t('_LOGIN', 'login')); ?>
<div class="std_block">
    <?php echo getBackUi('index.php', Lang::t('_BACK', 'standard')); ?>
    <div class="lostpwd_box">
	<span class="text_bold"><?php echo Lang::t('_LOST_TITLE_USER', 'register'); ?> - </span><?php echo Lang::t('_LOST_INSTRUCTION_USER', 'register'); ?>
        
        <?php
      
            echo Form::openForm('lost_user', FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _lostpwd_)
                      . Form::openElementSpace('form_right')
                          . Form::getHidden('lost_user_action', 'action', 'lost_user')
                          . Form::getLabel('email', "<span style='float:left;'>" . $lost_user_msg . '</span>' . Lang::t('_EMAIL', 'register'), 'text_bold')
                          . Form::getInputTextfield('textfield', 'lost_user_email', 'email', '', 255, '')
                          . Form::getButton('lost_user_send', 'send', Lang::t('_SEND', 'register'), 'button_nowh')
                      . Form::closeElementSpace()
                  . Form::closeForm();
      
        ?>
    </div>
    <div class="lostpwd_box">
	<span class="text_bold"><?php echo Lang::t('_LOST_TITLE_PWD', 'register'); ?> - </span><?php echo Lang::t('_LOST_INSTRUCTION_PWD', 'register'); ?>
        <?php
        echo Form::openForm('lost_pwd', FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _lostpwd_)
                  . Form::openElementSpace('form_right')
                      . Form::getHidden('lost_pwd_action', 'action', 'lost_pwd')
                      . Form::getLabel('userid', "<span style='float:left;'>" . $lost_pwd_msg . '</span>' . Lang::t('_USERNAME', 'register'), 'text_bold')
                      . Form::getInputTextfield('textfield', 'lost_pwd_userid', 'userid', '', 255, '')
                      . Form::getButton('lost_pwd_send', 'send', Lang::t('_SEND', 'register'), 'button_nowh')
                  . Form::closeElementSpace()
              . Form::closeForm();
        ?>
    </div>
</div>