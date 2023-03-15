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

defined('IN_FORMA') or exit('Direct access is forbidden.');

class CartLmsController extends LmsController
{
    public $name = 'catalog';

    private $path_course = '';

    protected $_default_action = 'show';

    public $model;
    public $json;
    public $acl_man;

    public function isTabActive($tab_name)
    {
        return false;
    }

    public function init()
    {
        YuiLib::load('base,tabview');
        Lang::init('course');
        $this->path_course = $GLOBALS['where_files_relative'] . '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . '/';
        $this->model = new CartLms();

        require_once _base_ . '/lib/lib.json.php';
        $this->json = new Services_JSON();

        $this->acl_man = &Docebo::user()->getAclManager();

        Util::get_css(FormaLms\lib\Get::rel_path(_base_) . '/appLms/views/cart/cart.css', true, true);
    }

    protected function checkCartTransaction($id_trans, $cart_id)
    {
        $req_code = md5($id_trans . $cart_id);
        $ses_code = md5($this->session->get('cart_transaction') . $this->session->get('cart_id'));

        $res = ($req_code === $ses_code ? true : false);

        return $res;
    }

    public function getPaypalUrl()
    {
        return 'https://www.' . (FormaLms\lib\Get::sett('paypal_sandbox', 'off') === 'off' ? '' : 'sandbox.') . 'paypal.com/cgi-bin/webscr';
    }

    public function show()
    {
        $new = true;
        $total_price = $this->model->getTotalPrice();

        $id_trans = FormaLms\lib\Get::req('id_transaction', DOTY_INT, 0);
        $cart_id = FormaLms\lib\Get::gReq('cart', DOTY_MIXED, 0);

        require_once _lms_ . '/admin/models/TransactionAlms.php';
        $trman = new TransactionAlms();

        if (!empty($_GET['cancel'])) {
            $new = false;
        }

        if (!$new && !$this->checkCartTransaction($id_trans, $cart_id)) {
            UIFeedback::error(Lang::t('_INVALID_TRANSACTION', 'cart'));
        } elseif (isset($_GET['cancel']) && $_GET['cancel'] == 1) {
            $trman->deleteTransaction($id_trans, true);

            UIFeedback::error(Lang::t('_TRANSACTION_ABORTED', 'cart'));
        } elseif (isset($_GET['ok']) && $_GET['ok'] == 1) {
            UIFeedback::info(Lang::t('_COURSE_ACTIVATION_SUCCESS', 'cart'));
        }

        if (isset($_GET['error']) && $_GET['error'] == 1) {
            $new = false;
            UIFeedback::error(Lang::t('_TRANSACTION_CREATION_ERROR', 'cart'));
        }

        if ($new) {
            $this->session->set('cart_id', time() . substr(uniqid('cart', true), 0, 20));
            $this->session->save();
        }

        $this->render('show', ['total_price' => $total_price, 'paypal_url' => $this->getPaypalUrl()]);
    }

