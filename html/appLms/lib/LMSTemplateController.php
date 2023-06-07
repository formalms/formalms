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

require_once _lib_ . '/TemplateController.php';
require_once _lms_ . '/lib/LMSTemplateModel.php';
require_once _lms_ . '/lib/lib.middlearea.php';

final class LMSTemplateController extends TemplateController
{
    private $model;

    protected function __construct()
    {
        $this->model = new LMSTemplateModel();

        $this->setLayout($this->model->selectLayout());
        $this->templateFolder = _folder_lms_;

        parent::__construct();
    }

    public function show()
    {
        $this->showLogo();
        $this->showMenu();
        $this->showCart();
        $this->showProfile();
        $this->showHelpDesk(); // Temporary solution before helpdesk refactoring.

        parent::show();
    }

    private function showLogo()
    {
        $this->render('logo', 'logo', [
            'user' => $this->model->getUser(), 'logo' => $this->model->getLogo(), 'currentPage' => $this->model->getCurrentPage(), 'homePage' => $this->model->getHomePage(),
        ]);
    }

    private function notGeneratedCertificates()
    {
        $id_user = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();
        $model = new MycertificateLms($id_user);
        $availables = 0;
        $certificates = $model->loadMyCertificates(false, false);

        foreach ($certificates as $cert) {
            if ($cert[0] == '0000-00-00' || $cert[0] == '') { // $cert['on_date']
                ++$availables;
            }
        }

        return $availables + $model->countAggrCertsToRelease();
    }

    private function showMenu()
    {
        $ma = new Man_MiddleArea();

        $this->render('menu', 'main-menu', [
            'user' => $this->model->getUser(), 'menu' => $this->model->getMenu(), 'currentPage' => $this->model->getCurrentPage(), 'perm_certificate' => $ma->currentCanAccessObj('mo_7'), 'notGeneratedCertificates' => $this->notGeneratedCertificates(),  'adminRoles' => [ADMIN_GROUP_GODADMIN, ADMIN_GROUP_ADMIN],
        ]);
    }

    private function showCart()
    {
        $this->render('cart', 'cart', [
            'user' => $this->model->getUser(), 'cart' => $this->model->getCart(), 'currentPage' => $this->model->getCurrentPage(),
        ]);
    }

    private function showProfile()
    {
        $this->render('profile', 'profile', [
            'user' => $this->model->getUser(), 'profile' => $this->model->getProfile(), 'credits' => $this->model->getCredits(), 'career' => $this->model->getCareer(), 'subscribeCourse' => $this->model->getSubscribeCourse(), 'news' => $this->model->getNews(), 'languages' => $this->model->getLanguages(), 'currentPage' => $this->model->getCurrentPage(),
        ]);
    }

    private function showHelpDesk()
    {
        // Temporary solution before helpdesk refactoring.
        $this->render('helpdesk_modal', 'helpdesk', [
            'user' => $this->model->getUser(), 'userDetails' => $this->model->getUserDetails(), 'email' => $this->model->getHelpDeskEmail(), 'currentPage' => $this->model->getCurrentPage(), 'helpDeskEmail' => $this->model->getUserDetails()[ACL_INFO_EMAIL],
        ]);
    }
}
