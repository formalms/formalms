CREATE TABLE IF NOT EXISTS learning_communication_lang (
    id_comm int,
    lang_code varchar(255),
    title varchar(255),
    description text
);

UPDATE `core_reg_setting` SET `value` = '-' WHERE `val_name` = 'date_sep';