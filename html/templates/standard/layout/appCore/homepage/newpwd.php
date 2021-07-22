<?php echo getTitleArea(Lang::t("_CHOOSE_NEW_PASSWORD", "register")) ?>
<div class="std_block">
    <?php echo getBackUi("index.php", Lang::t("_BACK", "standard")); ?>
    <ul class="reg_instruction">
        <?php if($pass_max_time_valid) { ?>
            <li><?php echo Lang::t("_NEWPWDVALID", "register", array("[valid_for_day]" => $pass_max_time_valid)); ?></li>
        <?php } ?>
        <?php if($pass_min_char) { ?>
            <li><?php echo Lang::t("_REG_PASS_MIN_CHAR", "register", array("[min_char]" => $pass_min_char)); ?></li>
        <?php } ?>
        <?php if($pass_alfanumeric) { ?>
            <li><?php echo Lang::t("_REG_PASS_MUST_BE_ALPNUM", "register", $pass_alfanumeric); ?></li>
        <?php } ?>
    </ul>
    <?php
    echo    Form::openForm('new_password', Get::rel_path("base") . "/index.php?r=" . _newpwd_)
              . ($msg ? "<p class='reg_err_data'>" . $msg . "</p>" : "")
              . Form::openElementSpace()
                  . Form::getPassword(Lang::t("_PASSWORD", "register"), "new_password", "new_password", 255)
                  . Form::getPassword(Lang::t("_RETYPE_PASSWORD", "register"), "retype_new_password", "retype_new_password", 255)
                  . Form::getHidden("code", "code", $code)
              . Form::closeElementSpace()
              . Form::openButtonSpace()
                . Form::getButton("send", "send", Lang::t("_SAVE", "register"))
              . Form::closeButtonSpace()
          . Form::closeForm();
    ?>
</div>