    public function paypalNotifyTask()
    {
        $debug = false;
        $log = '';

        if (!isset($GLOBALS['orig_post'])) {
            return false;
        }

        if ($debug) {
            file_put_contents('paypal.txt', print_r($_POST, true) . print_r($_GET, true), FILE_APPEND);
        }

        $url_parsed = parse_url($this->getPaypalUrl());

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';

        foreach ($GLOBALS['orig_post'] as $key => $value) { // not filtered POST values
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        unset($GLOBALS['orig_post']);

        // post back to PayPal system to validate
        // use of HTTP/1.1 protocol as requested by paypal since 13-10-2013
        $header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= 'Host: ' . $url_parsed['host'] . "\r\n";
        $header .= 'Content-Length: ' . strlen($req) . "\r\n";
        $header .= "Connection: close\r\n\r\n";
        $fp = fsockopen('ssl://' . $url_parsed['host'], 443, $errno, $errstr, 30);

        // assign posted variables to local variables
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $txn_id = $_POST['txn_id'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];

        if (!$fp) {
            // HTTP ERROR
        } else {
            fputs($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets($fp, 1024);
                $res = trim($res);
                if (strcmp($res, 'VERIFIED') == 0) {
                    //require_once (_lms_.'/admin/modules/subscribe/subscribe.php');
                    require_once _lms_ . '/admin/models/TransactionAlms.php';

                    $trman = new TransactionAlms();
                    $man_course = new Man_Course();
                    $transaction_info = $trman->getTransactionInfo($item_number);
                    $log .= print_r($transaction_info, true) . "\n\n";
                    $id_user = $transaction_info['id_user'];

                    $total_price = 0;
                    foreach ($transaction_info['product'] as $prod) {
                        $total_price += $prod['price'];
                    }

                    $ok = true;
                    // check that receiver_email is your Primary PayPal email
                    $ok = ($receiver_email == FormaLms\lib\Get::sett('paypal_mail', '') ? $ok : false);
                    // check the payment_status is Completed
                    $ok = ($payment_status == 'Completed' ? $ok : false);
                    // check that payment_amount/payment_currency are correct
                    $ok = ($payment_amount == $total_price ? $ok : false);
                    $ok = ($payment_currency == FormaLms\lib\Get::sett('paypal_currency', 'EUR') ? $ok : false);

                    if ($ok) { // process payment
                        $to_activate = [];
                        foreach ($transaction_info['product'] as $prod) { // activate subscription requests
                            if ($prod['activated'] == 0) {
                                $id_course = $prod['id_course'];
                                $edition_id = $prod['id_edition'];
                                $id_date = $prod['id_date'];

                                $to_activate[$id_course . '_' . $id_date . '_' . $edition_id] = '1';
                            }
                        }
                        if (!empty($to_activate)) {
                            $trman->saveTransaction($to_activate, $item_number, $id_user, true);
                        }
                    } elseif ($debug) {
                        $log .= ($receiver_email == FormaLms\lib\Get::sett('paypal_mail', '') ? '' : 'wrong receiver_email (' . $receiver_email . ' != ' . FormaLms\lib\Get::sett('paypal_mail', '') . ")\n");
                        $log .= ($payment_status == 'Completed' ? '' : 'wrong payment status (' . $payment_status . ")\n");
                        $log .= ($payment_amount == $transaction_info['price'] ? '' : 'wrong price (' . $payment_amount . ' != ' . $total_price . ")\n");
                        $log .= ($payment_currency == FormaLms\lib\Get::sett('paypal_currency', 'EUR') ? '' : 'wrong payment currency (' . $payment_currency . ' != ' . FormaLms\lib\Get::sett('paypal_currency', 'EUR') . ")\n");
                    }
                } elseif (strcmp($res, 'INVALID') == 0) {
                    // log for manual investigation
                    // echo "INVALID: ".$res;
                }
            }
            fclose($fp);
        }

        if ($debug) {
            file_put_contents('paypal.txt', "\n" . $log . $url_parsed['host'] . ' ' . $req . "\n" . $res . "\n----------------\n\n", FILE_APPEND);
        }
        exit();
    }

    public function getCartList()
    {
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'name');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');

        $course_list = $this->model->getCartList();

        $result = [
            'totalRecords' => count($course_list),
            'startIndex' => 0,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => count($course_list),
            'results' => count($course_list),
            'records' => $course_list,
        ];

        echo $this->json->encode($result);
    }

    public function emptyCart()
    {
        $this->session->set('lms_cart', []);
        $this->session->save();

        $result['success'] = true;

        echo $this->json->encode($result);
    }

    public function delSelectedElement()
    {
        $del_list = FormaLms\lib\Get::req('elements', DOTY_MIXED, '');
        $del_list = explode(',', $del_list);

        $cart = $this->session->get('lms_cart', []);

        foreach ($del_list as $id) {
            $id_details = explode('_', $id);

            if ($id_details[1] != 0) {
                unset($cart[$id_details[0]]['classroom'][$id_details[1]]);
                if (empty($cart[$id_details[0]]['classroom'])) {
                    unset($cart[$id_details[0]]);
                }
            } elseif ($id_details[2] != 0) {
                unset($cart[$id_details[0]]['edition'][$id_details[2]]);
                if (empty($cart[$id_details[0]]['edition'])) {
                    unset($cart[$id_details[0]]);
                }
            } else {
                unset($cart[$id_details[0]]);
            }
        }

        $this->session->set('lms_cart', $cart);
        $this->session->save();

        require_once _lms_ . '/lib/lib.cart.php';

        $result = [
            'success' => true,
            'cart_element' => '' . Learning_Cart::cartItemCount() . '',
            'price' => $this->model->getTotalPrice(),
        ];

        echo $this->json->encode($result);
    }

