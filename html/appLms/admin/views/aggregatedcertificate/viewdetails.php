<?php

cout(    getTitleArea(Lang::t('_DETAILS', 'certificate'))
                .'<div class="std_block">'
                .getBackUi('index.php?r=alms/'.$controller_name.'/'.$opsArr['associationsManagement'].'&id_certificate='.$id_certificate, Lang::t('_BACK'))
                .$tb->getTable()
                .getBackUi('index.php?r=alms/'.$controller_name.'/'.$opsArr['associationsManagement'].'&id_certificate='.$id_certificate, Lang::t('_BACK'))
                .'</div>');
