<?php

/**
 * Class Tab
 */
Class Tab {

    /* Versione G-tab */
   /* 
   static function printStartContent(){

        cout("<div class='tabscontent'>");

    }       */
      
/* Versione tab forma */

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

        /* cout("</div>");  Abilitare per versione forma */
        cout("</div>");
    }

}

/**
 * Class TabContainer
 */
Class TabContainer {

    /**
     *
     */
    static function printStartHeader(){

     //   cout ('<ul class="tabs">'); // In base ad una variabile print, fare return o cout. - versione G-tab
        cout ('<ul class="nav nav-tabs" role="tablist">'); // Versione forma

    }

    /** versione g-tab
     * @param $arrTab
     */
     /*
    static function printNewTabHeader($arrTab){

        $out = '';

        foreach ($arrTab as $key => $array) {
            $out .= "<li><a href='#tab-$key'>";
            $out .= $array["title"];
            $out .= "</a></li>";

        }

        cout($out);

    } */

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

    /*
   <ul class="tabs">
        <li><a href="#tab-1">Tab 1</a></li>
        <li><a href="#tab-2">Tab 2</a></li>
    </ul>

    <div class="tabscontent">
        <div id="tab-1">
            <div id="ilmiotree"></div>
        </div>
        <div id="tab-2">
            <div>Hello world</div>
        </div>
        <div id="tab-3">
            <!-- your third tab content -->
        </div>
        <div id="tab-n">
            <!-- your n tab content -->
        </div>
    </div>
     */

}