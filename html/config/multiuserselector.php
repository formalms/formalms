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

return [
    'processors' => [
        'communication' => [
            'includes' => _lms_ . '/admin/models/CommunicationAlms.php',
            'className' => 'CommunicationAlms', 
            'returnType' => 'redirect'
        ],
        'adminmanager' => [
            'includes' => _adm_.'/models/AdminmanagerAdm.php',
            'className' => 'AdminmanagerAdm',
            'returnType' => 'redirect'
        ],
        'lmsmenu' => [
            'includes' => _lms_ . '/admin/models/LmsMenuAlms.php',
            'className' => 'LmsMenuAlms', 
            'returnType' => 'redirect'
        ],
        'coursesubscription' => [
            'includes' => 'FormaLms\lib\Services\Courses\\' ,
            'className' => 'CourseSubscriptionService',
            'returnType' => 'render',
            'returnView' => 'level',
            'subFolderView' => 'subscription',
            'additionalPaths' => [_lms_.'/admin/views'],
            'useNamespace' =>  true
        ],
        'multiplecoursesubscription' => [
            'includes' => 'FormaLms\lib\Services\Courses\\' ,
            'className' => 'CourseSubscriptionService',
            'returnType' => 'render',
            'returnView' => 'multiple_subscription_2',
            'subFolderView' => 'subscription',
            'additionalPaths' => [_lms_.'/admin/views'],
            'useNamespace' =>  true
        ],
        'lmstab' => [
            'includes' => _lms_ . '/lib/lib.middlearea.php',
            'className' => 'Man_MiddleArea', 
            'returnType' => 'redirect'
        ],
        'lmsblock' => [
            'includes' => _lms_ . '/lib/lib.middlearea.php',
            'className' => 'Man_MiddleArea', 
            'returnType' => 'redirect'
        ],
        'dashboardsetting' => [
            'includes' => _adm_.'/models/DashboardsettingsAdm.php',
            'className' => 'DashboardsettingsAdm',
            'returnType' => 'redirect'
        ],
        'rule' => [
            'includes' => _lms_ . '/admin/models/EnrollrulesAlms.php',
            'className' => 'EnrollRulesAlms', 
            'returnType' => 'redirect'
        ],
        'aggregatedcertificate' => [
            'includes' => _lms_ . '/lib/lib.aggregated_certificate.php',
            'className' => 'AggregatedCertificate', 
            'returnType' => 'render', 
            'returnView' => 'associationCreate',
            'subFolderView' => 'aggregatedcertificate',
            'additionalPaths' => [_lms_.'/admin/views']
        ],
        'competence' => [
            'includes' => _adm_.'/models/CompetencesAdm.php',
            'className' => 'CompetencesAdm', 
            'returnType' => 'render', 
            'returnView' => 'users_assign',
            'subFolderView' => 'competences',
            'additionalPaths' => [_adm_.'/views']
        ],
        'role' => [
            'includes' => _adm_.'/models/FunctionalrolesAdm.php',
            'className' => 'FunctionalrolesAdm', 
            'returnType' => 'redirect'
            ],
        'group' => [
            'includes' => _adm_.'/models/GroupmanagementAdm.php',
            'className' => 'GroupmanagementAdm', 
            'returnType' => 'redirect'
            ],
        'coursepath' => [
            'includes' => _lms_ . '/admin/models/SubscriptionAlms.php',
            'className' => 'SubscriptionAlms', 
            'returnType' => 'redirect'
            ],
        'organization' => [
            'includes' => _lms_ . '/modules/organization/orglib.php',
            'className' => 'OrgDirDb', 
            'returnType' => 'redirect'
            ],
        'newsletter' => [
            'includes' => 'FormaLms\lib\Services\Newsletters\\' ,
            'className' => 'NewsletterService',
            'returnType' => 'redirect',
            'useNamespace' =>  true
        ],
        'reportuser' => [
            'includes' => 'FormaLms\lib\Services\Reports\\' ,
            'className' => 'ReportService',
            'returnType' => 'redirect',
            'useNamespace' =>  true
        ],
        'reportschedule' => [
            'includes' => 'FormaLms\lib\Services\Reports\\' ,
            'className' => 'ReportScheduleService',
            'returnType' => 'redirect',
            'useNamespace' =>  true
        ],
        'newslettercourse' => [
            'includes' => 'FormaLms\lib\Services\Newsletters\\' ,
            'className' => 'NewsletterService',
            'returnType' => 'redirect',
            'useNamespace' =>  true
        ],
        'advicecourse' => [
            'includes' => 'FormaLms\lib\Services\Advices\\' ,
            'className' => 'AdviceService',
            'returnType' => 'redirect',
            'useNamespace' =>  true
        ],
        'message' => [
            'includes' => _adm_.'/lib/lib.message.php' ,
            'className' => 'MessageModule',
            'returnType' => 'redirect',
        ],
        'orgnode' => [
            'includes' => _adm_.'/models/UsermanagementAdm.php',
            'className' => 'UsermanagementAdm', 
            'returnType' => 'redirect'
            ],
        'learninggroup' => [
            'includes' => 'FormaLms\lib\Services\Courses\\',
            'className' => 'LearningGroupService', 
            'returnType' => 'redirect',
            'useNamespace' =>  true
            ],
        'catalogue' => [
            'includes' => 'FormaLms\lib\Services\Courses\\',
            'className' => 'CatalogueService', 
            'returnType' => 'redirect',
            'useNamespace' =>  true
            ],
    ],
    "use_filter" => [
        "organization" => "course",
        "learninggroup" => "course",
        "newslettercourse" => "course",
        "advicecourse" => "course",
        "message" => "message"
        ],
    
];