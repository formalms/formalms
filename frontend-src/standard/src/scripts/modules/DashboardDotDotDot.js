require ('dotdotdot/src/js/jquery.dotdotdot')
const $ = require('jquery');

export default class DashboardDotDotDot {
	constructor() {
		const elements = $('.js-d-dotdotdot');
    
    if (elements.length) {
      [].forEach.call(elements, (item) => {
        $(item).dotdotdot({height: 34})
      });
    }
	}
}