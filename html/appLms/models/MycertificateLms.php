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

require_once Forma::inc(_lms_ . '/lib/lib.certificate.php');

class MycertificateLms extends Model
{
    protected $certificate;

    public $id_user;

    protected $aggrCertsArr;

    public function __construct($id_user)
    {
        $this->id_user = (int) $id_user;
        $this->certificate = new Certificate();
        parent::__construct();
    }

    public function loadMyCertificates($pagination = false, $count = false)
    {
        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'year');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'desc');

        $filter = ['id_user' => $this->id_user];
        $myCertificates = $this->certificate->getAssignment($filter, $pagination, $count);

        if ($count) {
            return $myCertificates;
        }

        $data = [];
        foreach ($myCertificates as $cert) {
            if ($this->certificate->certificateAvailableForUser($cert['id_certificate'], $cert['id_course'], $this->id_user)) {
                $download = '<a class="ico-wt-sprite subs_pdf" id="pdf_download" href="?r=mycertificate/'
                    . 'downloadCert&id_certificate=' . $cert['id_certificate'] . '&id_course=' . $cert['id_course'] . '" '
                    . ' title="' . (isset($cert['on_date']) ? Lang::t('_DOWNLOAD', 'certificate') : Lang::t('_GENERATE', 'certificate')) . '"><span>'
                    . (isset($cert['on_date']) ? Lang::t('_DOWNLOAD', 'certificate') : Lang::t('_GENERATE', 'certificate')) . '</span></a>';

                $data[] = [
                    'on_date' => substr($cert['on_date'], 0, 10),
                    'code' => $cert['code'],
                    'course_name' => $cert['course_name'],
                    'cert_name' => $cert['cert_name'],
                    'date_complete' => $cert['date_complete'],
                    'download' => $download,
                ];
            }
        }

        if ($order = $_REQUEST['order']) {
            $sort_index = $order[0]['column'];

            $fields = [
                'year',
                'code',
                'course_name',
                'cert_name',
                'date_complete',
                'preview',
                'download',
            ];

            $sort = $fields[$sort_index];
            $dir = $order[0]['dir'];
        }

        usort($data, function ($a, $b) use ($sort, $dir) {
            return $dir == 'desc' ? strcmp($b[$sort], $a[$sort]) : strcmp($a[$sort], $b[$sort]);
        });

        $data_to_display = [];
        for ($i = $startIndex; $i < ($startIndex + $results) && $i < count($data); ++$i) {
            $data_to_display[] = array_values($data[$i]);
        }

        return $data_to_display;
    }

    public function countMyCertificates()
    {
        $filter = ['id_user' => $this->id_user];

        return $this->certificate->countAssignment($filter);
    }

    /**
     * In this funct. we need to select all the aggr. certs that has been released or not.
     * The cert. has been released -> there's an entry in the aggr. certs. assignment with the user and id cert.
     *
     * From the user, get all assoc. -> from all assoc, get ids of cert. distinct
     * from the id cert., get all courses and see if they are completed
     *
     *
     * Return an array of all certs available
     */
    public function getMyMetaCertificates()
    {
        $q = "SELECT 
                c.id_certificate, 
                aca.idAssociation, 
                c.code, 
                c.name, 
                IF(aca.on_date IS NOT NULL, DATE_FORMAT(aca.on_date,'%Y/%m/%d'), '') as 'on_date',
                aca.cert_file, 
                '' as 'course_name', 
                cp.path_name
            FROM %lms_certificate AS c
            INNER JOIN %lms_aggregated_cert_assign aca ON c.id_certificate = aca.idCertificate
            INNER JOIN %lms_aggregated_cert_coursepath acc ON aca.idAssociation = acc.idAssociation AND acc.idUser = acc.idUser
            INNER JOIN %lms_coursepath cp ON acc.idCoursePath = cp.id_path
            WHERE aca.idUser = " . (int) $this->id_user;

        $rs = sql_query($q);
        $currentIdCert = 0;
        $index = 0;
        $arrAggregatedCerts = [];
        foreach ($rs as $row) {
            if ($currentIdCert !== $row['id_certificate']) {
                $arrAggregatedCerts[$index] = $row;
                ++$index;
            } else {
                $arrAggregatedCerts[$index - 1]['path_name'] = $arrAggregatedCerts[$index - 1]['path_name'] . ' | ' . $row['path_name'];
            }
            $currentIdCert = $row['id_certificate'];
        }

        $q = "SELECT 
                cu.idUser > 0 AS completed,
                c.id_certificate, 
                aca.idAssociation, 
                c.code, 
                c.name, 
                IF(aca.on_date IS NOT NULL, DATE_FORMAT(aca.on_date,'%Y/%m/%d'), '') as 'on_date',
                aca.cert_file, 
                cc.name as 'course_name', 
                '' as 'path_name'
            FROM %lms_certificate AS c
            INNER JOIN %lms_aggregated_cert_assign aca ON c.id_certificate = aca.idCertificate
            INNER JOIN %lms_aggregated_cert_course acc ON aca.idAssociation = acc.idAssociation
            INNER JOIN %lms_course cc ON acc.idCourse = cc.idCourse AND acc.idUser = aca.idUser
            LEFT JOIN %lms_courseuser cu ON cu.idCourse = acc.idCourse AND cu.idUser = acc.idUser AND cu.status = 2 AND cu.date_complete IS NOT NULL
            WHERE aca.idUser = " . (int) $this->id_user . ' ORDER BY completed, c.id_certificate ASC';
        $rs = sql_query($q);
        $currentIdCert = 0;
        foreach ($rs as $row) {
            if (!$row['completed'] && empty($row['cert_file'])) {
                continue;
            }
            if ($currentIdCert != $row['id_certificate']) {
                $arrAggregatedCerts[$index] = $row;
                ++$index;
            } else {
                $arrAggregatedCerts[$index - 1]['course_name'] = $arrAggregatedCerts[$index - 1]['course_name'] . ' | ' . $row['course_name'];
            }
            $currentIdCert = $row['id_certificate'];
        }

        return $arrAggregatedCerts;
    }

    public function countAggrCertsToRelease()
    {
        $r = sql_fetch_row(
            sql_query('SELECT count(*) as tot from %lms_aggregated_cert_assign where idUser =' . $this->id_user . ' AND cert_file = \'\'')
        );

        return $r[0];
    }

    public function countMyMetaCertificates()
    {
        $r = sql_fetch_row(sql_query('SELECT count(*) as tot from %lms_aggregated_cert_assign where idUser =' . $this->id_user));

        return $r[0];
    }

    public function countMyMetaCertsReleased()
    {
        $r = sql_fetch_row(
            sql_query('SELECT count(*) as tot from %lms_aggregated_cert_assign where idUser =' . $this->id_user . ' AND cert_file <> \'\'')
        );

        return $r[0];
    }
}
