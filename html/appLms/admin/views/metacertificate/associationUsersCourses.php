<?php

     cout(    getTitleArea(Lang::t('_AGGRETATE_CERTIFICATES_ASSOCIATION_CAPTION', 'certificate'), 'certificate')
                    .'<div class="std_block">'
                    .$form->openForm('new_assign_step_3', 'index.php?r=alms/'.$this->controller_name.'/'.$opsArr['saveAssignment'])
                    .$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
                    .$form->getHidden('id_metacertificate', 'id_metacertificate', $id_metacertificate)
                    );
             
        cout(    
                    $form->openElementSpace()
                    .$tb->getTable()
                    .$form->closeElementSpace()
                    .$form->openButtonSpace()
                   // .$form->getButton('select_all', 'select_all', Lang::t('_SELECT_ALL', false, null, false, ))
                    .Form::getButton('select_all', 'select_all', Lang::t('_SELECT_ALL'),false,'',true,true)
                    .$form->getButton('insert', 'insert', Lang::t('_INSERT'))
                    .$form->getButton('undo_assign', 'undo_assign', Lang::t('_UNDO'))
                    .$form->closeButtonSpace()
                    .$form->closeForm()
        );