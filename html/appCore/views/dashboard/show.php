<div class="yui-g">
    <div class="yui-u first">
        <div class="inline_block_big">
            <h2 class="heading"><?php echo Lang::t('_QUICK_LINKS', 'dashboard'); ?></h2>
            <div class="content">
                <div class="yui-u first">
                    <?php
                    $_can_view_block = (bool)($permissions['view_user'] && ($permissions['view_user'] || $permissions['add_user'] || $permissions['mod_user']));
                    if ($_can_view_block):
                        ?>
                        <div class="block_spacer">
                            <h3><?php echo Lang::t('_USERS', 'standard'); ?></h3>
                            <ul class="link_list">
                                <?php if ($permissions['view_user']): ?>
                                    <li>
                                        <a id="quicklinks_users_status"
                                           href="ajax.adm_server.php?r=adm/dashboard/user_status_dialog"><?php echo Lang::t('_PROFILE', 'profile'); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($permissions['mod_user']): ?>
                                    <li>
                                        <a id="quicklinks_users_chgpwd"
                                           href="ajax.adm_server.php?r=adm/usermanagement/changepwd"><?php echo Lang::t('_CHANGEPASSWORD', 'profile'); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($permissions['add_user']): ?>
                                    <li>
                                        <a id="quicklinks_users_create"
                                           href="ajax.adm_server.php?r=adm/usermanagement/create"><?php echo Lang::t('_NEW_USER', 'admin_directory'); ?></a>
                                    </li>
                                    <li>
                                        <a id="quicklinks_users_import"
                                           href="index.php?r=adm/usermanagement/importusers&id=0"><?php echo Lang::t('_ORG_CHART_IMPORT_USERS', 'organization_chart'); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <?php
                            if ($permissions['view_user']) {
                                $this->widget('dialog', array(
                                    'id' => 'status_user_dialog',
                                    'width' => "700px",
                                    'dynamicContent' => true,
                                    'ajaxUrl' => 'this.href',
                                    'dynamicAjaxUrl' => true,
                                    'constrainToViewport' => false,
                                    'callback' => 'Dashboard.userStatusCallback',
                                    'renderEvent' => 'Dashboard.userStatusRenderEvent',
                                    'confirmOnly' => true,
                                    'fixedCenter' => false,
                                    'callEvents' => array(
                                        array('caller' => 'quicklinks_users_status', 'event' => 'click')
                                    )
                                ));
                            }

                            if ($permissions['mod_user']) {
                                $this->widget('dialog', array(
                                    'id' => 'chgpwd_user_dialog',
                                    'dynamicContent' => true,
                                    'constrainToViewport' => false, //this dialog may be too big to be constrained
                                    'ajaxUrl' => 'this.href',
                                    'dynamicAjaxUrl' => true,
                                    'fixedCenter' => false,
                                    'callback' => 'Dashboard.changePasswordCallback',
                                    'renderEvent' => 'Dashboard.changePasswordRenderEvent',
                                    'callEvents' => array(
                                        array('caller' => 'quicklinks_users_chgpwd', 'event' => 'click')
                                    )
                                ));
                            }

                            if ($permissions['add_user']) {
                                $this->widget('dialog', array(
                                    'id' => 'create_user_dialog',
                                    'dynamicContent' => true,
                                    'ajaxUrl' => 'this.href',
                                    'dynamicAjaxUrl' => true,
                                    'fixedCenter' => false,
                                    'constrainToViewport' => false, //this dialog may be too big to be constrained
                                    'renderEvent' => 'Dashboard.createUserRenderEvent',
                                    'callback' => 'Dashboard.createUserCallback',
                                    'callEvents' => array(
                                        array('caller' => 'quicklinks_users_create', 'event' => 'click')
                                    )
                                ));

                                //orgchart tree in the user creation popup, to be activated at runtime
                                $this->widget('tree', array(
                                    'id' => 'createuser_orgchart_tree',
                                    'ajaxUrl' => 'ajax.adm_server.php?r=adm/usermanagement/gettreedata_create',
                                    'treeClass' => 'DialogOrgFolderTree',
                                    'treeFile' => Get::rel_path('adm') . '/views/usermanagement/orgchartfoldertree.js',
                                    'initialSelectedNode' => 0,
                                    'show' => 'tree',
                                    'useCheckboxes' => 'true',
                                    'initialSelectorData' => array(0),
                                    'setSelectedNodeOnServer' => false,
                                    'hiddenSelection' => 'orgchart_hidden_selection',
                                    'runtime' => true,
                                    'languages' => array(
                                        '_ROOT' => Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', 'organization_chart')),
                                        '_LOADING' => Lang::t('_LOADING', 'standard')
                                    )
                                ));
                            }
                            ?>
                        </div>
                        <?php
                    endif;
                    ?>
                    <div class="block_spacer">
                        <h3><?php echo Lang::t('_CERTIFICATE', 'menu'); ?></h3>
                        <ul class="link_list">
                            <li>
                                <a id="find_certificate"
                                   href="ajax.adm_server.php?r=adm/dashboard/certificate"><?php echo Lang::t('_MONITOR_PRINT_CERTIFICATE_STATUS', 'dashboard'); ?></a>
                            </li>
                        </ul>
                        <?php
                        $this->widget('dialog', array(
                            'id' => 'certificate_dialog',
                            'width' => "700px",
                            'dynamicContent' => true,
                            'ajaxUrl' => 'this.href',
                            'dynamicAjaxUrl' => true,
                            'fixedCenter' => false,
                            'constrainToViewport' => true,
                            'callback' => 'Dashboard.certificateCallback',
                            'renderEvent' => 'Dashboard.certificateRenderEvent',
                            'callEvents' => array(
                                array('caller' => 'find_certificate', 'event' => 'click')
                            )
                        ));
                        ?>
                    </div>
                    <?php if (count($reports) > 0) : ?>
                        <div class="block_spacer">
                            <h3><?php echo Lang::t('_REPORT', 'report'); ?></h3>
                            <?php

                            echo Form::openForm('show_report_created_form', 'index.php?modname=report&amp;op=show_results&amp;of_platform=lms');
                            echo '<p><label for="report_created_sel">' . Lang::t('_SELECT', 'report') . '</label></p>';
                            echo Form::getInputDropdown('dropdown', 'report_created_sel', 'idrep', $reports, false, '') . '<br />';
                            echo Form::getButton('show_report_created', 'show_report_created', Lang::t('_VIEW', 'standard'), true, '', false);
                            echo Form::getButton('export_report_created', 'export_report_created', Lang::t('_EXPORT', 'standard'), true, '', false);
                            echo Form::closeform();

                            $this->widget('dialog', array(
                                'id' => 'export_report_dialog',
                                'dynamicContent' => true,
                                'ajaxUrl' => 'function() { return "ajax.adm_server.php?r=adm/dashboard/exportformat&id_report="+YAHOO.util.Dom.get("report_created_sel").value; }',
                                'dynamicAjaxUrl' => true,
                                'fixedCenter' => false,
                                'directSubmit' => true,
                                'hideAfterSubmit' => true,
                                'callback' => 'Dashboard.exportCallback',
                                'callEvents' => array(
                                    array('caller' => 'export_report_created', 'event' => 'click')
                                )
                            ));

                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="yui-u">
                    <?php
                    $_can_view_block = (bool)($permissions['view_course'] && ($permissions['add_course'] || $permissions['subscribe']));;
                    if ($_can_view_block):
                        ?>
                        <div class="block_spacer">
                            <h3><?php echo Lang::t('_COURSES', 'course'); ?></h3>
                            <ul class="link_list">
                                <?php if ($permissions['subscribe']): ?>
                                    <li>
                                        <a id="quicklinks_courses_subscr"
                                           href="ajax.adm_server.php?r=alms/subscription/fast_subscribe_dialog"><?php echo Lang::t('_SUBSCRIBE', 'dashboard'); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($permissions['add_course']): ?>
                                    <li>
                                        <a id="quicklinks_courses_create"
                                           href="index.php?r=alms/course/newcourse"><?php echo Lang::t('_NEW_COURSE', 'dashboard'); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <?php
                            if ($permissions['subscribe']) {
                                $this->widget('dialog', array(
                                    'id' => 'subscr_course_dialog',
                                    'dynamicContent' => true,
                                    'ajaxUrl' => 'this.href',
                                    'dynamicAjaxUrl' => true,
                                    'fixedCenter' => false,
                                    'callback' => 'Dashboard.subscribeToCourseCallback',
                                    'renderEvent' => 'Dashboard.subscribeToCourseRenderEvent',
                                    'callEvents' => array(
                                        array('caller' => 'quicklinks_courses_subscr', 'event' => 'click')
                                    )
                                ));
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    <?php
                    $_can_view_block = (bool)(($permissions['view_communications'] && ($permissions['add_communications']) || ($permissions['view_games'] && $permissions['add_games'])));
                    if ($_can_view_block):
                        ?>
                        <div class="block_spacer">
                            <h3><?php echo Lang::t('_CONTENTS', 'dashboard'); ?></h3>
                            <ul class="link_list">
                                <?php if ($permissions['add_communications']): ?>
                                    <li>
                                        <a href="index.php?r=alms/communication/add"><?php echo Lang::t('_COMMUNICATIONS', 'communication'); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($permissions['add_games']): ?>
                                    <li>
                                        <a href="index.php?r=alms/games/add"><?php echo Lang::t('_CONTEST', 'games'); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <div class="block_spacer">
                        <h3><?php echo Lang::t('_DETAILS', 'dashboard'); ?></h3>
                        <ul class="link_list">
                            <li>
                                <?php
                                echo Lang::t('_VERSION', 'dashboard') . ': <b>' . $version['db_version'] . '</b>';

                                // check for differences beetween files and database version
                                if (version_compare($version['file_version'], $version['db_version']) <> 0) {
                                    echo '<br/>'
                                        . 'Different from core file version:' . '<span class="red"><b>' . $version['file_version'] . '</b></span>'
                                        . '<br/>'
                                        . '<a href="../upgrade" class="red"><b>' . 'You need database upgrade' . '</b></a>';
                                }

                                if (Get::sett('welcome_use_feed') == 'on') {

                                    if (!$version['online_version']) {

                                        $version = array('string' => '<b class="red">' . Lang::t('_UNKNOWN_RELEASE', 'dashboard') . '</b>');
                                    } elseif (version_compare($version['online_version'], $version['file_version']) == 1) {

                                        echo '<br/>'
                                            . '<a href="http://www.formalms.org/downloads/?versions" class="red">' . Lang::t('_NEW_RELEASE_AVAILABLE', 'dashboard') . ': <b>' . $version['online_version'] . '</b></a>';
                                    }
                                }
                                ?>
                            </li>
                            <li><a href="../changelog.txt" target="_blank">Changelog</a></li>
                            <li>
                                <a href="index.php?r=adm/dbupgrades/show"><?php echo Lang::t('_DB_UPGRADES', 'dashboard'); ?></a>
                            </li>
                            <li>
                                <a id="quicklinks_diagnostic"
                                   href="ajax.adm_server.php?r=adm/dashboard/diagnostic_dialog">
                                    <?php
                                    if ($diagnostic_problem) echo '<span class="ico-sprite fd_notice"><span>' . Lang::t('_WARNING', 'standard') . '</span></span>&nbsp;';
                                    echo Lang::t('_SERVERINFO', 'configuration');
                                    ?>
                                </a>
                            </li>
                        </ul>
                        <?php
                        $this->widget('dialog', array(
                            'id' => 'tech_info_dialog',
                            'dynamicContent' => true,
                            'ajaxUrl' => 'this.href',
                            'width' => '700px',
                            'dynamicAjaxUrl' => true,
                            'fixedCenter' => false,
                            'constrainToViewport' => false, //this dialog may be too big
                            'callback' => 'Dashboard.diagnosticCallback',
                            'renderEvent' => 'Dashboard.diagnosticRenderEvent',
                            'confirmOnly' => true,
                            'callEvents' => array(
                                array('caller' => 'quicklinks_diagnostic', 'event' => 'click')
                            )
                        ));
                        ?>
                    </div>
                </div>
                <div class="nofloat"></div>
                <div class="block_spacer">
                    <h3><?php echo Lang::t('_SUPPORT_SITE', 'dashboard'); ?></h3>
                    <ul class="link_list">
                        <li>
                            <a href="http://www.formalms.org/"
                               onclick="window.open(this.href); return false;"
                               onkeypress="window.open(this.href); return false;"
                               title="Forma <?php echo Lang::t('_OPEN_IN_NEW_WINDOW', 'dashboard'); ?>">
                                <?php echo Lang::t('_URL_COMPANY', 'dashboard'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="http://www.formalms.org/community"
                               onclick="window.open(this.href); return false;"
                               onkeypress="window.open(this.href); return false;"
                               title="Forma Community <?php echo Lang::t('_OPEN_IN_NEW_WINDOW', 'dashboard'); ?>">
                                <?php echo Lang::t('_URL_SUPPORTLMS', 'dashboard'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Social list -->
                <!-- hiding social links
				<div class="block_spacer">
					<h3><?php echo Lang::t('_FOLLOW_US', 'dashboard'); ?></h3>
				</div>
				-->
            </div>
        </div>
    </div>
    <div class="yui-u">
        <div class="inline_block_big">
            <h2 class="heading"><?php echo Lang::t('_USERS', 'dashboard'); ?></h2>
            <div class="content">
                <div class="yui-g">
                    <div class="yui-u first">
                        <ul class="link_list">
                            <li><?php echo Lang::t('_TOTAL_USER', 'dashboard') . ': <b id="total_users_count">' . ($user_stats['all'] - 1) . '</b>;'; ?></li>
                            <li><?php echo Lang::t('_SUSPENDED', 'dashboard') . ': <b>' . $user_stats['suspended'] . '</b>;'; ?></li>
                            <?php echo($can_approve ? '<li>' . Lang::t('_WAITING_USERS', 'dashboard') . ': <b>' . $user_stats['waiting'] . '</b>;</li>' : ''); ?>
                            <li><?php echo Lang::t('_REG_LASTSEVENDAYS', 'dashboard') . ':<b>' . $user_stats['register_7d'] . '</b>;'; ?></li>
                            <?php if (Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN): ?>
                                <li><?php echo Lang::t('_INACTIVE_USER', 'dashboard') . ': <b>' . $user_stats['inactive_30d'] . '</b>;'; ?></li>
                                <li><?php echo Lang::t('_ONLINE_USER', 'dashboard') . ': <b>' . $user_stats['now_online'] . '</b>;'; ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="yui-u">
                        <ul class="link_list">
                            <?php if (Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN): ?>
                                <li><?php echo Lang::t('_SUPERADMIN_USER', 'dashboard') . ': <b>' . $user_stats['superadmin'] . '</b>;'; ?></li>
                                <li><?php echo Lang::t('_ADMIN_USER', 'dashboard') . ': <b>' . $user_stats['admin'] . '</b>;'; ?></li>
                            <?php else: ?>
                                <li><?php echo Lang::t('_INACTIVE_USER', 'dashboard') . ': <b>' . $user_stats['inactive_30d'] . '</b>;'; ?></li>
                                <li><?php echo Lang::t('_ONLINE_USER', 'dashboard') . ': <b>' . $user_stats['now_online'] . '</b>;'; ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="nofloat"></div>
                <!-- <div style="text-align:center;margin:1em;padding:1em;">
                    <p>Statistics: <span id="users_chart_buttons"></span></p>
                    <div id="users_chart_display"></div>
                </div> --><br/>
                <!--				<div id="users_tabview"></div>-->
                <div class="graph graph--users">
                    <div class="graph__nav">
                        <ul>
                            <li class="js-dashboard-graph graph__label selected" data-tab="accesses"><?php echo Lang::t('_ACCESSES', 'standard')  ?></li>
                            <li class="js-dashboard-graph graph__label" data-tab="registeredusers"><?php echo Lang::t('_TOTAL_USER', 'dashboard') ?></li>
                        </ul>
                    </div>
                    <div class="graph__container">
                        <div id="user_accesses_chart"
                             class="graph__content graph__content--accesses graph__content--visible">

                        </div>
                        <div id="user_registrations_chart" class="graph__content graph__content--registeredusers">

                        </div>
                    </div>
                    <div id="user_registrations_chart"></div>
                </div>
            </div>
            <div class="inline_block_big">
                <h2 class="heading"><?php echo Lang::t('_COURSES', 'dashboard'); ?></h2>
                <div class="content">
                    <div class="yui-g">
                        <div class="yui-u first">
                            <ul class="link_list">
                                <li><?php echo Lang::t('_TOTAL_COURSE', 'dashboard') . ': <b>' . $course_stats['total'] . '</b>;'; ?></li>
                                <li><?php echo Lang::t('_ACTIVE_COURSE', 'dashboard') . ': <b>' . $course_stats['active'] . '</b>;'; ?></li>
                                <li><?php echo Lang::t('_ACTIVE_SEVEN_COURSE', 'dashboard') . ': <b>' . $course_stats['active_seven'] . '</b>;'; ?></li>
                            </ul>
                        </div>
                        <div class="yui-u">
                            <ul class="link_list">
                                <li>
                                    <?php echo Lang::t('_TOTAL_SUBSCRIPTION', 'dashboard') . ': <b>' . $course_stats['user_subscription'] . '</b>;'; ?>
                                </li>
                                <?php
                                echo(checkPerm('moderate', true, 'course', 'lms') ? '<li>' . Lang::t('_WAITING_SUBSCRIPTION', 'dashboard') . ': <b>' . $course_stats['user_waiting'] . '</b>;</li>' : '');
                                $month_1 = (int)date("m");
                                $month_2 = (($month_1 + 12 - 2) % 12) + 1;
                                $month_3 = (($month_1 + 12 - 3) % 12) + 1;
                                ?>
                                <li>
                                    <?php echo Lang::t('_SUBSCRIPTION', 'course') . '&nbsp;' . Lang::t('_MONTH_' . ((int)$month_1 < 10 ? '0' : '') . (int)$month_1) . ': <b>' . $course_months_stats['month_subs_1'] . '</b>;'; ?>
                                </li>
                                <li>
                                    <?php echo Lang::t('_SUBSCRIPTION', 'course') . '&nbsp;' . Lang::t('_MONTH_' . ((int)$month_2 < 10 ? '0' : '') . (int)$month_2) . ': <b>' . $course_months_stats['month_subs_2'] . '</b>;'; ?>
                                </li>
                                <li>
                                    <?php echo Lang::t('_SUBSCRIPTION', 'course') . '&nbsp;' . Lang::t('_MONTH_' . ((int)$month_3 < 10 ? '0' : '') . (int)$month_3) . ': <b>' . $course_months_stats['month_subs_3'] . '</b>;'; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="nofloat"></div>
                    <!-- <div style="text-align:center;margin:1em;padding:1em;">
                        <p>Statistics:&nbsp;<span id="courses_chart_buttons"></span></p>
                        <div id="users_chart_display"></div>
                    </div> --><br/>
                    <!--				<div id="courses_tabview"></div>-->
                    <div class="graph graph--users js-graph-courses">
                        <div class="graph__nav">
                            <ul>
                                <li class="js-dashboard-graph graph__label selected" data-tab="registered"><?php echo Lang::t('_ACCESSES', 'standard')  ?></li>
                                <li class="js-dashboard-graph graph__label" data-tab="ongoing"><?php echo Lang::t('_USER_STATUS_BEGIN', 'standard')  ?></li>
                                <li class="js-dashboard-graph graph__label" data-tab="finished"><?php echo Lang::t('_USER_STATUS_END', 'standard')  ?></li>
                            </ul>
                        </div>
                        <div class="graph__container">
                            <div id="courses_subscriptions_chart"
                                 class="graph__content graph__content--registered graph__content--visible">

                            </div>
                            <div id="courses_startattendings_chart" class="graph__content graph__content--ongoing">

                            </div>
                            <div id="courses_completed_chart" class="graph__content graph__content--finished">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="nofloat"></div>

<script type="text/javascript">
    $(document).ready(function () {

        new Chartist.Bar('#user_accesses_chart', {
            labels: <?php echo $userdata_accesses_js['x_axis']?>,
            series: [
                <?php echo $userdata_accesses_js['y_axis']?>
            ]
        }, {
            axisX: {
                // On the x-axis start means top and end means bottom
                position: 'end'
            },
            axisY: {
                // On the y-axis start means left and end means right
                position: 'start'
            }
        });

        new Chartist.Bar('#user_registrations_chart', {
            labels: <?php echo $userdata_registrations_js['x_axis']?>,
            series: [
                <?php echo $userdata_registrations_js['y_axis']?>
            ]
        }, {
            axisX: {
                // On the x-axis start means top and end means bottom
                position: 'end'
            },
            axisY: {
                // On the y-axis start means left and end means right
                position: 'start'
            }
        });

        new Chartist.Bar('#courses_subscriptions_chart', {
            labels: <?php echo $coursedata_subscriptions_js['x_axis']?>,
            series: [
                <?php echo $coursedata_subscriptions_js['y_axis']?>
            ]
        }, {
            axisX: {
                // On the x-axis start means top and end means bottom
                position: 'end'
            },
            axisY: {
                // On the y-axis start means left and end means right
                position: 'start'
            }
        });

        new Chartist.Bar('#courses_startattendings_chart', {
            labels: <?php echo $coursedata_startattendings_js['x_axis']?>,
            series: [
                <?php echo $coursedata_startattendings_js['y_axis']?>
            ]
        }, {
            axisX: {
                // On the x-axis start means top and end means bottom
                position: 'end'
            },
            axisY: {
                // On the y-axis start means left and end means right
                position: 'start'
            }
        });

        new Chartist.Bar('#courses_completed_chart', {
            labels: <?php echo $coursedata_completed_js['x_axis']?>,
            series: [
                <?php echo $coursedata_completed_js['y_axis']?>
            ]
        }, {
            axisX: {
                // On the x-axis start means top and end means bottom
                position: 'end'
            },
            axisY: {
                // On the y-axis start means left and end means right
                position: 'start'
            }
        });


    });
</script>
