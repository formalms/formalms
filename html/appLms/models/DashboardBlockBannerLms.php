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

use Symfony\Component\Uid\Uuid;

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Class DashboardBlockBannerLms.
 */
class DashboardBlockBannerLms extends DashboardBlockLms
{
    public function parseConfig($jsonConfig)
    {
        $this->parseBaseConfig($jsonConfig);
    }

    public function getAvailableTypesForBlock()
    {
        return self::ALLOWED_TYPES;
    }

    public function getForm()
    {
        $form = parent::getForm();

        array_push(
            $form,
            DashboardBlockForm::getFormItem($this, 'cover', DashboardBlockForm::FORM_TYPE_IMAGE, false),
            DashboardBlockForm::getFormItem($this, 'video', DashboardBlockForm::FORM_TYPE_TEXT, false));

        return $form;
    }

    public function getViewData()
    {
        $this->parseVideoType();
        $data = $this->getCommonViewData();

        return $data;
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * @return string
     */
    public function getViewFile()
    {
        return $this->viewFile;
    }

    private function parseVideoType()
    {
        $data = $this->getData();

        if (isset($data['video']) && !isset($_POST['settings']) && !isset($_GET['dashboard'])) {
            $type = $this->determineVideoUrlType($data['video']);

            $data = array_merge($data, $type);

            $this->setData($data); // TEMP
        }
    }

    private function determineVideoUrlType($url)
    {
        $yt_rx = '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/';
        $has_match_youtube = preg_match($yt_rx, $url, $yt_matches);

        $vm_rx = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/';
        $has_match_vimeo = preg_match($vm_rx, $url, $vm_matches);

        $m4_rx = '/\.mp4/';
        $has_match_mp4 = preg_match($m4_rx, $url, $m4_matches);

        //Then we want the video id which is:
        if ($has_match_youtube) {
            $video_id = $yt_matches[5];
            $type = 'yt';
        } elseif ($has_match_vimeo) {
            $video_id = $vm_matches[5];
            $type = 'vimeo';
        } elseif ($has_match_mp4) {
            $video_id = $url;
            $type = 'mp4';
        } else {
            $video_id = 0;
            $type = 'none';
        }

        $data['video_uuid'] = Uuid::v4()->toRfc4122();
        $data['video_id'] = $video_id;
        $data['video_type'] = $type;

        return $data;
    }

    public function getLink()
    {
        return '#';
    }

    public function getRegisteredActions()
    {
        return [];
    }
}
