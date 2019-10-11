<?php
    

    cout(    getTitleArea(Lang::t('_AGGRETATE_CERTIFICATES_ASSOCIATION_CAPTION'), 'certificate')
                .'<div class="std_block">');

    

    cout(    $tb->getTable()
                .$tb->getNavBar($ini, sql_num_rows($result))
                .getBackUi('index.php?r=alms/'.$controller_name.'/'. $arrOps['home'], Lang::t('_BACK'))
                .'</div>');