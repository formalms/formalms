<?php

$op =   'aggregatedcertificate/'.(get::req('type_assoc',DOTY_INT) == 0 ? 'associationUsersCourses': 'associationUsersPath' );

cout('<div class="std_block">');

    $user_selection->loadSelector('index.php?r=alms/'.$op,
        false,
        Lang::t('_USER_FOR_META_CERTIFICATE_ASSIGN','certificate'),
    true);

    
   
cout('</div>');

?>

