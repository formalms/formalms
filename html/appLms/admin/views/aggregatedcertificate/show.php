<?php

cout(getTitleArea(Lang::t('_TITLE_META_CERTIFICATE', 'certificate').'<div class="std_block">'));

$form = new Form();

cout(	
        $form->openForm('certificate_filter', 'index.php?r=alms/'.$controller_name.'/show')
        .'<div class="quick_search_form">
                <div>
                    <div class="simple_search_box">'
                        .Form::getInputTextfield("search_t", "filter_text", "filter_text", (isset($filter_text) ? $filter_text : ''), '', 255, '' )
                        .Form::getButton("filter", "filter", Lang::t('_SEARCH', 'standard'), "search_b")
                        .Form::getButton("toggle_filter", "toggle_filter", Lang::t('_RESET', 'standard'), "reset_b")
                    .'</div>
                </div>
        </div>'
        .$form->closeForm()
    );

if(isset($_GET['result']))
{
    switch($_GET['result'])
    {
        case "ok":
            cout(getResultUi(Lang::t('_OPERATION_SUCCESSFUL')));
            break;
        case "err":
            cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
            break;
        case "err_del":
            cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
            break;
    }
}

//cout($tb->getTable().$tb->getNavBar($ini, $countAggrCerts).'</div>');

cout($tb->getTable()                                  
    .$tb->WriteNavBar('',
                                'index.php?r=alms/aggregatedcertificate/show&result=ok&ini=',
                                $ini,              
                                count($aggregateCertsArrTot))     

    .'</div>');