    public function makeOrder()
    {
        $wire = FormaLms\lib\Get::req('wire', DOTY_INT, 0);

        $cart = $this->session->get('lms_cart');
        require_once _lms_ . '/lib/lib.cart.php';

        if (Learning_Cart::cartItemCount() > 0) {
            $id_trans = $this->model->createTransaction();
        } else {
            $id_trans = false;
        }

        if ($id_trans === false) {
            $result = [
                'success' => false,
                'message' => UIFeedback::error(Lang::t('_ERROR_CREATE_TRANS', 'catalogue'), true),
            ];
        } else {
            $course_info = $this->model->getCartList(true);
            $total_price = 0;

            foreach ($cart as $id_course => $extra) {
                $docebo_course = new DoceboCourse($id_course);

                require_once _lms_ . '/admin/models/SubscriptionAlms.php';

                $level_idst = &$docebo_course->getCourseLevel($id_course);

                if (count($level_idst) == 0 || $level_idst[1] == '') {
                    $level_idst = &$docebo_course->createCourseLevel($id_course);
                }

                $waiting = 1;

                $this->acl_man->addToGroup($level_idst[3], Docebo::user()->getIdSt());

                if (isset($extra['classroom'])) {
                    foreach ($extra['classroom'] as $id_date) {
                        $model = new SubscriptionAlms($id_course, 0, $id_date);
                        if (!$model->subscribeUser(Docebo::user()->getIdSt(), 3, $waiting)) {
                            $this->acl_man->removeFromGroup($level_idst[3], Docebo::user()->getIdSt());
                        } elseif ($this->model->addTransactionCourse($id_trans, $id_course, $id_date, 0, $course_info[$id_course . '_' . $id_date . '_0'])) {
                            $currentCart = $this->session->get('lms_cart');
                            unset($currentCart[$id_course]['classroom'][$id_date]);
                            $this->session->set('lms_cart', $currentCart);
                            $this->session->save();

                            $query = 'UPDATE %lms_courseuser'
                                . " SET status = '-2'"
                                . ' WHERE idUser = ' . Docebo::user()->getIdSt()
                                . ' AND idCourse = ' . $id_course;

                            sql_query($query);

                            $total_price += $course_info[$id_course . '_' . $id_date . '_0']['price'];
                        }
                    }
                } elseif (isset($extra['edition'])) {
                    foreach ($extra['edition'] as $id_edition) {
                        $model = new SubscriptionAlms($id_course, $id_edition, 0);
                        if (!$model->subscribeUser(Docebo::user()->getIdSt(), 3, $waiting)) {
                            $this->acl_man->removeFromGroup($level_idst[3], Docebo::user()->getIdSt());
                        } elseif ($this->model->addTransactionCourse($id_trans, $id_course, 0, $id_edition, $course_info[$id_course . '_0_' . $id_edition])) {
                            $currentCart = $this->session->get('lms_cart');
                            unset($currentCart[$id_course]['edition'][$id_edition]);
                            $this->session->set('lms_cart', $currentCart);
                            $this->session->save();

                            $query = 'UPDATE %lms_courseuser'
                                . " SET status = '-2'"
                                . ' WHERE idUser = ' . Docebo::user()->getIdSt()
                                . ' AND idCourse = ' . $id_course;

                            sql_query($query);

                            $total_price += $course_info[$id_course . '_0_' . $id_edition]['price'];
                        }
                    }
                } else {
                    $model = new SubscriptionAlms($id_course, 0, 0);
                    if (!$model->subscribeUser(Docebo::user()->getIdSt(), 3, $waiting)) {
                        $this->acl_man->removeFromGroup($level_idst[3], Docebo::user()->getIdSt());
                    } elseif ($this->model->addTransactionCourse($id_trans, $id_course, 0, 0, $course_info[$id_course . '_0_0'])) {
                        $currentCart = $this->session->get('lms_cart');
                        unset($currentCart[$id_course]);
                        $this->session->set('lms_cart', $currentCart);
                        $this->session->save();

                        $query = 'UPDATE %lms_courseuser'
                            . " SET status = '-2'"
                            . ' WHERE idUser = ' . Docebo::user()->getIdSt()
                            . ' AND idCourse = ' . $id_course;

                        sql_query($query);

                        $total_price += $course_info[$id_course . '_0_0']['price'];
                    }
                }
            }

            require_once _lms_ . '/lib/lib.cart.php';
            if (Learning_Cart::cartItemCount() == 0) {
                $this->session->set('lms_cart', []);
            }

            $this->session->set('cart_transaction', $id_trans);
            $this->session->save();
            $result = [
                'success' => true,
                'message' => UIFeedback::info(Lang::t('_TRANS_CREATED', 'catalogue'), true),
                'id_transaction' => $id_trans,
                'total_price' => $total_price,
                'link' => FormaLms\lib\Get::site_url() . _folder_lms_ . '/index.php?r=cart/show&id_transaction=' . $id_trans . '&cart=' . $this->session->get('cart_id'),
            ];
        }

        $course = $course_info[$id_course . '_0_0'];
        $method = $wire ? Lang::t('_WIRE_PAYMENT', 'cart') : Lang::t('_PAYPAL', 'cart');
        $this->sendPurchaseNotification($course, $id_trans, $total_price, $method);

        if ($wire) {
            if ($result['success']) {
                Util::jump_to('index.php?r=cart/wireInfo&id_transaction=' . $id_trans);
            }
            Util::jump_to('index.php?r=cart/show&error=1');
        } else {
            echo $this->json->encode($result);
        }
    }

