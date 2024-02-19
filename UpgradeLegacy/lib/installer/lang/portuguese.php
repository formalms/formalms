<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

define('_INSTALLER_TITLE', 'forma.lms - Instalação');
define('_NEXT', 'Passo seguinte');
define('_BACK', 'Voltar');
define('_LOADING', 'Carregar');
define('_TRY_AGAIN', 'Tentar novamente');
define('_YES', 'Sim');
define('_NO', 'Não');
//--------------------------------------
define('_TITLE_STEP1', 'Passo 1: Seleccionar Idioma');
define('_INTRODUCTION', _TITLE_STEP1);
define('_LANGUAGE', 'Idioma');
define('_INSTALLER_INTRO_TEXT', 'formalms.org é uma companhia que desenvolveu uma plataforma e-learning open-source chamada forma.lms  adequada para oraganizações complexas, mercados corporativos, governo e saúde.
	<p><b>Principais Características</b></p>
	<ul>
		<li>Suporte Scorm 1.2 e 2004</li>
		<li>Configurável para atender vários modelos de formação (auto-formação, ensino homogéneo, aprendizagem colaborativa, aprendizagem social)</li>
		<li>Ferramenta de Autoria que permite gerir testes, Download de ficheiros de qualquer formato, páginas Web, Faq, Glossários, Coleccções Links</li>
		<li>Características de Colaboração tais como <b>Forum</b>, <b>Wiki</b>, <b>Chat</b>, <b>gestão Projectos</b>, <b>Repositório</b></li>
		<li>Gestão de Talento e Competências, análises de gaps e desenvolvimento pessoal plan</li>
		<li>Geração e impressão de certificados Pdf</li>
		<li>O suporte para interface de terceiros com software de gestão de recursos humanos (<b>SAP</b>, <b>Cezanne</b>, <b>Lotus Notes</b>, ...) e outras companhias de serviço (<b>LDAP</b>, <b>Active Directory</b>, <b>CRM</b>, <b>Erp</b> e outras soluções)</li>
		<li>Características sociais tais como <b>Google Apps</b>, <b>Facebook</b>, <b>Twitter</b> e <b>Linkedin</b></li>
		<li>Sistema totalmente customizável e relatórios de inteligência comercial</li>
		<li>Características dedicadas de sub-administradores, área e caraterísticas de gestão</li>
		<li>Suporte multi-idioma EPD(esquerda-para-direita) e DPE(direita-para-esquerda). 25 idiomas suportados</li>
		<li>Suporta dispositivos móveis</li>
	</ul>');
// ---------------------------------------
define('_TITLE_STEP2', 'Passo 2: Informação');
define('_SERVERINFO', 'Informação servidor');
define('_SERVER_SOFTWARE', 'Software servidor : ');
define('_PHPVERSION', 'Versão PHP : ');
define('_MYSQLCLIENT_VERSION', 'MySQL/MariaDB Client Version : ');
define('_MYSQLSERVER_VERSION', 'MySQL/MariaDB Server Version : ');
define('_LDAP', 'Ldap : ');
define('_ONLY_IF_YU_WANT_TO_USE_IT', 'Considere isto um aviso apenas se necessitar de usar LDAP ');
define('_OPENSSL', 'Openssl : ');
define('_WARINNG_SOCIAL', 'Consider this warning only if you use social login');

define('_PHPINFO', 'Informação PHP : ');
define('_MAGIC_QUOTES_GPC', 'magic_quotes_gpc : ');
define('_SAFEMODE', 'Safe mode : ');
define('_REGISTER_GLOBALS', 'register_global : ');
define('_UPLOAD_MAX_FILESIZE', 'upload_max_filsize : ');
define('_POST_MAX_SIZE', 'post_max_size : ');
define('_MAX_EXECUTION_TIME', 'max_execution_time : ');
define('_ALLOW_URL_FOPEN', 'allow_url_fopen : ');
define('_ALLOW_URL_INCLUDE', 'allow_url_include : ');
define('_ON', 'ON ');
define('_OFF', 'OFF ');

define('_VERSION', 'Versão forma.lms');
define('_START', 'Ínicio');
define('_END', 'Final');
// -----------------------------------------
define('_TITLE_STEP3', 'Passo 3: Licença');
define('_AGREE_LICENSE', 'Eu concordo com os termos de licença');
// -----------------------------------------
define('_TITLE_STEP4', 'Passo 4: Configuração');
define('_SITE_BASE_URL', 'Base url do website');
define('_DATABASE_INFO', 'Informação Base Dados');
define('_DB_TYPE', 'Tipo');
define('_DB_HOST', 'Endereço');
define('_DB_NAME', 'Nome Base Dados');
define('_DB_USERNAME', 'Utilizador Base Dados');
define('_DB_PASS', 'Senha');
define('_UPLOAD_METHOD', 'Método de envio ficheiros (sugerido FTP, se utiliza Windows em casa utilize HTTP)');
define('_HTTP_UPLOAD', 'Método clássico (HTTP)');
define('_FTP_UPLOAD', 'Enviar ficheiros usando FTP');
define('_FTP_INFO', 'Acesso dados FTP');
define('_IF_FTP_SELECTED', '(Se seleccionou FTP como método de Envio)');
define('_FTP_HOST', 'Endereço Servidor');
define('_FTP_PORT', 'Número porta (geralmente é correcta)');
define('_FTP_USERNAME', 'Nome Utilizador');
define('_FTP_PASS', 'Senha');
define('_FTP_CONFPASS', 'Confirmar senha');
define('_FTP_PATH', 'Pasta FTP (é a raíz onde estão os ficheiros guardados, ex. /htdocs/ /mainfile_html/');
define('_CANT_CONNECT_WITH_DB', 'Não é possível conectar á BD, por favor verifique os dados inseridos');
define('_CANT_SELECT_DB', 'Não é possível seleccionar BD, por favor verifique os dados inseridos');
define('_DB_WILL_BE_CREATED', 'BD será criado');
define('_CANT_CONNECT_WITH_FTP', 'Não é possível conectar no FTP para o servidor especificado, por favor verifique os parâmetros inseridos');
// -----------------------------------------
define('_TITLE_STEP5', 'Step 5: Configuração');
define('_ADMIN_USER_INFO', 'Informações sobre o utilizador administrador');
define('_ADMIN_USERNAME', 'Nome Utilizador');
define('_ADMIN_FIRSTNAME', 'Primeiro Nome');
define('_ADMIN_LASTNAME', 'Último Nome');
define('_ADMIN_PASS', 'Senha');
define('_ADMIN_CONFPASS', 'Confirmar Senha');
define('_ADMIN_EMAIL', 'E-mail');
define('_LANG_TO_INSTALL', 'Idiomas para instalar');
// -----------------------------------------
define('_TITLE_STEP6', 'Step 6: Configuação dados Base Dados');
define('_DATABASE', 'Base Dados');
define('_DB_IMPORTING', 'Importar Base Dados');
define('_LANGUAGES', 'Idiomas');
// -----------------------------------------
define('_TITLE_STEP7', 'Step 7: Configuração SMTP');
define('_SMTP_INFO', "É possível definir a configuração do SMTP a partir da área administrativa (BD) ou do arquivo de config.");
define('_USE_SMTP_DATABASE', 'Configurações SMTP na Base Dados');
define('_USE_SMTP', 'Usa SMTP');
define('_SMTP_HOST', 'Host SMTP');
define('_SMTP_PORT', 'Porta SMTP');
define('_SMTP_SECURE', 'Tipo de segurança');
define('_SMTP_AUTO_TLS', 'Configuração Auto TLS SMTP');
define('_SMTP_USER', 'Usuário SMTP');
define('_SMTP_PWD', 'Senha SMTP');
define('_SMTP_DEBUG', 'Debug SMTP');
define('_CANT_CONNECT_SMTP', 'Não é possível conectar no SMTP para o servidor especificado');
// -----------------------------------------
define('_TITLE_STEP8', 'Step 8: Instalação completa');
define('_INSTALLATION_COMPLETED', 'Instalação foi concluída');
define('_INSTALLATION_DETAILS', 'Detalhes');
define('_SITE_HOMEPAGE', 'Home');
define('_REVEAL_PASSWORD', 'Revelar senha');
define('_COMMUNITY', 'Comunidade');
define('_COMMERCIAL_SERVICES', 'Serviços Comerciais');
define('_CONFIG_FILE_NOT_SAVED', 'O instalador foi incapaz de guardar o ficheiro config.php, faça download novamente e substitua-o online.');
define('_DOWNLOAD_CONFIG', 'Download Configuração');
define('_CHECKED_DIRECTORIES', 'Alguns directórios onde os ficheiros estão guardados não existem ou não têm permissão correcta');
define('_CHECKED_FILES', 'Certos ficheiros não têm permissão adequada');
// -----------------------------------------
define('_UPGRADER_TITLE', 'forma.lms - Actualizado');
define('_UPGRADE_CONFIG', 'Actualizar ficheiro config.php');
define('_UPG_CONFIG_OK', 'Ficheiro config.php actualizado com sucesso');
define('_UPG_CONFIG_NOT_CHANGED', 'Config.php already updated');
define('_UPG_CONFIG_NOT_SAVED', 'O processo de actualização para config.php falhou.');
define('_UPGRADING', 'Actualização em progresso');
define('_UPGRADING_LANGUAGES', 'Actualizar idiomas');
define('_UPGRADE_COMPLETE', 'Actualização concluída');
