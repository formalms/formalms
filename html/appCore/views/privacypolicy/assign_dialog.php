<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

echo Form::openForm('assign_orgchart_form', 'ajax.adm_server.php?r=adm/privacypolicy/assign_action');
echo '<div class="folder_tree" id="assign_orgchart_container">';
echo Form::getHidden('id_policy', 'id_policy', $id_policy);
echo Form::getHidden('assign_orgchart_hidden_selection', 'selection', implode(',', $selection));
echo Form::getHidden('already_assigned', 'already_assigned', implode(',', $already_assigned));
echo Form::getHidden('old_selection', 'old_selection', implode(',', $selection));
echo '<div id="assign_orgchart_tree"></div>';
echo '</div>';
echo Form::closeForm();
