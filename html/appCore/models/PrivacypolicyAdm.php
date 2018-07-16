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


class PrivacypolicyAdm extends Model {

	protected $db;
	protected $acl_man;

	public function  __construct() {
		$this->db = DbConn::getInstance();
		$this->acl_man = Docebo::user()->getACLManager();
	}

	public function getPerm() {
		return array(
			'view' => 'standard/view.png',
			//'add'	=> 'standard/add.png',
			'mod'  => 'standard/edit.png',
			'del'  => 'standard/delete.png'
		);
	}
	

	public function getPoliciesList($pagination = array(), $filter = false) {

		if (!is_array($pagination)) $pagination = array();

		$startIndex = (isset($pagination['startIndex']) ? $pagination['startIndex'] : 0);
		$results = (isset($pagination['results']) ? $pagination['results'] : Get::sett('visuItem', 25));

		$sort = 'name';
		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				default: $sort = 'name';
			}
		}

		$dir = 'ASC';
		if (isset($pagination['dir'])) {
			switch ($pagination['dir']) {
				case 'yui-dt-asc': $dir = 'ASC'; break;
				case 'yui-dt-desc': $dir = 'DESC'; break;
				case 'asc': $dir = 'ASC'; break;
				case 'desc': $dir = 'DESC'; break;
				default: $dir = 'ASC';
			}
		}
		
		$query = "SELECT id_policy, name "
			." FROM %adm_privacypolicy ".$filter;
		if ($filter) {
			$query .= " WHERE name LIKE '%".$filter."%' ";
		}

		$query .= " ORDER BY ".$sort." ".$dir." ";

		$startIndex = (isset($conditions['startIndex']) ? $conditions['startIndex'] : 0);
		$results = (isset($conditions['results']) ? $conditions['results'] : Get::sett('visuItem'));
		$query .= "LIMIT ".(int)$startIndex.", ".(int)$results;

		$res = $this->db->query($query);

		if ($res) {
			$output = array();
			$glist = array();
			while ($obj = $this->db->fetch_obj($res)) {
				$obj->is_assigned = FALSE;  //questa
				$output[$obj->id_policy] = $obj;
			}

			//check assignments ...
			if (!empty($output)) {
				$query = "SELECT associated_policy, COUNT(*) FROM %adm_org_chart_tree "
					." WHERE associated_policy IN (".implode(",", array_keys($output)).") "
					." GROUP BY associated_policy";
				$res = $this->db->query($query);
				while (list($id_policy, $count) = $this->db->fetch_row($res)) {
					if ($count > 0 && isset($output[$id_policy])) {
						$output[$id_policy]->is_assigned = TRUE;
					}
				}
			}
		} else {
			return FALSE;
		}
		return array_values($output);

	}


	public function getPoliciesTotal($filter = false) {
		$query = "SELECT COUNT(*) "
			." FROM %adm_privacypolicy ";
		if ($filter) {
			$query .= " WHERE name LIKE '%".$filter."%' ";
		}

		$res = $this->db->query($query);
		list($count) = $this->db->fetch_row($res);

		return $count;
	}



	public function deletePolicy($id_policy) {
		$output = false;

		$query = "DELETE FROM %adm_privacypolicy WHERE id_policy = ".(int)$id_policy." LIMIT 1";
		$res = $this->db->query($query);

		if ($res) {
			$query_lang = "DELETE FROM %adm_privacypolicy_lang WHERE id_policy=".(int)$id_policy;
			$res_lang = $this->db->query($query);

			$output = true;
			//delete associations ...
		}

		return $output;
	}


	public function createPolicy($name, $translations) {
		//validate params
		if (!$name || !is_array($translations) || empty($translations)) {
			return FALSE;
		}

		//initialize output and variables
		$output = false;
		$lang_codes = Docebo::langManager()->getAllLangCode();

		$query = "INSERT INTO %adm_privacypolicy (name) VALUES ('".$name."')";
		$res = $this->db->query($query);
		if ($res) {
			$id_policy = $this->db->insert_id();
			if ($id_policy) {
				$query_translations = array();
				foreach ($lang_codes as $lang_code) {
					if (isset($translations[$lang_code])) {
						$query_translations[] = "(".$id_policy.", '".$lang_code."', '".$translations[$lang_code]."')";
					} else {
						$query_translations[] = "(".$id_policy.", '".$lang_code."', '')";
					}
				}
				if (!empty($translations)) {
					$query = "INSERT INTO %adm_privacypolicy_lang (id_policy, lang_code, translation) VALUES ";
					$query .= implode(",", $query_translations);
					$res = $this->db->query($query);
				}
			}
			
			$output = $id_policy;
		}

		return $output;
	}


	    public function updatePolicy($id_policy, $name, $is_default, $reset_policy, $translations) {
        //validate params
        if ((int)$id_policy <= 0 || !$name || !is_array($translations) || empty($translations)) {
            return FALSE;
        }

        //initialize output and variables
        $output = false;
        $lang_codes = Docebo::langManager()->getAllLangCode();

        $query = "UPDATE %adm_privacypolicy SET name = '".$name."', lastedit_date = '".date("Y-m-d H:i:s")."' WHERE id_policy = ".(int)$id_policy;
        $res = $this->db->query($query);

        if ($reset_policy == 1){
            $query = "UPDATE %adm_privacypolicy SET validity_date = '".date("Y-m-d H:i:s")."' WHERE id_policy = ".(int)$id_policy;
            $res = $this->db->query($query);
        }

        if ($is_default == 1){
            $query = "UPDATE %adm_privacypolicy SET is_default = 0";
            $res = $this->db->query($query);
            $query = "UPDATE %adm_privacypolicy SET is_default = 1 WHERE id_policy = ".(int)$id_policy;
            $res = $this->db->query($query);
        }

        
        if ($res) {
            //remove old translations and insert new ones
            $query = "DELETE FROM %adm_privacypolicy_lang WHERE id_policy = ".(int)$id_policy;
            $res = $this->db->query($query);
            if ($res) {
                $query_translations = array();
                foreach ($lang_codes as $lang_code) {
                    if (isset($translations[$lang_code])) {
                        $query_translations[] = "(".$id_policy.", '".$lang_code."', '".$translations[$lang_code]."')";
                    } else {
                        $query_translations[] = "(".$id_policy.", '".$lang_code."', '')";
                    }
                }
                if (!empty($translations)) {
                    $query = "INSERT INTO %adm_privacypolicy_lang (id_policy, lang_code, translation) VALUES ";
                    $query .= implode(",", $query_translations);
                    $res = $this->db->query($query);
                }
            }

            $output = TRUE;
        }

        return $output;
    }


	public function getPolicyName($id_policy) {
		$output = false;
		$query = "SELECT name FROM %adm_privacypolicy WHERE id_policy=".(int)$id_policy;
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res)>0) {
			list($output) = $this->db->fetch_row($res);
		}
		return $output;
	}


	public function getPolicyTranslations($id_policy) {
		$output = false;
		$query = "SELECT * FROM %adm_privacypolicy_lang WHERE id_policy=".(int)$id_policy;
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res)>0) {
			//initialize output
			$lang_codes = Docebo::langManager()->getAllLangCode();
			$output = array();
			foreach ($lang_codes as $lang_code) {
				$output[$lang_code] = "";
			}
			//read values from DB and prepare output
			while ($obj = $this->db->fetch_obj($res)) {
				if (isset($output[$obj->lang_code])) {
					$output[$obj->lang_code] = $obj->translation;
				}
			}
		}
		return $output;
	}


	public function getPolicyInfo($id_policy) {
		$output = new stdClass();
        $output->id_policy = $id_policy;
		$output->name = $this->getPolicyName($id_policy);           
        $output->is_default = $this->getPolicyIsDefault($id_policy);
		$output->translations = $this->getPolicyTranslations($id_policy);
		return $output;
	}



    
    public function getPolicyIsDefault($id_policy) {
        $output = false;
        $query = "SELECT is_default FROM %adm_privacypolicy WHERE id_policy=".(int)$id_policy;
        $res = $this->db->query($query);
        if ($res && $this->db->num_rows($res)>0) {
            list($output) = $this->db->fetch_row($res);
        }
        return $output;
    }
    
    

	public function getSelectedOrgchart($id_policy) {
		$output = FALSE;
		if ($id_policy > 0) {
			$query = "SELECT * FROM %adm_org_chart_tree WHERE associated_policy = ".(int)$id_policy;
			$res = $this->db->query($query);
			if ($res) {
				$output = array();
				while ($obj = $this->db->fetch_obj($res)) {
					$output []= (int)$obj->idOrg;
				}
			}
		}
		return $output;
	}


	public function getAlreadyAssignedOrgchart() {
		$output = FALSE;
		$query = "SELECT * FROM %adm_org_chart_tree "
			." WHERE associated_policy IS NOT NULL "
			." OR associated_policy > 0";
		$res = $this->db->query($query);
		if ($res) {
			$output = array();
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = (int)$obj->idOrg;
			}
		}
		return $output;
	}

	public function getUnssignedOrgchart() {
		$output = FALSE;
		$query = "SELECT * FROM %adm_org_chart_tree "
			." WHERE associated_policy IS NULL "
			." OR associated_policy <= 0";
		$res = $this->db->query($query);
		if ($res) {
			$output = array();
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = (int)$obj->idOrg;
			}
		}
		return $output;
	}
	
	
	public function resetOrgchartAssignment($id_policy) {
		if ($id_policy < 0) return true;
		
		$query = "UPDATE %adm_org_chart_tree SET associated_policy = NULL "
			." WHERE associated_policy = ".(int)$id_policy;
		$res = $this->db->query($query);
		
		return $res;
	}


	public function setOrgchartAssignment($id_policy, $folders) {
		if (!is_array($folders)) return FALSE;
		if ($id_policy < 0) return TRUE;

		$res =$this->resetOrgchartAssignment($id_policy);

		if (!empty($folders)) {
			$query = "UPDATE %adm_org_chart_tree SET associated_policy = ".(int)$id_policy." "
				." WHERE idOrg IN (".implode(",", $folders).")";
			$res = $this->db->query($query);
		}

		return $res ? TRUE : FALSE;
	}


	public function getUserPolicy($id_user) {
		$output = array();
		$query = "SELECT oct.idst_oc, oct.idst_ocd, oct.iLeft, oct.iRight, "
			." oct.associated_policy, oct.lev, oct.idParent "
			." FROM %adm_org_chart_tree AS oct "
			." JOIN %adm_group_members AS gm JOIN %adm_user AS u "
			." ON (oct.idst_oc = gm.idst  AND gm.idstMember = u.idst "
			." AND gm.idstMember = ".(int)$id_user.")";
            
		$res = $this->db->query($query);
		if (!$res) return FALSE;
		$folders = array();
		if ($this->db->num_rows($res) > 0) {
			while ($obj = $this->db->fetch_obj($res)) {
				if ((int)$obj->associated_policy > 0) {
					$output[] = $obj->associated_policy;
				}
				$folders[] = $obj;
			}
			if (empty($output)) {
				//search parent folders for policies
				foreach ($folders as $folder) {
					$query = "SELECT associated_policy FROM %adm_org_chart_tree "
						." WHERE iLeft < ".(int)$folder->iLeft." AND iRight > ".(int)$folder->iRight." "
						." AND associated_policy > 0 ORDER BY iLeft DESC LIMIT 1";
					$res = $this->db->query($query);
					if ($res && $this->db->num_rows($res) > 0) {
						list($id_policy) = $this->db->fetch_row($res);
						$output[] = (int)$id_policy;
					}
				}
			}
		}
		$output = array_unique($output);
		return $output;
	}

    
public function getDefaultPolicyInfo() {

        $query = "SELECT id_policy FROM %adm_privacypolicy "
        ." WHERE is_default = 1";       
        $res = $this->db->query($query);
        list($id_policy) = $this->db->fetch_row($res);
        
        $output = new stdClass();
        $output->id_policy = $id_policy;
        $output->name = $this->getPolicyName($id_policy);
        $output->translations = $this->getPolicyTranslations($id_policy);
        return $output;
    }
    

}

?>