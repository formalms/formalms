<?php
    echo getTitleArea(Lang::t('_TRANSACTION', 'transaction'))
            . '<div class="std_block">';

    $this->widget('table', [
        'id' => 'transaction_table',
        'ajaxUrl' => 'ajax.adm_server.php?r=alms/transaction/getTransactionData',
        'rowsPerPage' => Get::sett('visuItem', 25),
        'startIndex' => 0,
        'results' => Get::sett('visuItem', 25),
        'sort' => 'date_creation',
        'dir' => 'desc',
        'columns' => [
            ['key' => 'userid', 'label' => Lang::t('_USERNAME', 'transaction'), 'sortable' => true],
            ['key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'transaction'), 'sortable' => true],
            ['key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'transaction'), 'sortable' => true],
            ['key' => 'date_creation', 'label' => Lang::t('_DATE', 'transaction'), 'sortable' => true],
            ['key' => 'date_activated', 'label' => Lang::t('_ACTIVE', 'transaction'), 'sortable' => true],
            ['key' => 'price', 'label' => Lang::t('_PRICE', 'transaction')],
            ['key' => 'paid', 'label' => '', 'className' => 'img-cell'],
            ['key' => 'edit', 'label' => Get::img('standard/edit.png', Lang::t('_MOD', 'transaction')), 'className' => 'img-cell'],
        ],
        'fields' => ['id_trans', 'userid', 'firstname', 'lastname', 'date_creation', 'date_activated', 'price', 'paid', 'edit'],
        'show' => 'table',
        'delDisplayField' => 'name',
        'rel_actions' => '',
    ]);
?>
</div>