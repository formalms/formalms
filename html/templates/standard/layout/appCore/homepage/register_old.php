<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

    echo getBackUi('index.php', Lang::t('_BACK', 'standard'));
    echo Form::openForm('register', Forma\lib\Get::rel_path('base') . '/index.php?r=' . _register_)
              . $this->model->getRegisterForm()
          . Form::closeForm();
