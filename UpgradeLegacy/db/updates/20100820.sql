--
-- Role to add
--
--/lms/course/public/pcertificate/view
--/lms/course/public/pcertificate/mod
--

--Insert public admin menu

INSERT INTO `learning_module` (`idModule`, `module_name`, `default_op`, `default_name`, `token_associated`, `file_name`, `class_name`, `module_info`, `mvc_path`) VALUES
(NULL, 'pcertificate', 'certificate', '_PUBLIC_CERTIFICATE_ADMIN', 'view', 'class.pcertificate.php', 'Module_Pcertificate', 'public_admin', '');