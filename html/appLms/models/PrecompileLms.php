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

class PrecompileLms extends Model {

	protected $db;
	protected $error;
	protected $pmodel;

	public function __construct() {
		$this->db = DbConn::getInstance();
		$this->error = false;
		$this->pmodel = new PrivacypolicyAdm();
	}

	public function compileRequired() {

		// get privacy_policy --> con questo non funziona -->setting Get::sett('privacy_policy', 'off')
		$query  = " SELECT param_value FROM %adm_setting"
		. " WHERE param_name = 'privacy_policy'"
		. " ORDER BY pack, sequence";
		$privacy_policy = $this->db->fetch_row($this->db->query($query))[0];

		if(Get::sett('request_mandatory_fields_compilation', 'off') == 'off' && $privacy_policy == 'off'){
			return false;
		}

		$id_user = Docebo::user()->getIdSt();
		$policy_checked = $this->getAcceptingPolicy($id_user);

		if($privacy_policy == 'off'){
			$policy_checked = true;
		}
		
		$fields_checked = true;
		if(Get::sett('request_mandatory_fields_compilation', 'on') == 'on') {
			require_once(_adm_.'/lib/lib.field.php');
			$fieldlist = new FieldList();

			$fields_checked = $fieldlist->checkUserMandatoryFields($id_user);
		}
		
		return (!$policy_checked || !$fields_checked);
	}

	/**
	 * Retrieve the privacy policy text for the current user, given a specific language code
	 * @param string $language the language code to use, current language by default
	 * @return string 
	 */
	public function getPrivacyPolicyText($language = FALSE) {
		//initialize output
		$output = FALSE;
		if (!$language) $language = Lang::get();
		$id_user = Docebo::user()->getIdSt();

		//retrieve the translation from DB
		$pmodel = new PrivacypolicyAdm();
		$policies = $pmodel->getUserPolicy($id_user);
		if (!empty($policies)) {
			$output = "";
			$id_policy = $policies[0]; //the user may have more than one policy, get the first one
			$pinfo = $pmodel->getPolicyInfo($id_policy);
			if (isset($pinfo->translations[Lang::get()]))
				$output = $pinfo->translations[Lang::get()];
		} else {
			// default policy text
			$pinfo = $pmodel->getDefaultPolicyInfo();
			if (isset($pinfo->translations[Lang::get()])){
				$output = $pinfo->translations[Lang::get()];
			} else {
				$output = Lang::t('_REG_PRIVACY_POLICY', 'login');
			}
				
		}

		return $output;
	}

	/**
	 * Retrieve the privacy policy text for the current user, given a specific language code
	 * @param string $language the language code to use, current language by default
	 * @return integer 
	 */
	public function getPrivacyPolicyId() {
		//initialize output
		$output = -1;
		$id_user = Docebo::user()->getIdSt();

		$pmodel = new PrivacypolicyAdm();
		$policies = $pmodel->getUserPolicy($id_user);
		if (!empty($policies)) {
			$output = $policies[0]; //the user may have more than one policy, get the first one
		} else {
			$pinfo = $pmodel->getDefaultPolicyInfo();
			$output = $pinfo->id_policy;
		}

		return $output;
	}

	/**
	 * Set if the user has accepted the privacy policy or not
	 * @param integer $id_user the idst of the user who is accepting/refusing privacy policy
	 * @param boolean $accepted true if the policy has been accepted by the user, false otherwise
	 * @return boolean
	 */
	public function setAcceptingPolicy($id_user, $id_policy, $accepted = TRUE) {
		//check input values
		if ((int)$id_user <= 0) return FALSE;

		//set value in DB - old method
		//$query = "UPDATE %adm_user SET privacy_policy = ".($accepted ? "1" : "0")
		//	." WHERE idst = ".(int)$id_user;
		//$res = $this->db->query($query);

		if ($accepted) {
			// set value in DB - new method
			$query = "INSERT INTO %adm_privacypolicy_user (id_policy, idst, accept_date) VALUES (".(int)$id_policy.", ".(int)$id_user.", '".date("Y-m-d H:i:s")."' ) ";
			$res = $this->db->query($query);
		}
		return $res ? TRUE : FALSE;
	}


	/**
	 * Check if the user has accepted the privacy policy or not yet
	 * @param integer $id_user the idst of the user to check
	 * @return boolean
	 */
	public function getAcceptingPolicy($id_user) {
		//check input values
		if ((int)$id_user <= 0) {
			return FALSE;
		}

		//retrieve id_policy from DB
		$pmodel = new PrivacypolicyAdm();
		$policies = $pmodel->getUserPolicy($id_user);
		if (!empty($policies)) {
			$id_policy = $policies[0]; //the user may have more than one policy, get the first one
		} else {
			$pinfo = $pmodel->getDefaultPolicyInfo();
			$id_policy = $pinfo->id_policy;
		}


		//read value in DB
		$output = FALSE;
		//$query = "SELECT privacy_policy FROM %adm_user WHERE idst = ".(int)$id_user; // Old Method
		$query = "SELECT ppu.id_policy, ppu.accept_date, pp.validity_date
				FROM core_privacypolicy_user AS ppu, core_privacypolicy AS pp
				WHERE ppu.id_policy = pp.id_policy
				AND ppu.accept_date > pp.validity_date
				AND ppu.idst = ".(int)$id_user."
				AND ppu.id_policy= ".(int)$id_policy;

		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res) > 0) {
			list($value) = $this->db->fetch_row($res);
			if ($value > 0) $output = TRUE;
		}

		return $output;
	}
    
     public function getHomeUrl(){
        $array_tab['tb_classroom']  = 'classroom/show';
        $array_tab['tb_communication']  = 'communication/show';
        $array_tab['tb_coursepath']  = 'coursepath/show';
        $array_tab['tb_elearning']  = 'elearning/show';
        $array_tab['tb_games']  = 'games/show';
        $array_tab['tb_home']  = 'home/show';
        $array_tab['tb_kb']  = 'kb/show';
        $array_tab['tb_label']  = 'label/show';
        $array_tab['tb_videoconference']  = 'videoconference/show';
        $query = " SELECT obj_index from %lms_middlearea where is_home=1";
        list($tb_home) = sql_fetch_row(sql_query($query));
       if (Get::sett('home_page_option') == 'catalogue') {
           $url = 'lms/catalog/show';
       } else{
           if (Get::sett('on_usercourse_empty')=='off'){
                $url = $array_tab[$tb_home]; 
           } else {
                $a= Docebo::user()->getIdSt();
                $q = 'Select count(\'x\') from learning_courseuser where idUser ='.$a;
                list($n) = sql_fetch_row(sql_query($q));
                if ($n == 0) { //showing catalogue if no enrollment
                    $url = 'lms/catalog/show'; 
                }  else { 
                    $url =  $array_tab[$tb_home];
                }
           }
          
       }
       return $url; 
    }
    
    public function getForceChangeUser(){
        $a= Docebo::user()->getIdSt();
                $q = 'Select force_change from core_user where idst='.$a;
                list($n) = sql_fetch_row(sql_query($q));
                return $n;
        
    }    
    

}