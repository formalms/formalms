ALTER TABLE `core_user` CHANGE `pass` `pass` VARCHAR(255) NOT NULL;

INSERT INTO `core_setting` (
  `param_name` ,
  `param_value` ,
  `value_type` ,
  `max_size` ,
  `pack` ,
  `regroup` ,
  `sequence` ,
  `param_load` ,
  `hide_in_modify` ,
  `extra_info`
)
VALUES (
  'pass_algorithm', '1', 'password_algorithms', '255', 'password', '3', '5', '1', '0', ''
);