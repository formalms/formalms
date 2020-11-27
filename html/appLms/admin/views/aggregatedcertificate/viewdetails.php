<?php

cout(    getTitleArea(Lang::t('_DETAILS', 'certificate').":&nbsp".$cert_name)
                .'<div class="std_block">'
                .getBackUi('index.php?r=alms/'.$controller_name.'/associationsManagement&id_certificate='.$id_certificate, Lang::t('_BACK'))
                .$tb->getTable()
                .getBackUi('index.php?r=alms/'.$controller_name.'/associationsManagement&id_certificate='.$id_certificate, Lang::t('_BACK'))
                .'</div>');
