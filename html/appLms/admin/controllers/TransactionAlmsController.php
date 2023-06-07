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

defined('IN_FORMA') or exit('Direct access is forbidden');

class TransactionAlmsController extends AlmsController
{
    protected $acl_man;
    protected $permissions;
    public TransactionAlms $model;
    public Services_JSON $json;

    public function init()
    {
        parent::init();
        require_once _base_ . '/lib/lib.json.php';
        $this->json = new Services_JSON();
        $this->acl_man = \FormaLms\lib\Forma::getAclManager();
        $this->model = new TransactionAlms();
        $this->permissions = [
            'view' => checkPerm('view', true, 'transaction', 'lms'),
            'mod' => checkPerm('mod', true, 'transaction', 'lms'),
            'del' => checkPerm('mod', true, 'transaction', 'lms'),
        ];
    }

    public function getPerm()
    {
        return [];
    }

    public function show()
    {
        if (isset($_GET['res'])) {
            $res = FormaLms\lib\Get::req('res', DOTY_STRING, '');

            switch ($res) {
                case 'ok':
                    UIFeedback::info(Lang::t('_UPDATE_OK', 'catalogue'));
                break;
                case 'err':
                    UIFeedback::error(Lang::t('_UPDATE_ERROR', 'catalogue'));
                break;
                default:
                break;
            }
        }

        $this->render('show', []);
    }

    public function getTransactionData()
    {
        //Datatable info
        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'date_creation');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'desc');

        $total_transaction = $this->model->getTotalTransaction();
        $array_transaction = $this->model->getTransaction($start_index, $results, $sort, $dir);

        $result = [
            'totalRecords' => $total_transaction,
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($array_transaction),
            'records' => $array_transaction, ];

        echo $this->json->encode($result);
    }

    public function mod()
    {
        $id_trans = FormaLms\lib\Get::req('id_trans', DOTY_INT, 0);

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=alms/transaction/show');
        }

        if (isset($_POST['save']) || isset($_POST['not_paid'])) {
            $product_to_activate = FormaLms\lib\Get::req('product', DOTY_MIXED, []);
            $id_user = FormaLms\lib\Get::req('id_user', DOTY_MIXED, 0);

            if ($this->model->saveTransaction($product_to_activate, $id_trans, $id_user)) {
                $this->model->controlActivation($id_trans, (isset($_POST['not_paid'])));

                $trans = $this->model->getTransactionInfo($id_trans);
                $products = $trans['product'];
                $trans['product'] = [];
                foreach ($product_to_activate as $key => $to_add) {
                    if ($to_add) {
                        $id = explode('_', $key);
                        foreach ($products as $product) {
                            if ($product['id_course'] == $id[0] && $product['id_date'] == $id[1] && $product['id_edition'] == $id[2]) {
                                $trans['product'][] = $product;
                            }
                        }
                    }
                }

                //TODO: EVT_OBJECT (§)
                //$event = new \appLms\Events\Transaction\TransactionPaidEvent($trans);
                //TODO: EVT_LAUNCH (&)
                //\appCore\Events\DispatcherManager::dispatch($event::EVENT_NAME, $event);

                //if($event->getRes()) { Util::jump_to('index.php?r=alms/transaction/show&res=ok'); }
                if (true) {
                    Util::jump_to('index.php?r=alms/transaction/show&res=ok');
                } else {
                    Util::jump_to('index.php?r=alms/transaction/show&res=err');
                }
            }
            Util::jump_to('index.php?r=alms/transaction/show&res=err');
        }

        $transaction_info = $this->model->getTransactionInfo($id_trans);
        $user_info = $this->acl_man->getUser($transaction_info['id_user'], false);
        $user_info[ACL_INFO_USERID] = $this->acl_man->relativeId($user_info[ACL_INFO_USERID]);

        require_once _base_ . '/lib/lib.table.php';

        $tb = new Table(false, Lang::t('_DETAILS', 'transaction'), Lang::t('_DETAILS', 'transaction'));

        $ts = ['', '', 'min-cell', 'image'];
        $th = [Lang::t('_CODE', 'transaction'),
                        Lang::t('_NAME', 'transaction'),
                        Lang::t('_PRICE', 'transaction'),
                        Lang::t('_MARK_AS_PAID', 'transaction'), ];

        $tb->setColsStyle($ts);
        $tb->addHead($th);

        foreach ($transaction_info['product'] as $product_info) {
            $tb->addBody([$product_info['code'],
                                $product_info['name'],
                                $product_info['price'],
                                Form::getInputCheckbox('product_' . $product_info['id_course'] . '_' . $product_info['id_date'] . '_' . $product_info['id_edition'], 'product[' . $product_info['id_course'] . '_' . $product_info['id_date'] . '_' . $product_info['id_edition'] . ']', 1, $product_info['activated'], ($product_info['activated'] ? ' disabled="disabled"' : '')), ]);
        }

        $this->render('mod', ['transaction_info' => $transaction_info,
                                    'user_info' => $user_info,
                                    'tb' => $tb,
                                    'id_trans' => $id_trans, ]);
    }
}
