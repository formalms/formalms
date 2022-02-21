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

defined('IN_FORMA') or exit('Direct access is forbidden.');

/*
    - users flat
    - users hierarchical
    - groups flat
    - groups hierarchical
    - all flat
    - all hierarchical
    - oragnization chart

    flat view
      |--- users
      |--- groups
      |--- all

    hierarchical view
      |--- users
      |--- groups
      |--- all

    organization chart

    The hierarchical view is now suspended because it's
    incompatible with users knowledge

*/

/* function for dipatch main operations
function directoryDispatch( $op ) {

}*/
