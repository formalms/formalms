'use strict';

var Config = {
	projectName: 'formalms',
	env: {
		dev: {
			baseUrl: 'http://localhost',
			apiUrl: 'http://formalms.local/',
			shareUrl: 'http://localhost',
			assetsUrl: '/dev/'
		},
		local: {
			baseUrl: 'http://formalms.local',
			apiUrl: 'http://formalms.local/',
			shareUrl: 'http://formalms.local/',
			assetsUrl: 'http://formalms.local/static/'
		},
		stage: {
			baseUrl: 'http://formalms.stage.it',
			apiUrl: 'http://formalms.stage.it/',
			shareUrl: 'http://formalms.stage.it/',
			assetsUrl: 'http://formalms.stage.it/static/'
		},
		prod: {
			baseUrl: 'http://www.formalms.it',
			apiUrl: 'http://www.formalms.it',
			shareUrl: 'http://www.formalms.it',
			assetsUrl: 'http://www.formalms.it/static/'
		}
	}
};

module.exports = Config;