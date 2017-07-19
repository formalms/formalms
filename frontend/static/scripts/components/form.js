'use strict';

var $ = require('jquery');

var Form = (function () {
	
	function Form(config) {
		this.config = config || false;
		this.errors = setErrors(this.config);
		this.init();
	}
	
	function setErrors(config) {
		var _errors = {};
		
		if (config) {
			$(config.form).find('[data-validation]').each(function () {
				_errors[$(this).attr('id')] = false;
			});
		}
		
		return _errors;
	}
	
	function validateRequired(input) {
		var value = input.prop('value');
		var type = input.prop('type');
		var result = true;
		
		if (type === 'checkbox') {
			if (input.is(':checked')) {
				result = true;
			} else {
				result = false;
			}
		} else {
			if (!value || value === '') {
				result = false;
			} else {
				result = true;
			}
		}
		
		return result;
	}
	
	function validateEmail(input) {
		var value = $(input).prop('value');
		var result = true;
		
		if (!value || value === '') {
			result = false;
		}
		
		var _reg = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		if (!_reg.test(value)) {
			result = false;
		}
		
		return result;
	}
	
	function validateLength(input, minLength) {
		var value = $(input).prop('value');
		var result = true;
		
		if (!value || value.length < minLength) {
			result = false;
		}
		
		return result;
	}
	
	/*function watchPhoneChange(input) {
	 var value = $(input).prop('value');
	 
	 if (value.substr(0, 1) === '+') {
	 value = value.substr(1, value.length);
	 value = value.replace(/\s+/g, '-').replace(/[^0-9-]/g, '').replace(/\-+/g, '');
	 value = '+' + value.substr(0, value.length);
	 } else {
	 value = value.replace(/\s+/g, '-').replace(/[^0-9-]/g, '').replace(/\-+/g, '');
	 value = value.substr(0, value.length);
	 }
	 
	 input.val(value);
	 }*/
	
	function validatePhone(input) {
		var value = $(input).prop('value');
		
		if (!value || value === '') {
			return false;
		}
		
		if (value.length < 10) {
			return false;
		} else {
			return true;
		}
	}
	
	/*function watchDateChange(input) {
	 var value = $(input).prop('value');
	 
	 value = value.replace(/\s+/g, '-').replace(/[^0-9-/]/g, '').replace(/\-+/g, '');
	 value = value.substr(0, 10);
	 
	 input.val(value);
	 }*/
	
	function watchDuplicateInput(input) {
		var value = $(input).prop('value');
		var valueOriginal = $('[name="' + $(input).attr('data-watch') + '"]').prop('value');
		
		if (value !== valueOriginal) {
			return false;
		} else {
			return true;
		}
	}
	
	function addError(input) {
		$(input).parents('.form__input').addClass('has-error');
	}
	
	function canSubmit(form, errors) {
		var count = 0;
		$.each(errors, function (index, val) {
			if (!val) {
				count++;
			}
		});
		
		if (count === 0) {
			$(form).find('.js-form__submit').removeClass('button--disabled');
		} else {
			$(form).find('.js-form__submit').addClass('button--disabled');
		}
	}
	
	Form.prototype.init = function () {
		if (this.config.onSuccess && typeof this.config.onSuccess === 'function') {
			$(this.config.form).on('submit', $.proxy(function (ev) {
				ev.preventDefault();
				this.submit($(this.config.form));
				return false;
			}, this));
		}
		
		$(this.config.form).find('input').focus(function () {
			$(this).parents('.has-error').removeClass('has-error');
		}).on('change', function () {
			$(this).parents('.has-error').removeClass('has-error');
		});
		
		$(this.config.form).find('input[type="email"]').on('input', $.proxy(function (ev) {
			this.errors[$(ev.currentTarget).attr('id')] = validateEmail($(ev.currentTarget));
			canSubmit(this.config.form, this.errors);
		}, this)).on('blur', function () {
			if (!validateEmail(this)) {
				addError(this);
			}
		});
		
		$(this.config.form).find('input[type="password"]').on('input', $.proxy(function (ev) {
			this.errors[$(ev.currentTarget).attr('id')] = validateLength($(ev.currentTarget), 6);
			canSubmit(this.config.form, this.errors);
		}, this)).on('blur', function () {
			if (!validateLength(this, 6)) {
				addError(this);
			}
		});
		
		$(this.config.form).find('[data-watch]').on('input', $.proxy(function (ev) {
			this.errors[$(ev.currentTarget).attr('id')] = watchDuplicateInput($(ev.currentTarget));
			canSubmit(this.config.form, this.errors);
		}, this)).on('blur', function () {
			if (!watchDuplicateInput(this)) {
				addError(this);
			}
		});
		
		$(this.config.form).find('input[data-validation]').on('blur change', $.proxy(function (ev) {
			var _isRequired = ($(ev.currentTarget).attr('data-validation').indexOf('required') >= 0);
			if (_isRequired) {
				this.errors[$(ev.currentTarget).attr('id')] = validateRequired($(ev.currentTarget));
				if (!validateRequired($(ev.currentTarget))) {
					addError($(ev.currentTarget));
				}
			}
			canSubmit(this.config.form, this.errors);
		}, this));
		
		// $('[type="tel"]').on('input',function() {
		// 	watchPhoneChange($(this));
		// });
		
		// $('[name="birthdate"]').on('input',function() {
		// 	watchDateChange($(this));
		// });
		
		// $('.js-input').click(function() {
		// 	$(this).parents('.has-error').removeClass('has-error');
		// });
	};
	
	Form.prototype.validate = function (callback) {
		var errors = 0;
		$(this.config.form).find('[data-validation]').each(function (index, item) {
			var _validation = $(item).attr('data-validation').split(',');
			var _isValid = false;
			$.each(_validation, function (index, type) {
				if (type === 'required') {
					_isValid = validateRequired($(item));
				}
				
				if (type === 'email') {
					_isValid = validateEmail($(item));
				}
				
				if (type === 'phone') {
					_isValid = validatePhone($(item));
				}
				
				if (!_isValid) {
					errors++;
				}
			});
		});
		
		if (errors === 0) {
			callback();
		} else {
			$('.section').scrollTop(0);
		}
	};
	
	Form.prototype.submit = function () {
		
		this.validate($.proxy(function () {
			$.ajax({
				type: this.config.method,
				url: this.config.apiUrl,
				data: $(this.config.form).serialize(),
				success: $.proxy(function (res) {
					if (this.config.onSuccess && typeof this.config.onSuccess === 'function') {
						this.config.onSuccess(res);
					}
				}, this),
				error: $.proxy(function (err) {
					switch (err.status) {
						case 401:
							console.debug('Errore campi server da gestire');
							console.log('--> ', err);
							break;
						case 409:
							$('.section').scrollTop(0);
							$(this.config.form).find('[type="email"]').siblings('.js-formError').html('This user already exists').parents('.form__input').addClass('has-error');
							break;
						default:
							break;
					}
				}, this)
			});
		}, this));
	};
	
	Form.prototype.validateEmail = function (value) {
		var result = true;
		
		if (!value || value === '') {
			result = false;
		}
		
		var _reg = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		if (!_reg.test(value)) {
			result = false;
		}
		
		return result;
	};
	
	return Form;
	
})();

module.exports = Form;