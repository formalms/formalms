<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

/**
 * Class Tab.
 */
class Tab
{
    public static function printStartContent()
    {
        cout("<div class='tab-content'>");
        cout('<div role="tabpanel" class="tab-pane fade in active" id="">');
    }

    /**
     * @param $tabsArr
     */
    public static function printTab($tabsArr)
    {
        $out = '';

        foreach ($tabsArr as $key => $tabsArr) {
            $out .= "<div id='tab-$key'>";
            $out .= $tabsArr['content'];
            $out .= '</div>';
        }

        cout($out);
    }

    public static function printEndContent()
    {
        cout('</div>');
    }
}

/**
 * Class TabContainer.
 */
class TabContainer
{
    public static function printStartHeader()
    {
        cout('<ul class="nav nav-tabs" role="tablist">');
    }

    public static function printNewTabHeader($arrTab)
    {
        $out = '';

        foreach ($arrTab as $key => $array) {
            $out .= "<li><a aria-controls='' role='' data-toggle='' href='#tab-$key'><em>";
            $out .= $array['title'];
            $out .= '</em></a></li>';
        }

        cout($out);
    }

    public static function printEndHeader()
    {
        cout('</ul>');
    }
}
