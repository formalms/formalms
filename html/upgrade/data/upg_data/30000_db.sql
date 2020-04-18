--
-- Aggregated certificate refactoring MVC
-- 
UPDATE core_menu_under 
SET  	default_op = '',
		class_file = '',
		class_name = '',
		mvc_path = 'alms/aggregatedcertificate/show'
WHERE module_name = 'meta_certificate';



ALTER TABLE learning_certificate_meta
    RENAME TO learning_aggregated_cert_metadata;
ALTER TABLE learning_aggregated_cert_metadata
    CHANGE idMetaCertificate idAssociation INT(11) NOT NULL AUTO_INCREMENT;




ALTER TABLE learning_certificate_meta_assign
    RENAME TO learning_aggregated_cert_assign;
ALTER TABLE learning_aggregated_cert_assign
    CHANGE idMetaCertificate idAssociation INT(11) NOT NULL;
ALTER TABLE `learning_aggregated_cert_assign` DROP PRIMARY KEY, ADD PRIMARY KEY (`idUser`, `idCertificate`,  `idAssociation`) USING BTREE;



ALTER TABLE learning_certificate_meta_course
    RENAME TO learning_aggregated_cert_course;
ALTER TABLE learning_aggregated_cert_course
    CHANGE idMetaCertificate idAssociation INT(11) NOT NULL ;
ALTER TABLE `learning_aggregated_cert_course`
  ADD KEY `idAssociation` (`idAssociation`);    

    

CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_coursepath` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idAssociation` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCoursePath` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idAssociation` (`idAssociation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- 
--
