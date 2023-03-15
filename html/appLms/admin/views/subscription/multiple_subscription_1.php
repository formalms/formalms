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

$title = ['index.php?r=' . $this->link_course . '/show' => Lang::t('_COURSE', 'course'),
                Lang::t('_MULTIPLE_SUBSCRIPTION', 'course'), ];

$user_selector->loadSelector('index.php?r=' . $this->link . '/multiplesubscription',
                                $title,
                                Lang::t('_CHOOSE_SUBSCRIBE', 'subscribe'),
                                true);
