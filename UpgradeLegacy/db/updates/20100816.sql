INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('paypal_mail', '', 'string', '255', '0', '1', '8', '1', '0', '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('paypal_currency', 'EUR', 'string', '255', '0', '1', '9', '1', '0', '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('paypal_sandbox', 'off', 'enum', '3', '0', '1', '10', '1', '0', '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('currency_symbol', '€', 'string', '10', '0', '1', '11', '1', '0', '');

ALTER TABLE `core_transaction`
CHANGE `payed` `paid` tinyint(1) NOT NULL DEFAULT '0';