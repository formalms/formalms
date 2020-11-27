<?php

      cout(
        getTitleArea(Lang::t('_TITLE_META_CERTIFICATE_CREATE','certificate'), 'certificate')
        .  '<div class="std_block">'
        .getBackUi( 'index.php?r=alms/'.$controller_name.'/'.$opsArr['home'], Lang::t('_BACK') )
        .  $tb->getTable()
        .getBackUi( 'index.php?r=alms/'.$controller_name.'/'.$opsArr['home'], Lang::t('_BACK') )

        . '</div>'

      );

    
