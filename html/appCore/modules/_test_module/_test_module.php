<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

function formatter_userid(&$column, &$record, &$data, $args = false) {
	$acl = new DoceboACLManager();
	$output = $acl->relativeId($data);
	return $output;
}

function dispatch($op) {
		
	switch ($op) {

		case "tab" : {
			YuiLib::load(
				array('tabview'=>'tabview-min.js'),
				array('tabview/assets/skins/sam/' => 'tabview.css')
			);
			require_once($GLOBALS['where_framework'].'/lib/user_selector/lib.basetree.php');
			require_once($GLOBALS['where_framework'].'/lib/user_selector/lib.groupselectortable.php');
			require_once($GLOBALS['where_framework'].'/lib/user_selector/lib.userselectortable.php');
			require_once($GLOBALS['where_framework'].'/lib/user_selector/lib.dynamicuserfilter.php');

			cout(getTitleArea(array('Test manager e selettore utenti')));
			cout('<div class="std_block">');

			$bt = new BaseTree('user_orgchart', false, false, _TREE_COLUMNS_TYPE_RADIO);
			$bt->init();
			$bt->setInitialSelection();
			$bt_out = $bt->get();

			$gst = new GroupSelectorTable('group_table');
			$gst->init();
			$gst_out = $gst->get();

			$ust = new UserSelectorTable('user_table');
			$ust->init();
			$ust_out = $ust->get();

			$duf = new DynamicUserFilter('user_rules');
			$duf->init();
			$duf_out = $duf->get();

			cout($bt_out['js'] ,'page_head');
			cout($gst_out['js'] ,'page_head');
			cout($ust_out['js'] ,'page_head');
			cout($duf_out['js'] ,'page_head');

			cout('<div id="'.$this->id.'" class="yui-navset">
				<ul class="yui-nav">
					<li><a href="#tab1"><em>Organigramma</em></a></li>
					<li class="selected"><a href="#tab2"><em>Utenti</em></a></li>
					<li><a href="#tab3"><em>Gruppi</em></a></li>
					<li><a href="#tab4"><em>Regole</em></a></li>
				</ul>
				<div class="yui-content">
					<div id="tab1">'.$bt_out['html'].'</div>
					<div id="tab2">'.$ust_out['html'].'</div>
					<div id="tab3"><p>'.$gst_out['html'].'</p></div>
					<div id="tab4">'.$duf_out['html'].'</div>
				</div>
			</div>');

			cout('<script type="text/javascript">
				var tabView = new YAHOO.widget.TabView(\'demo\');
			</script>');

			cout('</div>');

		};break;

		case "final" : {
			require_once(_base_.'/lib/lib.form.php');
			require_once($GLOBALS['where_framework'].'/lib/user_selector/lib.fulluserselector.php');

			$selector = new FullUserSelector('selector');
			$selector->init();
			$temp = $selector->get();

			cout(getTitleArea(array('Selettore utenti completo')));
			cout('<div class="std_block">');

			cout(Form::openForm('test', 'index.php?modname=_test_module&op=resp_to_form'));

			cout($temp['js'], 'page_head');
			cout($temp['html']);

			cout(
				Form::openButtonSpace().
				Form::getButton('save', 'save', 'SALVA').
				Form::getButton('undo', 'undo', 'ANNULLA').
				Form::closeButtonSpace()
			);

			cout(Form::closeForm());

			cout('</div>');
		} break;
		
		
//------------------------------------------------------------------------------



		case 'dataexport': {
			require_once(_base_.'/lib/dataexport/lib.dataexport.php');

			$lang =& DoceboLanguage::CreateInstance('standard', 'framework');

			$query = "SELECT * FROM core_user ORDER BY lastname LIMIT 0,20 ";
			$source = new DataSource_Query($query);

			$nameGroup = array();
			$nameGroup[] = new DataColumn('lastname', $lang->def('_LASTNAME'));
			$nameGroup[] = new DataColumn('firstname', $lang->def('_FIRSTNAME'));

			$columns = array();
			$columns[] = new DataColumn('idst', $lang->def('_ID'));
			$columns[] = new DataColumnGroup('name', $lang->def('_NAME'), $nameGroup);
			$columns[] = new DataColumn('userid', $lang->def('_USERNAME'), 'formatter_userid');
			$columns[] = new DataColumn('email', $lang->def('_EMAIL'));
			//$columns[] = new DataColumn();


			$export = new DataExport(DATATYPE_HTM, 'users', $columns, $source);
			cout($export->render());

			cout('<br /><br /><a href="index.php?modname=_test_module&amp;op=dataexportcsv">SCARICA IN CSV</a>');

		} break;


		case 'dataexportcsv': {
			require_once(_base_.'/lib/lib.download.php');
			require_once(_base_.'/lib/dataexport/lib.dataexport.php');
			$lang =& DoceboLanguage::CreateInstance('standard', 'framework');
			$query = "SELECT * FROM core_user ORDER BY lastname LIMIT 0,20 ";
			$source = new DataSource_Query($query);
			$nameGroup = array();
			$nameGroup[] = new DataColumn('lastname', $lang->def('_LASTNAME'));
			$nameGroup[] = new DataColumn('firstname', $lang->def('_FIRSTNAME'));
			$columns = array();
			$columns[] = new DataColumn('idst', $lang->def('_ID'));
			$columns[] = new DataColumnGroup('name', $lang->def('_NAME'), $nameGroup);
			$columns[] = new DataColumn('userid', $lang->def('_USERNAME'), 'formatter_userid');
			$columns[] = new DataColumn('email', $lang->def('_EMAIL'));
			//$columns[] = new DataColumn();
			$export = new DataExport(DATATYPE_XLS, 'users', $columns, $source);
			sendStrAsFile($export->render(), "export_utenti.xls");
		} break;



		case 'sample': {
			$libs = YuiLib::load(false, false, true);
			$GLOBALS['page']->add($libs, 'page_head');
			$GLOBALS['page']->add(Util::get_css('../yui-skin/datatable.css'), 'page_head');
			Util::get_js(Get::rel_path('base').'/docebocore/modules/_test_module/sample.js', true, true);
			$script = 'YAHOO.util.Event.onDOMReady(function(e) {
					initTable();
				});';
			//$GLOBALS['page']->add('<p>TITLE</p>');
			$GLOBALS['page']->add('<div id="datatable"></div>');
			$GLOBALS['page']->add('<script type="text/javascript">'.$script.'</script>');
		} break;


		case 'datatable': {
				require_once(_lms_.'/lib/table_view/class.coursetableview.php');

				$_temp_ = array(
						array("idCourse"=>0, "code"=>"codice_001", "name"=>"nome_001", "status"=>"ok", "subscriptions"=>10),
						array("idCourse"=>1, "code"=>"codice_002", "name"=>"nome_002", "status"=>"ok", "subscriptions"=>20),
						array("idCourse"=>2, "code"=>"codice_003", "name"=>"nome_003", "status"=>"ok", "subscriptions"=>30),
						array("idCourse"=>3, "code"=>"codice_004", "name"=>"nome_004", "status"=>"no", "subscriptions"=>40),
						array("idCourse"=>4, "code"=>"codice_005", "name"=>"nome_005", "status"=>"ok", "subscriptions"=>50)
				);


				require_once(_lms_.'/lib/table_view/class.coursetableview.php');
				$tableView = new CourseTableView("courses_table");
				$tableView->useDOMReady = true; //to change
				$tableView->isGlobalVariable = true; //just for debug purpose
				$tableView->initLibraries();
				$tableView->setInitialData($_temp_);
				$temp = $tableView->get();
				cout($temp['js'], 'page_head');
				cout('<div style="border:solid 1px black; padding:8px;"><p>DATATABLE:</p>'.$temp['html'].'</div>');


		} break;

		case "catalogue" : {
			YuiLib::load();

			cout('<div class="area_block"><h1 class="main_title_dashboard" id="main_area_title">Catalogo corsi</h1></div>', 'content');
			cout('<div class="std_block">', 'content');
			cout('<div id="course_cat" class="">', 'content');

			cout('<ul class="">
					<li class="selected"><a href="#tab1"><em>Inviti</em></a></li>
					<li><a href="#tab2"><em>Nuovi</em></a></li>
					<li><a href="#tab3"><em>Consigliati</em></a></li>
					<li><a href="#tab4"><em>Completo</em></a></li>
					<li><a href="#tab5"><em>Calendario</em></a></li>
				</ul>
				<div class="yui-content">
					<div>'.Get::img(Get::rel_path('base').'/mycourses.jpg', false, false, false, true).'</div>
					<div>
						<p>Lorem ipsum dolor sit amet consectetuer accumsan enim tempor neque urna. Tempus interdum euismod felis mauris Aliquam et vitae elit vel leo. Accumsan Phasellus sit natoque rutrum nibh auctor eu neque porta tincidunt. Ipsum enim ut felis nunc Pellentesque sed malesuada justo nec nec. Sem justo dolor mattis porta Quisque.</p>
						<p>Interdum ut diam convallis Sed hendrerit est augue eget ipsum lacinia. Et at montes Sed est nec arcu cursus congue neque quis. Sagittis nec dictum nibh urna non urna justo consectetuer accumsan pretium. A risus velit ante id Donec nibh eros vitae at amet. Enim et hac Nam mus tellus consequat sapien eros nec sapien. Wisi Integer sapien suscipit tincidunt et tincidunt eu et neque et. Semper nisl et.</p>
						<p>Justo nunc et Maecenas dictum Vestibulum vel a neque libero non. Hendrerit metus Vestibulum Pellentesque consectetuer augue malesuada Ut Vestibulum Vestibulum scelerisque. Elit tellus enim purus nascetur Cum condimentum est vitae pellentesque pellentesque. Nisl pretium vel dolor Integer et pharetra elit nulla et nonummy. Phasellus tempus malesuada cursus ipsum urna consectetuer ut quis condimentum consequat. Parturient pretium convallis accumsan.</p>
						<p>Turpis vitae turpis lorem dignissim quis lorem rutrum pede mus justo. Morbi dictumst interdum ut dui elit faucibus ac tempor eget a. Pede penatibus urna mus id pellentesque commodo amet porta risus pede. Sapien semper congue nibh sit tortor enim nibh amet quis in. Vivamus condimentum egestas dictumst vel auctor ut Aenean malesuada mattis convallis. Ipsum Pellentesque libero Nullam Donec nec at enim faucibus sit orci. </p>
					</div>
					<div>'.Get::img(Get::rel_path('base').'/mycourses.jpg', false, false, false, true).'</div>
					<div>'.Get::img(Get::rel_path('base').'/mycourses.jpg', false, false, false, true).'</div>
					<div>'.Get::img(Get::rel_path('base').'/calendar.jpg', false, false, false, true).'</div>
				</div>'
			, 'content');

			cout('</div>', 'content');

			cout(''
				.'<script type="text/javascript">'."\n"
			//	."	var myTabs = new YAHOO.widget.TabView('course_cat'); "."\n"
				.'</script>'."\n"
			, 'scripts');
		};break;


		default: {

			YuiLib::load();
			
			//cout('<div class="area_block">PROVA ALBERO</div>', 'content');
			cout('<div style="margin: 2em">', 'content');
			cout('<div id="course_tag" class="yui-navset">', 'content');

cout('<style>
.subcatbox {
	margin-bottom: 20px;
	font-size: 86%;
}

.subcatbox dt {
	font-weight: bold;
	font-size: 108%;
	margin-bottom: 4px;
}

.subcatbox dd {
	margin-left: 10px;
	margin-bottom: 4px;
}


</style>', 'page_head');

cout('
    <ul class="yui-nav">
        <li><a href="#tab1"><em>Corsi</em></a></li>
        <li class="selected"><a href="#tab2"><em>Documenti e multimedia</em></a></li>
        <li><a href="#tab3"><em>Videconferenze</em></a></li>
    </ul>            
    <div class="yui-content yui-nopadding">
		<div>
        	<div class="subtab_list">
				<ul class="">
					<li class="selected"><a href="#tab1"><em>In itinere</em></a></li>
					<li><a href="#tab1"><em>Completati</em></a></li>

					<li><a href="#tab1"><em>Inviti</em></a></li>
					<li><a href="#"><em>Nuovi</em></a></li>
					<li><a href="#"><em>Consigliati</em></a></li>
					<li><a href="#"><em>Tutti</em></a></li>
					<li><a href="#"><em>Calendario</em></a></li>
				</ul>
			</div>
			'.Get::img(Get::rel_path('base').'/mycourses.jpg', false, false, false, true).'
		</div>
		<div>
        	<div class="subtab_list">
				<ul class="">
					<li class="selected"><a href="#tab1"><em>Ricerca</em></a></li>
					<li><a href="#tab1"><em>Gestione</em></a></li>
				</ul>
			</div>
			<br />
			<br />
			<div style="text-align: center; position:relative;">
			 
			<input type="text" size="40" id="c_filter" name="c_filter" value="Cerca ..." class="" maxlength="255" alt="Cerca" onclick="this.value=\'\';" /><input type="submit" id="c_filter" name="c_filter" value="Cerca" class="search_b" maxlength="255" alt="Cerca" />
			<br/><br/>
			<input type="radio" name="searchin" value="2" checked="checked" /> Nei documenti e nei corsi &nbsp;&nbsp;
			<input type="radio" name="searchin" value="0" /> Nei documenti &nbsp;&nbsp;
			<input type="radio" name="searchin" value="1" /> Nei corsi
			</div>

			<br />
			<br />
			<br />
			<div class="yui-gb" style="margin: 0 22px">
				<div class="yui-u first">

					<!-- sample code from ciao.it-->

					<dl class="subcatbox"><dt> <a href="http://www.ciao.it/Portatili_206481_2" id="Node_Category_197256" class="hdl">Portatili</a></dt><dd> <a href="http://www.ciao.it/Portatili_206481_2-apple" class="subnr">Portatili Apple</a> <span class="subnr">(104)</span></dd><dd> <a href="http://www.ciao.it/Portatili_206481_2-sony" class="subnr">Portatili Sony</a> <span class="subnr">(383)</span></dd><dd> <a href="http://www.ciao.it/Portatili_206481_2-dell" class="subnr">Portatili Dell</a> <span class="subnr">(81)</span></dd><dd> <a href="http://www.ciao.it/Portatili_206481_2-fujitsu_siemens" class="subnr">Portatili Fujitsu-Siemens</a> <span class="subnr">(400)</span></dd><dd> <a href="http://www.ciao.it/Portatili_206481_2-hp" class="subnr">Portatili HP</a> <span class="subnr">(799)</span></dd><dd> <a href="http://www.ciao.it/Portatili_206481_2-asus" class="subnr">Portatili Asus</a> <span class="subnr">(663)</span></dd><dd> <a href="http://www.ciao.it/Portatili_206481_2-acer" class="subnr">Portatili Acer</a>
					<span class="subnr">(713)</span></dd><dd> <a href="http://www.ciao.it/Portatili_206481_2-toshiba" class="subnr">Portatili Toshiba</a> <span class="subnr">(737)</span></dd><dd> <a href="http://www.ciao.it/Portatili_206481_2-samsung" class="subnr">Portatili Samsung</a> <span class="subnr">(43)</span></dd></dl><dl class="subcatbox"><dt> <a href="http://www.ciao.it/PC_178183_2" id="Node_Category_168457" class="hdl">PC</a></dt><dd> <a href="http://www.ciao.it/PC_178183_2-hp" class="subnr">PC HP</a> <span class="subnr">(1643)</span></dd><dd> <a href="http://www.ciao.it/PC_178183_2-acer" class="subnr">PC Acer</a> <span class="subnr">(649)</span></dd><dd> <a href="http://www.ciao.it/PC_178183_2-packard_bell" class="subnr">PC Packard Bell</a> <span class="subnr">(864)</span></dd><dd> <a href="http://www.ciao.it/PC_178183_2-apple" class="subnr">PC Apple</a> <span class="subnr">(227)</span></dd><dd> <a href="http://www.ciao.it/PC_178183_2-olidata" class="subnr">PC Olidata</a> <span class="subnr">(574)</span></dd><dd>
					<a href="http://www.ciao.it/PC_178183_2-compaq" class="subnr">PC Compaq</a> <span class="subnr">(627)</span></dd></dl><dl class="subcatbox"><dt> <a href="http://www.ciao.it/Stampanti_178158_2" id="Node_Category_168459" class="hdl">Stampanti</a></dt><dd> <a href="http://www.ciao.it/Stampanti_178158_2-hp" class="subnr">Stampanti HP</a> <span class="subnr">(55)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_178158_2-epson" class="subnr">Stampanti Espson</a> <span class="subnr">(466)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_178158_2-lexmark_international" class="subnr">Stampanti Lexmark</a> <span class="subnr">(301)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_178158_2-canon" class="subnr">Stampanti Canon</a> <span class="subnr">(239)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_178158_2-brother" class="subnr">Stampanti Brother</a> <span class="subnr">(99)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_178158_2-xerox" class="subnr">Stampanti Xerox</a>
					<span class="subnr">(182)</span></dd></dl><dl class="subcatbox"><dt> <a href="http://www.ciao.it/Stampanti_Multifunzione_205569_2" id="Node_Category_196265" class="hdl">Stampanti Multifunzione</a></dt><dd> <a href="http://www.ciao.it/Stampanti_Multifunzione_205569_2-xerox" class="subnr">Stampanti Multifunzione Xerox</a> <span class="subnr">(340)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_Multifunzione_205569_2-canon" class="subnr">Stampanti Multifunzione Canon</a> <span class="subnr">(182)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_Multifunzione_205569_2-brother" class="subnr">Stampanti Multifunzione Brother</a> <span class="subnr">(138)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_Multifunzione_205569_2-lexmark_international" class="subnr">Stampanti Multifunzione Lexmark</a> <span class="subnr">(97)</span></dd><dd> <a href="http://www.ciao.it/Stampanti_Multifunzione_205569_2-epson" class="subnr">Stampanti Multifunzione Epson</a> <span class="subnr">(76)</span></dd><dd>
					<a href="http://www.ciao.it/Stampanti_Multifunzione_205569_2-samsung" class="subnr">Stampanti Multifunzione Samsung</a> <span class="subnr">(59)</span></dd></dl>
					<!-- end of sample code from ciao.it-->

				</div>
				<div class="yui-u">

					<!-- sample code from ciao.it-->

					<dl class="subcatbox"><dt> <a href="http://www.ciao.it/Componenti_Hardware_178057_2" id="Node_Category_168463" class="hdl">Componenti Hardware</a></dt><dd> <a href="http://www.ciao.it/Schede_Madri_205459_3" class="subnr">Schede Madri</a> <span class="subnr">(1877)</span></dd><dd> <a href="http://www.ciao.it/Hard_Disk_178068_3" class="subnr">Hard Disk</a> <span class="subnr">(3568)</span></dd><dd> <a href="http://www.ciao.it/Drive_178061_3" class="subnr">Drive</a> <span class="subnr">(158)</span></dd><dd> <a href="http://www.ciao.it/Floppy_178067_3" class="subnr">Floppy</a> <span class="subnr">(169)</span></dd><dd> <a href="http://www.ciao.it/Alimentatori_178074_3" class="subnr">Alimentatori</a> <span class="subnr">(3207)</span></dd><dd> <a href="http://www.ciao.it/Componenti_Hardware_178057_2" id="Node_Category_More_168463" class="hdl">continua</a>
					</dd></dl><dl class="subcatbox"><dt> <a href="http://www.ciao.it/Monitor_LCD_178159_2" id="Node_Category_168460" class="hdl">Monitor LCD</a></dt><dd> <a href="http://www.ciao.it/Monitor_LCD_178159_2-samsung" class="subnr">Monitor Samsung</a> <span class="subnr">(394)</span></dd><dd> <a href="http://www.ciao.it/Monitor_LCD_178159_2-lg" class="subnr">Monitor LG</a> <span class="subnr">(315)</span></dd><dd> <a href="http://www.ciao.it/Monitor_LCD_178159_2-sony" class="subnr">Monitor Sony</a> <span class="subnr">(181)</span></dd><dd> <a href="http://www.ciao.it/Monitor_LCD_178159_2-philips" class="subnr">Monitor Philips</a> <span class="subnr">(275)</span></dd><dd> <a href="http://www.ciao.it/Monitor_LCD_178159_2-acer" class="subnr">Monitor Acer</a> <span class="subnr">(323)</span></dd><dd> <a href="http://www.ciao.it/Monitor_LCD_178159_2-hp" class="subnr">Monitor HP</a> <span class="subnr">(127)</span></dd></dl><dl class="subcatbox"><dt>
					<a href="http://www.ciao.it/Monitor_CRT_206455_2" id="Node_Category_197225" class="hdl">Monitor CRT</a></dt><dd> <a href="http://www.ciao.it/Monitor_CRT_206455_2-philips" class="subnr">Monitor CRT Philips</a> <span class="subnr">(120)</span></dd><dd> <a href="http://www.ciao.it/Monitor_CRT_206455_2-hp" class="subnr">Monitor CRT HP</a> <span class="subnr">(41)</span></dd><dd> <a href="http://www.ciao.it/Monitor_CRT_206455_2-samsung" class="subnr">Monitor CRT Samsung</a> <span class="subnr">(80)</span></dd><dd> <a href="http://www.ciao.it/Monitor_CRT_206455_2-ibm" class="subnr">Monitor CRT IBM</a> <span class="subnr">(67)</span></dd><dd> <a href="http://www.ciao.it/Monitor_CRT_206455_2-compaq" class="subnr">Monitor CRT Compaq</a> <span class="subnr">(61)</span></dd><dd> <a href="http://www.ciao.it/Monitor_CRT_206455_2-nec" class="subnr">Monitor CRT NEC</a> <span class="subnr">(64)</span></dd></dl><dl class="subcatbox"><dt>
					<a href="http://www.ciao.it/Processori_206294_2" id="Node_Category_197039" class="hdl">Processori</a></dt><dd> <a href="http://www.ciao.it/Processori_206294_2-hewlett_packard" class="subnr">Processori HP</a> <span class="subnr">(379)</span></dd><dd> <a href="http://www.ciao.it/Processori_206294_2-intel" class="subnr">Processori Intel</a> <span class="subnr">(311)</span></dd><dd> <a href="http://www.ciao.it/Processori_206294_2-ibm" class="subnr">Processori IBM</a> <span class="subnr">(141)</span></dd><dd> <a href="http://www.ciao.it/Processori_206294_2-fujitsu_siemens_computers" class="subnr">Processori Fujitsu Siemens</a> <span class="subnr">(43)</span></dd><dd> <a href="http://www.ciao.it/Processori_206294_2-compaq" class="subnr">Processori Compaq</a> <span class="subnr">(14)</span></dd><dd> <a href="http://www.ciao.it/Processori_206294_2-acer" class="subnr">Processori Acer</a> <span class="subnr">(24)</span></dd></dl>
					<!-- end of sample code from ciao.it-->

				</div>

				<div class="yui-u">

					<!-- sample code from ciao.it-->

					<dl class="subcatbox"><dt> <a href="http://www.ciao.it/Componenti_di_Rete_178049_2" id="Node_Category_168462" class="hdl">Componenti di Rete</a></dt><dd> <a href="http://www.ciao.it/Modem_178051_3" class="subnr">Modem</a> <span class="subnr">(1428)</span></dd><dd> <a href="http://www.ciao.it/Schede_di_Rete_178050_3" class="subnr">Schede di Rete</a> <span class="subnr">(3191)</span></dd><dd> <a href="http://www.ciao.it/Router_e_Bridge_178053_3" class="subnr">Router e Bridge</a> <span class="subnr">(1176)</span></dd><dd> <a href="http://www.ciao.it/Hub_e_Switch_178052_3" class="subnr">Hub e Switch</a> <span class="subnr">(2739)</span></dd><dd> <a href="http://www.ciao.it/Dispositivi_di_Rete_205570_3" class="subnr">Dispositivi di Rete</a> <span class="subnr">(1791)</span></dd><dd> <a href="http://www.ciao.it/Componenti_di_Rete_178049_2" id="Node_Category_More_168462" class="hdl">continua</a></dd></dl><dl class="subcatbox"><dt>
					<a href="http://www.ciao.it/Palmari_Smartphone_206480_2" id="Node_Category_197255" class="hdl">Palmari &amp; Smartphone</a></dt><dd> <a href="http://www.ciao.it/Palmari_Smartphone_206480_2-htc" class="subnr">Palmari HTC</a> <span class="subnr">(26)</span></dd><dd> <a href="http://www.ciao.it/Palmari_Smartphone_206480_2-nokia" class="subnr">Palmari Nokia</a> <span class="subnr">(22)</span></dd><dd> <a href="http://www.ciao.it/Palmari_Smartphone_206480_2-hp" class="subnr">Palmari HP</a> <span class="subnr">(85)</span></dd><dd> <a href="http://www.ciao.it/Palmari_Smartphone_206480_2-i_mate" class="subnr">Palmari i-mate</a> <span class="subnr">(19)</span></dd><dd> <a href="http://www.ciao.it/Palmari_Smartphone_206480_2-samsung" class="subnr">Palmari Samsung</a> <span class="subnr">(10)</span></dd><dd> <a href="http://www.ciao.it/Palmari_Smartphone_206480_2-palm" class="subnr">Palmari Palm</a> <span class="subnr">(89)</span></dd></dl>
					<dd> <a href="http://www.ciao.it/Mouse_178035_3" class="subnr">Mouse</a> <span class="subnr">(3589)</span></dd><dd> <a href="http://www.ciao.it/Tastiere_178036_3" class="subnr">Tastiere</a> <span class="subnr">(2826)</span></dd><dd> <a href="http://www.ciao.it/Scanner_178040_3" class="subnr">Scanner</a> <span class="subnr">(879)</span></dd><dd> <a href="http://www.ciao.it/Webcam_206484_3" class="subnr">Webcam</a> <span class="subnr">(578)</span></dd><dd> <a href="http://www.ciao.it/USB_Flash_Drive_205463_3" class="subnr">USB Flash Drive</a> <span class="subnr">(1086)</span></dd><dd> <a href="http://www.ciao.it/Periferiche_178035_2" id="Node_Category_More_168461" class="hdl">continua</a></dd></dl><dl class="subcatbox"><dt> <a href="http://www.ciao.it/Storage_Media_178080_2" id="Node_Category_168465" class="hdl">Storage Media</a></dt><dd>
					<a href="http://www.ciao.it/CD_Registrabili_178080_3" class="subnr">CD Registrabili</a> <span class="subnr">(2340)</span></dd><dd> <a href="http://www.ciao.it/DVD_Registrabili_178081_3" class="subnr">DVD Registrabili</a> <span class="subnr">(2490)</span></dd></dl><dl class="subcatbox"><dt> <a href="http://www.ciao.it/Accessori_178161_2" id="Node_Category_168466" class="hdl">Accessori</a></dt><dd> <a href="http://www.ciao.it/Supporti_Cartacei_per_Stampanti_205643_3" class="subnr">Supporti Cartacei per Stampanti</a> <span class="subnr">(1891)</span></dd><dd> <a href="http://www.ciao.it/Accessori_per_Stampanti_205472_3" class="subnr">Accessori per Stampanti</a> <span class="subnr">(20111)</span></dd><dd> <a href="http://www.ciao.it/Cartucce_per_Stampanti_e_Toner_206282_3" class="subnr">Cartucce per Stampanti e Toner</a></dd><dd> <a href="http://www.ciao.it/Accessori_per_Portatili_197962_3" class="subnr">Accessori per Portatili</a> <span class="subnr">(42)</span></dd><dd>
					<a href="http://www.ciao.it/Accessori_per_Palmari_178161_3" class="subnr">Accessori per Palmari</a> <span class="subnr">(41)</span></dd><dd> <a href="http://www.ciao.it/Accessori_178161_2" id="Node_Category_More_168466" class="hdl">continua</a></dd></dl><dl class="subcatbox"><dt> <a href="http://www.ciao.it/Servizi_e_Consigli_178181_2" id="Node_Category_168467" class="hdl">Servizi e Consigli</a></dt></dl>

					<!-- end of sample code from ciao.it-->

				</div>
			</div>
		</div>
		<div>
        	<div class="subtab_list">
				<ul class="">
					<li><a href="#tab1"><em>Attive ora</em></a></li>
					<li><a href="#tab1"><em>Programmate</em></a></li>
					<li class="selected"><a href="#tab1"><em>Calendario</em></a></li>
				</ul>
				
			</div>
				'.Get::img(Get::rel_path('base').'/calendar.jpg', false, false, false, true).'

		</div>
		', 'content');
		cout('</div>', 'content');
		cout('</div>', 'content');

		cout(''
			.'<script type="text/javascript">'."\n"
			."	var myTabs = new YAHOO.widget.TabView('course_tag'); "."\n"
			.'</script>'."\n"
		, 'scripts');




			/*


			cout('<script type="text/javascript">
		var temp;
		YAHOO.util.Event.onDOMReady(function(e) {
		  var oConfig = {
				dragdrop: false,
				initNodes: '.$nodes.',
				ajax_url: "ajax.adm_server.php?plf=framework&file=category_tree&sf=folder_tree'.'"
			};
		  temp = new FolderTree("tree", oConfig);
		});
	  </script>', 'page_head');

			cout('<div class="area_block">PROVA ALBERO</div>', 'content');
			cout('<div class="std_block">', 'content');
			cout('<div style="border:solid 1px" class="folder_tree">', 'content');
			cout('<div id="tree"></div>', 'content');
			cout('</div>', 'content');
			cout('<br /><br />DEBUG:&nbsp;<button onclick="alert(temp.getCurrentSelection());">SELEZIONE</button>', 'content');

			cout('</div>', 'content');
			require_once(_lms_.'/lib/folder_tree/class.category_tree.php');
			$tree = new CategoryTree('categorytree');

			$tree->initLibraries();
			$tree->useDOMready = true;
			$temp = $tree->get();

			cout($temp['js'], 'page_head');
			cout('<div class="area_block">PROVA ALBERO</div>', 'content');
			cout('<div class="std_block">', 'content');
			cout('<div style="border:solid 1px" class="folder_tree">', 'content');
			cout($temp['html'], 'content');
			cout('</div>', 'content');
			cout('<br /><br />DEBUG:&nbsp;<button onclick="alert(temp.getCurrentSelection());">SELEZIONE</button>', 'content');

			cout('</div>', 'content');
			*/
		} break;

	}

}


?>