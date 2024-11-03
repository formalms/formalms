CREATE TABLE `core_privacypolicy` (
  `id_policy` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_policy`)
)
ENGINE = InnoDB;


CREATE TABLE `core_privacypolicy_lang` (
  `id_policy` INTEGER(11) UNSIGNED NOT NULL,
  `lang_code` VARCHAR(255) NOT NULL,
  `translation` TEXT NOT NULL,
  PRIMARY KEY (`id_policy`, `lang_code`)
)
ENGINE = InnoDB;

ALTER TABLE `core_org_chart_tree` ADD COLUMN `associated_policy` INTEGER(11) UNSIGNED AFTER `idst_ocd`;


ALTER TABLE `learning_courseuser` ADD COLUMN `requesting_unsubscribe` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `date_expire_validity`,
 ADD COLUMN `requesting_unsubscribe_date` DATETIME AFTER `requesting_unsubscribe`;