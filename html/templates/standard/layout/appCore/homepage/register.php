<?php echo getTitleArea(Lang::t("_REGISTER", "register"), "register") ?>
<div class="std_block">
    <?php
    echo getBackUi("index.php", Lang::t("_BACK", "standard"));
    echo    Form::openForm("register", Get::rel_path("base") . "/index.php?r=" . _register_)
              . $this->model->getRegisterForm()
          . Form::closeForm();
    ?>
</div>