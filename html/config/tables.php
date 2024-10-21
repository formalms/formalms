<?php

return [
    'columnsacert' => [
        [
            'data' => 'id_certificate',
            'title' => 'id_certificate',
            'sortable' => false,
            'visible' => false,
            'searchable' => false],
        ['data' => 'idAssociation', 'title' => 'idAssociation', 'sortable' => false, 'visible' => false, 'searchable' => false],
        ['data' => 'on_date', 'title' => Lang::t('_DATE'), 'sortable' => true, 'visible' => true, 'searchable' => false,
            'render' => 'function(data){
                if (data !=  "" && data != "0000/00/00") {
                    d = new Date(data);
                    return d.toLocaleDateString();
                }
                return "";
            }'
        ],
        ['data' => 'code', 'title' => Lang::t('_CODE'), 'sortable' => true, 'visible' => true, 'searchable' => false],
        ['data' => 'name', 'title' => Lang::t('_CERTIFICATE_NAME', 'course'), 'sortable' => true, 'visible' => true, 'searchable' => true],
        ['data' => 'course_name', 'title' => Lang::t('_COURSES'), 'sortable' => true, 'visible' => true, 'searchable' => true,
            'render' => 'function(data){return data.split("|").join("<br>");}'
        ],
        ['data' => 'path_name', 'title' => Lang::t('_COURSEPATH'), 'sortable' => true, 'visible' => true, 'searchable' => true,
            'render' => 'function(data){return data.split("|").join("<br>");}'
        ],
        ['data' => 'cert_file', 'title' => Lang::t('_TAKE_A_COPY', 'certificate'),
            'sortable' => false, 'visible' => true, 'searchable' => false,
            'render' => 'function(data, type, row){
                title = (data !== "" ? "' . Lang::t('_DOWNLOAD', 'certificate') . '" : "' . Lang::t('_GENERATE', 'certificate') . '");
                return "<a id=\"pdf_download\" class=\"ico-wt-sprite subs_pdf\" href=\"?r=mycertificate/downloadMetaCert&"
                    + "id_certificate=" + row.id_certificate
                    + "&aggCert=1"
                    + "&id_association=" + row.idAssociation + "\" title=\"" + title + "\">"
                    + title + "</a>";
            }'
        ]
    ]
];

?>