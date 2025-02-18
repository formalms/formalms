<?php

return [
    'columnsacert' => [
        [
            'data' => 'id_certificate',
            'title' => 'id_certificate',
            'sortable' => false,
            'visible' => false,
            'searchable' => false
        ],
        ['data' => 'idAssociation', 'title' => 'idAssociation', 'sortable' => false, 'visible' => false, 'searchable' => false],
        [
            'data' => 'on_date',
            'title' => Lang::t('_DATE'),
            'sortable' => true,
            'visible' => true,
            'searchable' => false,
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
        [
            'data' => 'course_name',
            'title' => Lang::t('_COURSES'),
            'sortable' => true,
            'visible' => true,
            'searchable' => true,
            'render' => 'function(data){return data.split("|").join("<br>");}'
        ],
        [
            'data' => 'path_name',
            'title' => Lang::t('_COURSEPATH'),
            'sortable' => true,
            'visible' => true,
            'searchable' => true,
            'render' => 'function(data){return data.split("|").join("<br>");}'
        ],
        [
            'data' => 'cert_file',
            'title' => Lang::t('_TAKE_A_COPY', 'certificate'),
            'sortable' => false,
            'visible' => true,
            'searchable' => false,
            'render' => 'function(data, type, row){
                title = (data !== "" ? "' . Lang::t('_DOWNLOAD', 'certificate') . '" : "' . Lang::t('_GENERATE', 'certificate') . '");
                return "<a id=\"pdf_download\" class=\"ico-wt-sprite subs_pdf\" href=\"?r=mycertificate/downloadMetaCert&"
                    + "id_certificate=" + row.id_certificate
                    + "&aggCert=1"
                    + "&id_association=" + row.idAssociation + "\" title=\"" + title + "\">"
                    + title + "</a>";
            }'
        ]
    ],
    'certificate_users' => [
        'columns' => [
            [
                "data" => 'id_user',
                "title" => 'id_user',
                "sortable" => false,
                "visible" => false,
                "searchable" => false,
            ],
            [
                "data" => 'id_certificate',
                "title" => 'id_certificate',
                "sortable" => false,
                "visible" => false,
                "searchable" => false,
            ],
            [
                "data" => 'username',
                "title" => Lang::t('_USERNAME', 'standard'),
                "sortable" => true,
                "searchable" => true,
                "search_field" => 'text'
            ],
            [
                "data" => 'lastname',
                "title" => Lang::t('_LASTNAME', 'standard'),
                "sortable" => true,
            ],
            [
                "data" => 'firstname',
                "title" => Lang::t('_NAME', 'standard'),
                "sortable" => true,
            ],
            [
                "data" => 'status',
                "title" => Lang::t('_STATUS', 'standard'),
                "sortable" => true,
            ],
            [
                "data" => 'name_certificate',
                "title" => Lang::t('_CERTIFICATE_REPORT', 'certificate'),
                "sortable" => true
            ],
            [
                "data" => 'date_complete',
                "title" => Lang::t('_DATE_END', 'standard'),
                "sortable" => true,
                "type" => 'date-euro',
            ], // TBD converting to local time
            [
                "data" => 'on_date',
                "title" => Lang::t('_RELASE_DATE', 'certificate'),
                "sortable" => true,
                "type" => 'date-euro'
            ], // TBD converting to local time
            [
                "data" => 'cell_down_gen',
                "title" => \FormaLms\lib\Get::sprite('subs_pdf', Lang::t('_TITLE_VIEW_CERT', 'certificate')),
                "sortable" => true,
                "searchable" => false
            ],
            [
                "data" => 'cell_del_cert',
                "title" => \FormaLms\lib\Get::sprite('subs_del', Lang::t('_DEL', 'certificate')),
                "sortable" => false,
                "searchable" => false,
            ]
        ],
    ],
    'mycert_columns' => [
        ['key' => 'on_date', 'label' => Lang::t('_DATE'), 'className' => 'min-cell', 'sortable' => true],
        ['key' => 'code', 'label' => Lang::t('_CODE')],
        ['key' => 'course_name', 'label' => Lang::t('_COURSE', 'certificate')],
        ['key' => 'cert_name', 'label' => Lang::t('_CERTIFICATE_NAME', 'course')],
        ['key' => 'date_complete', 'label' => Lang::t('_DATE_COMPLETE', 'certificate')],
        ['key' => 'download', 'label' => Lang::t('_TAKE_A_COPY', 'certificate'), 'className' => 'img-cell'],
  
    ]
];
