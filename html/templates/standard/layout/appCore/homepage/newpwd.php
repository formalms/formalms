<?php echo getTitleArea(Lang::t('_CHOOSE_NEW_PASSWORD', 'register')); ?>
<div class="std_block">
    <?php echo getBackUi('index.php', Lang::t('_BACK', 'standard')); ?>
    <ul class="reg_instruction">
        <?php
            if ($options['pass_alfanumeric'] == 'on') {
                $s = Lang::t('_REG_PASS_MUST_BE_ALPNUM', 'register');
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['user_pwd_history_length'] > 1) {
                $s = Lang::t('_REG_PASS_MUST_DIFF', 'register', ['[min_char]' => $options['user_pwd_history_length']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['user_pwd_history_length'] == 1) {
                $s = Lang::t('_REG_PASS_MUST_DIFF_1', 'register');
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_min_char'] > 1) {
                $s = Lang::t('_REG_PASS_MIN_CHAR','register',['[min_char]'=>$options['pass_min_char']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_min_digit'] > 1) {
                $s = Lang::t('_REG_PASS_MIN_DIGITS','register',['[min_char]'=>$options['pass_min_digit']]).'</li>';
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_min_lowercase'] > 1) {
                $s = Lang::t('_REG_PASS_MIN_LOWER','register',['[min_char]'=>$options['pass_min_lowercase']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_min_uppercase'] > 1) {
                $s = Lang::t('_REG_PASS_MIN_UPPER','register',['[min_char]'=>$options['pass_min_uppercase']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_special_char'] > 1) {
                $s = Lang::t('_REG_PASS_MIN_NONALPHANUM','register',['[min_char]'=>$options['pass_special_char']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }

            if ($options['pass_min_char'] == 1) {
                $s = Lang::t('_REG_PASS_MIN_CHAR_1','register',['[min_char]'=>$options['pass_min_char']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_min_digit'] == 1) {
                $s = Lang::t('_REG_PASS_MIN_DIGITS_1','register',['[min_char]'=>$options['pass_min_digit']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_min_lowercase'] == 1) {
                $s = Lang::t('_REG_PASS_MIN_LOWER_1','register',['[min_char]'=>$options['pass_min_lowercase']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_min_uppercase'] == 1) {
                $s = Lang::t('_REG_PASS_MIN_UPPER_1','register',['[min_char]'=>$options['pass_min_uppercase']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            if ($options['pass_special_char'] == 1) {
                $s = Lang::t('_REG_PASS_MIN_NONALPHANUM_1','register',['[min_char]'=>$options['pass_special_char']]);
                if (in_array($s, $error )) {
                    echo "<li style='font-weight: bold; color: red;'>$s</li>";
                } else {
                    echo '<li>' . $s. '</li>';
                }
            }
            $s = Lang::t('_PASS_DIFFERENT_USERNAME', 'register');
            if (in_array($s, $error )) {
                echo "<li style='font-weight: bold; color: red;'>$s</li>";
            } else {
                echo '<li>' . $s. '</li>';
            }

            $s = Lang::t('_ERR_PWD_OLD', 'register');
            if (in_array($s, $error )) {
                echo "<li style='font-weight: bold; color: red;'>$s</li>";
            }

            $s = Lang::t('_ERR_PASSWORD_NO_MATCH', 'register');
            if (in_array($s, $error )) {
                echo "<li style='font-weight: bold; color: red;'>$s</li>";
            }
        ?>
    </ul>
    <?php
        echo Form::openForm('new_password', FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _newpwd_)
                  . ($msg ? "<p class='reg_err_data'>" . $msg . '</p>' : '')
                  . Form::openElementSpace()
                      . Form::getPassword(Lang::t('_PASSWORD', 'register'), 'new_password', 'new_password', 255)
                      . Form::getPassword(Lang::t('_RETYPE_PASSWORD', 'register'), 'retype_new_password', 'retype_new_password', 255)
                      . Form::getHidden('code', 'code', $code)
                  . Form::closeElementSpace()
                  . Form::openButtonSpace()
                    . Form::getButton('send', 'send', Lang::t('_SAVE', 'register'))
                  . Form::closeButtonSpace()
              . Form::closeForm();
    ?>
</div>