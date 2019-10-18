
/* Utilizzato sito nimbletext per generazione di testi parametrizzati, creata lista su csv editor di identificativi (40) e generate le seguenti query





INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_$0', 'Cert. $0', 'Questa è la descrizione del cert. $0', 'italian', 'Struttura del certificato $0', 'P', '', 1,1);

INSERT INTO `learning_aggregated_cert_metadata` (`idCertificate`, `title`, `description`) VALUES ($0, 'Associazione al cert. $0', 'Questa è la descrizione dell\'assoc. del cert. $0, ed è di tipo corso');

-- associazione dell'utente admin al corso 1
INSERT INTO `learning_aggregated_cert_course` (`idAssociation`, `idUser`, `idCourse`) VALUES ($1,11836, 1);

-- associazione dell'utente admin al corso 2
INSERT INTO `learning_aggregated_cert_course` (`idAssociation`, `idUser`, `idCourse`) VALUES ($1,11836, 2);

-- associazione dell'utente 11922 (bicca) al corso 1
INSERT INTO `learning_aggregated_cert_course` (`idAssociation`, `idUser`, `idCourse`) VALUES ($1,11922, 1);

-- associazione dell'utente 11922 (bicca) al corso 1
INSERT INTO `learning_aggregated_cert_course` (`idAssociation`, `idUser`, `idCourse`) VALUES ($1,11922, 2);


INSERT INTO `learning_aggregated_cert_metadata` (`idCertificate`, `title`, `description`) VALUES ($0, 'Associazione al cert. $0', 'Questa è la descrizione dell\'assoc. del cert. $0, ed è di tipo percorso');

-- associazione dell'utente 11836 al percorso formativo 2
INSERT INTO `learning_aggregated_cert_coursepath` (`idAssociation`, `idUser`, `idCoursePath`) VALUES ($2, 11836, 2); 

-- associazione dell'utente 11922 al percorso formativo 2
INSERT INTO `learning_aggregated_cert_coursepath` (`idAssociation`, `idUser`, `idCoursePath`) VALUES ($2, 11922, 2); 



	Dati di test: 
		40 certificati aggregati
			per ogni cert. sono state create due associazioni, di cui una a tipo corso e una a tipo percorso, e altre 38 associazioni vuote SOLO per il certificato 15 (al fine di testare la paginazione)
			
			l'associazione di tipo corso è stata fatta per gli utenti 11836 e 11922 ai corsi di frontend e backend di formalms
			
			l'associazione di tipo percorso è stata fatta per gli stessi utenti al percorso wordpress (che contiene i corsi sviluppatore plugin e frontend)
			
			
			
			per ottenere il certificato aggregato, l'utente 11836 o 11922 dovrà completare i corsi di frontend e backend di formalms, e il percorso formativo di wordpress
			
		
*/

-- -- -- -- -- -- ---- -- -- -- -- -- ---- -- -- -- -- -- --  -- -- -- -- -- -- --  Inserimento di certificati -- -- -- -- -- -- ---- -- -- -- -- -- ---- -- -- -- -- -- ---- -- -- -- -- -- ---- -- -- --
-- totale 40

INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_1', 'Cert. 1', 'Questa è la descrizione del cert. 1', 'italian', 'Struttura del certificato 1', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_2', 'Cert. 2', 'Questa è la descrizione del cert. 2', 'italian', 'Struttura del certificato 2', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_3', 'Cert. 3', 'Questa è la descrizione del cert. 3', 'italian', 'Struttura del certificato 3', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_4', 'Cert. 4', 'Questa è la descrizione del cert. 4', 'italian', 'Struttura del certificato 4', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_5', 'Cert. 5', 'Questa è la descrizione del cert. 5', 'italian', 'Struttura del certificato 5', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_6', 'Cert. 6', 'Questa è la descrizione del cert. 6', 'italian', 'Struttura del certificato 6', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_7', 'Cert. 7', 'Questa è la descrizione del cert. 7', 'italian', 'Struttura del certificato 7', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_8', 'Cert. 8', 'Questa è la descrizione del cert. 8', 'italian', 'Struttura del certificato 8', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_9', 'Cert. 9', 'Questa è la descrizione del cert. 9', 'italian', 'Struttura del certificato 9', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_10', 'Cert. 10', 'Questa è la descrizione del cert. 10', 'italian', 'Struttura del certificato 10', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_11', 'Cert. 11', 'Questa è la descrizione del cert. 11', 'italian', 'Struttura del certificato 11', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_12', 'Cert. 12', 'Questa è la descrizione del cert. 12', 'italian', 'Struttura del certificato 12', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_13', 'Cert. 13', 'Questa è la descrizione del cert. 13', 'italian', 'Struttura del certificato 13', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_14', 'Cert. 14', 'Questa è la descrizione del cert. 14', 'italian', 'Struttura del certificato 14', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_15', 'Cert. 15', 'Questa è la descrizione del cert. 15', 'italian', 'Struttura del certificato 15', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_16', 'Cert. 16', 'Questa è la descrizione del cert. 16', 'italian', 'Struttura del certificato 16', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_17', 'Cert. 17', 'Questa è la descrizione del cert. 17', 'italian', 'Struttura del certificato 17', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_18', 'Cert. 18', 'Questa è la descrizione del cert. 18', 'italian', 'Struttura del certificato 18', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_19', 'Cert. 19', 'Questa è la descrizione del cert. 19', 'italian', 'Struttura del certificato 19', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_20', 'Cert. 20', 'Questa è la descrizione del cert. 20', 'italian', 'Struttura del certificato 20', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_21', 'Cert. 21', 'Questa è la descrizione del cert. 21', 'italian', 'Struttura del certificato 21', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_22', 'Cert. 22', 'Questa è la descrizione del cert. 22', 'italian', 'Struttura del certificato 22', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_23', 'Cert. 23', 'Questa è la descrizione del cert. 23', 'italian', 'Struttura del certificato 23', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_24', 'Cert. 24', 'Questa è la descrizione del cert. 24', 'italian', 'Struttura del certificato 24', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_25', 'Cert. 25', 'Questa è la descrizione del cert. 25', 'italian', 'Struttura del certificato 25', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_26', 'Cert. 26', 'Questa è la descrizione del cert. 26', 'italian', 'Struttura del certificato 26', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_27', 'Cert. 27', 'Questa è la descrizione del cert. 27', 'italian', 'Struttura del certificato 27', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_28', 'Cert. 28', 'Questa è la descrizione del cert. 28', 'italian', 'Struttura del certificato 28', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_29', 'Cert. 29', 'Questa è la descrizione del cert. 29', 'italian', 'Struttura del certificato 29', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_30', 'Cert. 30', 'Questa è la descrizione del cert. 30', 'italian', 'Struttura del certificato 30', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_31', 'Cert. 31', 'Questa è la descrizione del cert. 31', 'italian', 'Struttura del certificato 31', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_32', 'Cert. 32', 'Questa è la descrizione del cert. 32', 'italian', 'Struttura del certificato 32', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_33', 'Cert. 33', 'Questa è la descrizione del cert. 33', 'italian', 'Struttura del certificato 33', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_34', 'Cert. 34', 'Questa è la descrizione del cert. 34', 'italian', 'Struttura del certificato 34', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_35', 'Cert. 35', 'Questa è la descrizione del cert. 35', 'italian', 'Struttura del certificato 35', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_36', 'Cert. 36', 'Questa è la descrizione del cert. 36', 'italian', 'Struttura del certificato 36', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_37', 'Cert. 37', 'Questa è la descrizione del cert. 37', 'italian', 'Struttura del certificato 37', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_38', 'Cert. 38', 'Questa è la descrizione del cert. 38', 'italian', 'Struttura del certificato 38', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_39', 'Cert. 39', 'Questa è la descrizione del cert. 39', 'italian', 'Struttura del certificato 39', 'P', '', 1,1);
INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES (NULL, 'COD_40', 'Cert. 40', 'Questa è la descrizione del cert. 40', 'italian', 'Struttura del certificato 40', 'P', '', 1,1);


