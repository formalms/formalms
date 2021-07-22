--
-- Update database formalms
--
--
-- Update db script from docebo ce 4.0.5 to forma 1.0
--

-- ------------------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- ------------------------------------------------------------------

-- maintenance mode settings
INSERT IGNORE INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`) VALUES
('maintenance', 'off', 'enum', 3, 'security', 8, 25);

INSERT IGNORE INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`) VALUES
('maintenance_pw', 'manutenzione', 'string', 3, 'security', 8, 25);

-- new field type
INSERT IGNORE INTO `core_field_type` (`type_field`, `type_file`, `type_class`) VALUES
('textlabel', 'class.label.php', 'Field_Textlabel');

-- update key language
UPDATE IGNORE `core_lang_text` SET `text_key`= '_IMPORT_NOTHINGTOPROCESS' WHERE `text_key` = '_DOCEBO_IMPORT_NOTHINGTOPROCESS';

-- maintenance mode settings
INSERT IGNORE INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`) VALUES
('maintenance', 'off', 'enum', 3, 'security', 8, 25);

INSERT IGNORE INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`) VALUES
('maintenance_pw', 'manutenzione', 'string', 3, 'security', 8, 25);

-- -----------
--

CREATE TABLE IF NOT EXISTS `learning_htmlpage_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idpage` int(11) unsigned NOT NULL,
  `file` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--


-- ------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ------------------------------------------------------------------
