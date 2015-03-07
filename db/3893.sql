-- bug #3893

-- set max size of the maintenance password to 16 chars
UPDATE `core_setting` SET `max_size`=16 WHERE `param_name`='maintenance_pw';
