<?php

function getTemplate() {
	return 'standard';
}


function getTemplatePath() {
	switch (INSTALL_ENV) {
		case 'upgrade': {
			return '../install/templates/'.getTemplate().'/';
		} break;

		default:
		case 'install': {
			return './templates/'.getTemplate().'/';
		} break;
	}
}

?>