'use strict';

// fastclick
require('fastclick').attach(document.body);

// device
require('./components/device');

// router
require('./router/router').pushState(false);