'use strict';

var Page = (function () {
	
	function setScroll(elem, action) {
		if (action === 'lock') {
			$(elem).addClass('no-scroll');
		} else {
			$(elem).removeClass('no-scroll');
		}
	}

	function setInteractions() {
		$('.o-wrapper').on('click', function () {
			console.log('lock');

			if ($(this).hasClass('open')) {
				setScroll('.header', 'unlock');
			} else {
				setScroll('.header', 'lock');
			}
		});

	}

	function Page() {

		console.log('asd');

		setInteractions();
	}

	Page.prototype.setData = function () {

	};
	
	Page.prototype.load = function () {

	};

	return new Page();

})();

module.exports = Page;