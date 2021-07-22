<?php

/**
 * Class Tab
 */
Class Tab {


    static function printStartContent(){

        cout("<div class='tab-content'>");
        cout('<div role="tabpanel" class="tab-pane fade in active" id="">');

    }

    /**
     * @param $tabsArr
     */
    static function printTab($tabsArr){

        $out = '';

        foreach ($tabsArr as $key => $tabsArr) {

            $out .= "<div id='tab-$key'>";
            $out .= $tabsArr["content"];
            $out .= "</div>";

        }

        cout($out);

    }

    /**
     *
     */
    static function printEndContent(){

        cout("</div>");
    }

}

/**
 * Class TabContainer
 */
Class TabContainer {

    static function printStartHeader(){

        cout ('<ul class="nav nav-tabs" role="tablist">'); 

    }

    static function printNewTabHeader($arrTab){

        $out = '';

        foreach ($arrTab as $key => $array) {
            $out .= "<li><a aria-controls='' role='' data-toggle='' href='#tab-$key'><em>";
            $out .= $array["title"];
            $out .= "</em></a></li>";

        }

        cout($out);

    }

    /**
     *
     */
    static function printEndHeader(){
        cout ('</ul>');
    }



}