<?php
    

    cout(    getTitleArea(Lang::t('_ASSOCIATIONS_AGGREGATED_CERTIFICATES'), 'certificate')
                .'<div class="std_block">');

    

    cout(    $tb->getTable()
                .$tb->getNavBar($ini, $countAssociations)
                .getBackUi('index.php?r=alms/'.$controller_name.'/'. $arrOps['home'], Lang::t('_BACK'))
                .'</div>');