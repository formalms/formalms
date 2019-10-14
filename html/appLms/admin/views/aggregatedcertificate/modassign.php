<?php
   
    $user_selection->loadSelector(
                'index.php?r=alms/'.$this->controller_name.'/modassignment',
                false,
                Lang::t('_USER_FOR_META_CERTIFICATE_ASSIGN', 'certificate'),
                true);