UPDATE `learning_menu_under` SET `default_op` = '', `class_file` = '', `class_name` = '', `mvc_path` = 'alms/questcategory/show' WHERE `idUnder`=7 LIMIT 1 ;

ALTER TABLE `learning_communication` ADD COLUMN `id_category` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `id_resource`;

CREATE TABLE `learning_communication_category` (
  `id_category` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_parent` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
  `level` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
  `iLeft` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
  `iRight` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_category`)
) ENGINE = InnoDB;

CREATE TABLE `learning_communication_category_lang` (
  `id_category` INTEGER UNSIGNED NOT NULL,
  `lang_code` VARCHAR(255) NOT NULL,
  `translation` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_category`, `lang_code`)
) ENGINE = InnoDB;