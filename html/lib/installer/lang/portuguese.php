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

define('_ADMIN_CONFPASS', 'Confirmar Senha');
define('_ADMIN_EMAIL', 'Endereço de E-mail');
define('_ADMIN_FIRSTNAME', 'Primeiro Nome');
define('_ADMIN_LASTNAME', 'Último Nome');
define('_ADMIN_PASS_DOESNT_MATCH', 'As senhas não coincidem');
define('_ADMIN_PASS_REQ', 'Senha requerida');
define('_ADMIN_PASS', 'Senha');
define('_ADMIN_USER_INFO', 'Informações sobre o utilizador administrador');
define('_ADMIN_USERID_REQ', 'Nome de utilizador requerido');
define('_ADMIN_USERNAME', 'Nome de Utilizador');
define('_ADMINISTRATION_TYPE', 'Tipo de administração');
define('_AGREE_LICENSE', 'Eu concordo com os termos de licença');
define('_ALLOW_URL_FOPEN', 'allow_url_fopen');
define('_ALLOW_URL_INCLUDE', 'allow_url_include');
define('_ALLOW_URL_INCLUDE', 'allow_url_include');
define('_ANSWER_NO', 'Não');
define('_ANSWER_YES', 'Sim');
define('_ASSESSMENT_FUNCTION_REMOVED', 'A função de avaliação foi removida');
define('_BACK', 'Voltar');
define('_CANT_CONNECT_SMTP', 'Não é possível conectar no SMTP para o servidor especificado');
define('_CANT_CONNECT_WITH_DB', 'Não é possível conectar á BD, por favor verifique os dados inseridos');
define('_CANT_CONNECT_WITH_FTP', 'Não é possível conectar no FTP para o servidor especificado, por favor verifique os parâmetros inseridos');
define('_CANT_SELECT_DB', 'Não é possível seleccionar BD, por favor verifique os dados inseridos');
define('_CHECKED_DIRECTORIES', 'Alguns directórios onde os ficheiros estão guardados não existem ou não têm permissão correcta');
define('_CHECKED_FILES', 'Certos ficheiros não têm permissão adequada');
define('_CMS', 'forma.lms Content Management System');
define('_COMMERCIAL_SERVICES', 'Serviços Comerciais');
define('_COMMUNITY', 'Comunidade');
define('_CONFIGURATION', 'Configuração');
define('_CONFIG_FILE_NOT_SAVED', 'O instalador foi incapaz de guardar o ficheiro config.php, faça download novamente e substitua-o online.');
define('_CRITICAL_ERROR_UPGRADE_SUSPENDED', "Erro crítico na atualização, falha na atualização. Foi interrompida");
define('_CRITICAL_ERROR', 'Erro crítico ');
define('_DANGER', 'Perigo - Definir como OFF');
define('_DATABASE_INFO', 'Informação da Base de Dados');
define('_DATABASE', 'Base de Dados');
define('_DB_CONFPASS', 'Confirmar senha');
define('_DB_HOST', 'Endereço');
define('_DB_IMPORT_FAILED', 'Ocorreram erros ao carregar o banco de dados');
define('_DB_IMPORT_OK', 'Banco de dados carregado com sucesso');
define('_DB_IMPORTING', 'Importar Base de Dados');
define('_DB_NAME', 'Nome da Base Dados');
define('_DB_NOT_EMPTY', 'O banco de dados especificado não está vazio');
define('_DB_PASS', 'Senha');
define('_DB_TYPE', 'Tipo');
define('_DB_USERNAME', 'Utilizador da Base de Dados');
define('_DB_WILL_BE_CREATED', 'BD será criado');
define('_DEFAULT_PLATFORM', 'Aplicativo por defeito (página inicial)');
define('_DOINSTALL', 'Instalar');
define('_DOMXML_REQUIRED', 'Para instalar a suíte forma.lms você precisa ter o módulo DOMXML ou PHP 5 ou posterior instalado.');
define('_DOMXML', 'domxml()');
define('_DOWNLOAD_CONFIG', 'Download da Configuração');
define('_ECOM', 'forma.lms E-Commerce');
define('_EMPTY_DIRECTORIES', '');
define('_END', 'Final');
define('_FAILED_OPERATION', "Falha na operação, código de erro");
define('_FILEINFO', 'Suporte a Fileinfo');
define('_FINISH', 'Fim');
define('_FRAMEWORK', 'forma.lms Core Framework');
define('_FTP_CONFPASS', 'Confirmar Senha');
define('_FTP_HOST', 'Endereço do Servidor');
define('_FTP_INFO', 'Dados de Acesso FTP');
define('_FTP_PASS', 'Senha');
define('_FTP_PATH', 'Pasta FTP (é a raiz onde estão os ficheiros guardados, ex. /htdocs/ /mainfile_html/');
define('_FTP_PORT', 'Número da porta (geralmente está correcta)');
define('_FTP_UPLOAD', 'Enviar ficheiros usando FTP');
define('_FTP_USERNAME', 'Nome de Utilizador');
define('_HTTP_UPLOAD', 'Método clássico (HTTP)');
define('_IF_FTP_SELECTED', '(Se seleccionou FTP como Método de Envio)');
define('_INSTALL', 'Instalação');
define('_INSTALLATION_COMPLETED', 'Instalação foi concluída');
define('_INSTALLATION_DETAILS', 'Detalhes da instalação');
define('_INSTALLED_APPS', 'Aplicativos instalados');
define('_INSTALLER_INTRO_TEXT', 'formalms.org é uma companhia que desenvolveu uma plataforma e-learning open-source chamada forma.lms  adequada para oraganizações complexas, mercados corporativos, governo e saúde.
<p><b>Principais Características</b></p>
<ul>
	<li>Suporte a Scorm 1.2 e 2004</li>
	<li>Configurável para atender a vários modelos de formação (auto-formação, ensino homogéneo, aprendizagem colaborativa, aprendizagem social)</li>
	<li>Ferramenta de Autoria que permite gerir testes, Download de ficheiros de qualquer formato, páginas Web, FAQ, Glossários, Coleccções de Links</li>
	<li>Características de Colaboração tais como <b>Fórum</b>, <b>Wiki</b>, <b>Chat</b>, <b>Gestão de Projetos</b>, <b>Repositório</b></li>
	<li>Gestão de Talento e Competências, análises de <i>gaps</i> e plano de desenvolvimento pessoal</li>
	<li>Geração e impressão de certificados PDF</li>
	<li>Suporte para interface de terceiros com software de gestão de recursos humanos (<b>SAP</b>, <b>Cezanne</b>, <b>Lotus Notes</b>, ...) e outras companhias de serviço (<b>LDAP</b>, <b>Active Directory</b>, <b>CRM</b>, <b>ERP</b> e outras soluções)</li>
	<li>Características sociais tais como <b>Google Apps</b>, <b>Facebook</b>, <b>Twitter</b> e <b>Linkedin</b></li>
	<li>Sistema totalmente customizável e relatórios de inteligência comercial</li>
	<li>Características dedicadas de sub-administradores, área e caraterísticas de gestão</li>
	<li>Suporte multi-idioma LTR (esquerda-para-direita) e RTL (direita-para-esquerda). 25 idiomas suportados</li>
	<li>Suporte a dispositivos móveis</li>
</ul>');
define('_INSTALLER_TITLE', 'forma.lms - Instalação');
define('_INTRODUCTION', 'Introdução');
define('_INVALID_CONFIG_FILE', 'Arquivo config.php inválido; certifique-se de que seja a versão do forma.lms especificada em "Inicial"');
define('_INVALID_DEFAULTSENDEREMAIL', 'Endereço de e-mail do remetente por defeito inválido.');
define('_INVALID_EMAIL', 'O endereço de email não é válido.');
define('_INVALID_PASSWORD', 'Senha inválida ou as senhas não coincidem.');
define('_INVALID_SITEBASEURL', "URL base do site inváldia; o endereço deve terminar com \"/\".");
define('_INVALID_USERNAME', 'Nome de utilizador inválido.');
define('_IS_PRESENT_DIRECTORIES', 'Ainda existem diretórios que não são mais utilizados, recomendamos que você os exclua');
define('_JUMP_TO_CONTENT', 'Ir para o conteúdo');
define('_KMS', 'forma.lms Knowledge Management System');
define('_LACKING_DIRECTORIES', 'Faltam alguns diretórios contendo os aplicativos da suíte, sem essas pastas não será possível. possível usar o sistema');
define('_LANG_INSTALLED', 'O idioma foi instalado');
define('_LANG_TO_INSTALL', 'Idiomas para instalar');
define('_LANGUAGE', 'Idioma');
define('_LANGUAGES', 'Idiomas');
define('_LDAP', 'LDAP');
define('_LEARNING_NEWS_REMOVED', 'A função de notícias de login e a tabela relacionada foram removidas');
define('_LESS_THAN150', 'Entre 50 e 150');
define('_LESS_THAN50', 'Menos de 50');
define('_LMS_ENABLE_EVENT_UI', 'Os utilizadores poderão configurar quais notificações receberão');
define('_LMS_ENABLE_GROUPSUB_UI', 'Os utilizadores poderão configurar se desejam ou não ingressar em grupos');
define('_LMS', 'forma.lms Learning Management System');
define('_LOADING', 'Carregar');
define('_MAGIC_QUOTES_GPC', 'magic_quotes_gpc');
define('_MAX_EXECUTION_TIME', 'max_execution_time');
define('_MBSTRING', 'Suporte a Multibyte');
define('_MIME_CONTENT_TYPE', 'Suporte a mime_content_type()');
define('_MORE_THAN_ONE_BRANCH', 'A sua empresa/associação tem mais de um local?');
define('_MORE_THAN150', 'Mais do que 150');
define('_MUST_ACCEPT_LICENSE', "Você deve aceitar os termos da licença do software para continuar.");
define('_MYSQLCLIENT_VERSION', 'Versão do Cliente MySQL/MariaDB');
define('_MYSQLSERVER_VERSION', 'Versão do Servidor MySQL/MariaDB');
define('_NEXT_IMPORT_LANG', "Agora os idiomas serão importados; a operação pode demorar algum tempo (até mais de um minuto por módulo). Não feche o navegador e clique em \"Passo seguinte\" SOMENTE depois que a página estiver totalmente carregada. Se a operação falhar, peça ao provedor ou administrador do servidor para aumentar o tempo máximo de execução ou utilize o procedimento manual");
define('_NEXT_STEP', 'Passo seguinte ');
define('_NEXT', 'Passo seguinte');
define('_NO_LANG_SELECTED', 'Nenhum idioma selecionado');
define('_NO', 'Não');
define('_NOTAVAILABLE', 'Não disponível');
define('_NOTSCORM', "Este servidor não suporta DOMXML para PHP 4 e não é PHP 5, você não pode instalar o forma.lms aqui, peça ao seu provedor para instalar a extensão DOMXML");
define('_NUMBER_ESTIMATED_USERS', 'Número estimado de utilizadores registados');
define('_OFF', 'OFF ');
define('_ON', 'ON ');
define('_ONE_ADMIN', 'Apenas um administrador');
define('_ONLY_IF_YU_WANT_TO_USE_IT', 'Considere isto um aviso apenas se necessitar de usar LDAP ');
define('_OPENSSL', 'OpenSSL');
define('_PHP_TIMEZONE', 'Fuso Horário de Instalação');
define('_PHPINFO', 'Informação do PHP');
define('_PHPVERSION', 'Versão do PHP');
define('_PLATFORM', 'Applicativo');
define('_POST_MAX_SIZE', 'post_max_size');
define('_REFRESH', 'Atualizar');
define('_REG_TYPE_ADMIN', "Somente o administrador pode registar novos utilizadores");
define('_REG_TYPE_FREE', 'Registo liberado');
define('_REG_TYPE_MOD', 'Registo moderado');
define('_REGISTER_GLOBALS', 'register_global : ');
define('_REGISTRATION_TYPE', 'Tipo de registo');
define('_REMOVE_INSTALL_FOLDERS_AND_WRITE_PERM', '<b>Atenção:</b> Antes de prosseguir, exclua o diretório install/ do site e remova as permissões de gravação do arquivo config.php, configurando-as para 644');
define('_REQUIRE_ACCESSIBILITY', "É necessária a conformidade com os padrões de acessibilidade?");
define('_REVEAL_PASSWORD', 'Revelar senha');
define('_SAFEMODE', 'Safe mode');
define('_SCS', 'forma.lms Syncronous Collaborative System');
define('_SELECT_LANGUAGE', 'Seleccione seu idioma');
define('_SELECT_WHATINSTALL', 'Seleccione quais aplicativos você deseja instalar');
define('_SERVER_ADDR', 'Endereço do servidor');
define('_SERVER_ADMIN', 'Administrador do servidor');
define('_SERVER_NAME', 'Nome do servidor');
define('_SERVER_PORT', 'Porta do servidor');
define('_SERVER_SOFTWARE', 'Software do Servidor');
define('_SERVERINFO', 'Informação do Servidor');
define('_SIMPLIFIED_INTERFACE', 'Opções de simplificação de interface');
define('_SITE_BASE_URL', 'URL base do site (não alterar)');
define('_SITE_DEFAULT_SENDER', 'Remetente por defeito de mensagens de e-mail automáticas');
define('_SITE_HOMEPAGE', 'Página inicial');
define('_SMTP_AUTO_TLS', 'Configuração automática de TLS SMTP');
define('_SMTP_DEBUG', 'Debug SMTP');
define('_SMTP_HOST', 'Host SMTP');
define('_SMTP_INFO', "É possível definir a configuração do SMTP a partir da área administrativa (BD) ou do arquivo de config.");
define('_SMTP_PORT', 'Porta SMTP');
define('_SMTP_PWD', 'Senha SMTP');
define('_SMTP_SECURE', 'Tipo de segurança');
define('_SMTP_USER', 'Usuário SMTP');
define('_SOFTWARE_LICENSE', 'Licença de software para aplicativos forma.lms');
define('_SQL_STRICT_MODE_WARN', 'O modo <a href="http://dev.mysql.com/doc/en/server-sql-mode.html" target="_blank">strict mode</a> do MySQL está ativo; forma.lms não é compatível, desative-o');
define('_SQL_STRICT_MODE', 'MySQL <a href="http://dev.mysql.com/doc/en/server-sql-mode.html" target="_blank">strict mode</a>');
define('_START', 'Início');
define('_SUB_ADMINS', 'Administrador e subadministradores');
define('_SUCCESSFULL_OPERATION', 'Operação bem-sucedida para');
define('_TITLE_STEP1', 'Passo 1: Seleccionar Idioma');
define('_TITLE_STEP2', 'Passo 2: Verificar informação do sistema');
define('_TITLE_STEP3', 'Passo 3: Licença');
define('_TITLE_STEP4', 'Passo 4: Configuração');
define('_TITLE_STEP5', 'Passo 5: Personalização da instalação');
define('_TITLE_STEP6', 'Passo 6: Configuação da base de dados');
define('_TITLE_STEP7', 'Passo 7: Configuração SMTP');
define('_TITLE_STEP8', 'Passo 8: Instalação completada');
define('_TO_ADMIN', "Para aceder a interface de administração clique no link a seguir");
define('_TO_WEBSITE', 'Para aceder ao site principal clique no link a seguir');
define('_TRY_AGAIN', 'Tentar novamente');
define('_UPG_CONFIG_NOT_CHANGED', 'Config.php já atualizado');
define('_UPG_CONFIG_NOT_SAVED', 'O processo de actualização para config.php falhou.');
define('_UPG_CONFIG_OK', 'Ficheiro config.php actualizado com sucesso');
define('_UPGRADE_COMPLETE', 'Actualização concluída');
define('_UPGRADE_CONFIG', 'Actualizar ficheiro config.php');
define('_UPGRADE_NOT_NEEDED_FILE_IS_LATER', "Parece que sua versão do forma.lms é posterior à actualização, nenhuma actualização é necessária.");
define('_UPGRADE_NOT_NEEDED', "Você já possui a versão mais recente do forma.lms; não há necessidade de actualizar.");
define('_UPGRADER_TITLE', 'forma.lms - Actualizado');
define('_UPGRADING_LANGUAGES', 'Actualizar idiomas');
define('_UPGRADING', 'Actualização em progresso');
define('_UPLOAD_MAX_FILESIZE', 'upload_max_filsize : ');
define('_UPLOAD_METHOD', 'Método de envio de ficheiros (sugerido FTP, se utiliza Windows utilize HTTP)');
define('_USE_SMTP_DATABASE', 'Configurações SMTP na Base de Dados');
define('_USE_SMTP', 'Usar SMTP');
define('_USEFUL_LINKS', 'Links úteis');
define('_VERSION', 'Versão do forma.lms');
define('_WARINNG_SOCIAL', 'Considere este aviso apenas se você usar login social');
define('_WARNING_NOT_INSTALL', '<b>Atenção</b>: se você desmarcar um aplicativo ele não estará disponível, mas você pode instalá-lo no futuro usando o procedimento automático.');
define('_WARNINGS', 'Avisos');
define('_WEBSITE_INFO', 'Informações no site');
define('_YES', 'Sim');
define('_YOU_DONT_HAVE_FUNCTION_OVERLOAD', 'a função de overload não está ativa, para que funcione é necessário ter uma versão do PHP maior que 4.3.0 instalada. Linux Mandriva não é compilado com a função de overload, procure um arquivo com um nome semelhante a php4-overload-xxxxx.mdk e instale o módulo você mesmo; o Fedora Core 4 tem um bug <a href="http://download.fedora.redhat.com/pub/fedora/linux/core/updates/4/" target="_blank">que precisa ser corrigido</a>. Se você estiver no Windows, recomendamos instalar o <a href="http://www.easyphp.org" target="_blank">EasyPHP 1.8</a>.');
define('REMOVE_INSTALL_FOLDER', 'Sugere-se remover o diretório de instalação, o Forma fica vulnerável até que esteja acessível.');
define('WARNING_PUB_ADMIN_DELETED', 'Administradores públicos serão eliminados');