    public function sendPurchaseNotification($course, $id_trans, $total_price, $method)
    {
        if ($purchase_user = FormaLms\lib\Get::sett('purchase_user')) {
            // Send Message
            require_once _base_ . '/lib/lib.eventmanager.php';

            $user_data = $this->acl_man->getUser(Docebo::user()->getIdSt(), false);
            $t = new TransactionAlms();
            $transaction_info = $t->getTransactionInfo($id_trans);
            $date = null;
            if ($transaction_info['date_creation']) {
                $date = date('d-m-Y H:i', strtotime($transaction_info['date_creation']));
            }

            $username = str_replace('/', '', $user_data[1]);

            require_once Forma::inc(_adm_ . '/lib/lib.field.php');
            $extra_field = new FieldList();
            $fields_arr = $extra_field->getUserFieldEntryData($user_data[0]);

            $fields = '<ul>';
            foreach ($fields_arr as $k => $f) {
                list($name) = sql_fetch_row(sql_query("SELECT `translation` FROM core_field WHERE idField = $k AND lang_code = '" . getLanguage() . "'"));
                $fields .= "<li>$name: <strong>$f</strong></li>";
            }
            $fields .= '</ul>';

            $array_subst = [
                '[url]' => FormaLms\lib\Get::site_url(),
                '[userid]' => $user_data[0],
                '[username]' => $username,
                '[firstname]' => $user_data[2],
                '[lastname]' => $user_data[3],
                '[email]' => $user_data[5],
                '[course]' => $course['name'],
                '[id_transaction]' => $id_trans,
                '[date_transaction]' => $date,
                '[price]' => $total_price,
                '[method]' => $method,
                '[fields]' => $fields,
            ];

            $e_msg = new EventMessageComposer();
            $e_msg->setSubjectLangText('email', '_PURCHASE_COURSE_MAIL_SBJ', false);
            $e_msg->setBodyLangText('email', '_PURCHASE_COURSE_MAIL_TEXT', $array_subst);

            createNewAlert(
                'PurchaseCourse',
                'subscribe',
                'insert',
                '1',
                "User $username purchased the course " . $course['name'],
                [$purchase_user], // Username or idst
                $e_msg
            );
        }
    }

    public function wireInfo()
    {
        require_once _lms_ . '/admin/models/TransactionAlms.php';
        $model = new TransactionAlms();

        $id_trans = FormaLms\lib\Get::req('id_transaction', DOTY_INT, 0);

        $transaction_info = $model->getTransactionInfo($id_trans);

        $total_price = 0;

        foreach ($transaction_info['product'] as $product_info) {
            $total_price += $product_info['price'];
        }

        if ($id_trans != 0) {
            UIFeedback::info(Lang::t('_TRANS_CREATED', 'cart'));
        }

        $this->render('wire', ['transaction_info' => $transaction_info, 'total_price' => $total_price]);
    }
}
