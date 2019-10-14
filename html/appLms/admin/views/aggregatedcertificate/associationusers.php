<?php

cout('<div class="std_block">');

    $user_selection->loadSelector('index.php?r=alms/'.$controller_name.'/'. $opsArr['associationCourses'] 
       // .'&amp;type_course='.$type_assoc
        ,
        false,
        Lang::t('_USER_FOR_META_CERTIFICATE_ASSIGN','certificate'),
    true);
    
   
cout('</div>');