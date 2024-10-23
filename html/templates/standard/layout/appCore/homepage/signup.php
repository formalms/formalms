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

 echo '<div class="homepage__row homepage__row--gray homepage__back">
	        <a href="index.php">
		        <span class="fa fa-chevron-left"></span>' . str_replace('&lt;&lt; ', '', Lang::t('_BACK', 'standard')) . '
	        </a>
       </div>';
 echo Form::openForm('register', FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _register_);
 echo '<div class="homepage__row homepage__row--gray">'
        . $this->model->getConfirmRegister() .
	    '<br><br>
       </div>';
 echo '<div class="homepage__row">
	    <div class="col-xs-12 col-sm-6 col-sm-offset-3">
		    <br>
			<a class="forma-button forma-button--primary" href="./index.php" style="margin-bottom: 10px; max-width: none;">' . Lang::t('_GOTO_LOGIN', 'register') . '</a>
	    </div>
       </div>';
 echo Form::closeForm();