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

class DummyLmsController extends LmsController {

	public $name = 'testdata';

	public function show() {
		$this->render('show', array(
			'test' => '...'
		));
	}

	public function testdata() {
		require_once(_base_.'/lib/lib.json.php');
		$json = new Services_JSON();

		$nodes = array();

		for ($i=0; $i<10; $i++) {
			$temp = array(
				'id' => $i+10,
				'label' => 'node_'.$i,
				'is_leaf' => ($i == 3 ? false : true),
				'count_content' => ($i == 3 ? 4 : 0),
				'options' => false
			);

			if ($i == 3) {
				$children = array();
				for ($j=0; $j<4; $j++) {
					$children[] = array(
						'id' => $j+50,
						'label' => 'sub_node_'.$j,
						'is_leaf' => true,
						'count_content' => 0,
						'options' => false
					);
				}
				$temp['children'] = $children;
			}

			$nodes[] = $temp;
		}

		$data = array('success' => true, 'nodes' => $nodes);

		$this->render('testdata', array(
			'data' => $json->encode($data)
		));
	}
	
}
