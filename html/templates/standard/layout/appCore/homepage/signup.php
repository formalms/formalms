<?php echo getTitleArea(Lang::t("_REGISTER", "register"), "register") ?>
<div class="std_block">
    <?php
    echo getBackUi("index.php", Lang::t("_BACK", "standard"));    
    echo    Form::openForm("register", Get::rel_path("base") . "/index.php?r=" . _register_)
              . $this->model->getConfirmRegister()
              . "<br/><a href='./index.php'>" . Lang::t('_GOTO_LOGIN', 'register') . "</a>"
          . Form::closeForm();
    ?>
</div>