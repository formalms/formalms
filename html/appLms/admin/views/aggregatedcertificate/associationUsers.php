<?php

cout(
    getTitleArea($cert_name . ':&nbsp;' . Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate'))
);

$op = 'aggregatedcertificate/' . (Forma\lib\Get::req('type_assoc', DOTY_INT) == 0 ? 'associationUsersCourses' : 'associationUsersPath');

cout('<div class="std_block">');

    $user_selection->loadSelector('index.php?r=alms/' . $op,
        false,
        Lang::t('_USER_FOR_META_CERTIFICATE_ASSIGN', 'certificate'),
    true);

cout('</div>');

?>

