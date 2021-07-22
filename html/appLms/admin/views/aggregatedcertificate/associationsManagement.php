<?php
    

    cout(    getTitleArea($cert_name.':&nbsp;'.Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION','certificate'))
                .'<div class="std_block">');

    

    cout(    $tb->getTable()
                .$tb->getNavBar($ini, $countAssociations)
                .getBackUi('index.php?r=alms/'.$controller_name.'/'. $arrOps['home'], Lang::t('_BACK'))
                .'</div>');