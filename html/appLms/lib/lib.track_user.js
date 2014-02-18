/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

var ut_step = 30;

var ut_id_interval;

var ut_partial 	= 0;
var ut_total 	= 0;

if( window.document.getElementById == null ) {
	window.document.getElementById = function( id ) {
		return document.all[id];
  }
}

function userTimeCounter() {
	
	var elem_partial 	= document.getElementById('partial_time');
	var elem_total 		= document.getElementById('total_time');
	
	if(elem_partial == false || elem_total == false) {
		
		window.clearInterval(ut_id_interval);
	}
	
	ut_partial 	+= ut_step;
	ut_total 	+= ut_step;
	
	hour 	= Math.floor(ut_partial / 3600);
	minute 	= Math.floor((ut_partial % 3600) / 60);
	second 	= (ut_partial % 60);
	
	elem_partial.innerHTML = ( hour == 0 ? '' : hour+'h ' ) + 
		( minute <= 9 ? '0' + minute : minute ) + 'm ';
		// + ( second <= 9 ? '0' + second : second ) + 's';
	
	hour 	= Math.floor(ut_total / 3600);
	minute 	= Math.floor((ut_total % 3600) / 60);
	second 	= (ut_total % 60);
	
	if( minute.length <= 1 ) minute = '0' + minute;
	if( second.length <= 1 ) second = '0' + second;
	
	elem_total.innerHTML = ( hour == 0 ? '' : hour+'h ' ) + 
		( minute <= 9 ? '0' + minute : minute ) + 'm ';
		// + ( second <= 9 ? '0' + second : second ) + 's';
}

function userCounterStart(partial_sec, total_sec) {
	
	ut_partial 	= partial_sec;
	ut_total 	= total_sec;
	
	ut_id_interval = window.setInterval("userTimeCounter()", ut_step * 1000);
}
