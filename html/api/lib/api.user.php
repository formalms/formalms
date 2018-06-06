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

require_once(_base_.'/api/lib/lib.api.php');

class User_API extends API {

	protected function _getBranch($like, $parent = false, $lang_code = false) {
		if (!$like) return false;
		$query = "SELECT oct.idOrg FROM %adm_org_chart as oc JOIN %adm_org_chart_tree as oct "
			." ON (oct.idOrg = oc.id_dir) WHERE oc.translation LIKE '".addslashes($like)."'";
		if ($lang_code !== false) { //TO DO: check if lang_code is valid
			$query .= " AND oc.lang_code = '".$lang_code."'";
		}
		if ($parent !== false) {
			$query .= " AND oct.idParent = ".(int)$parent;
		}
		$res = $this->db->query($query);
		if ($this->db->num_rows($res) > 0) {
			list($output) = $this->db->fetch_row($res);
			return $output;
		} else
			return false;
	}

	public function checkUserIdst($idst) {
		$output = false;
		if (is_numeric($idst)) {
			$res = $this->db->query("SELECT * FROM %adm_user WHERE idst=".$idst);
			$output = ($this->db->num_rows($res) > 0);
		}
		return $output;
	}

	public function createUser($params, $userdata) {
		
		if (defined("_API_DEBUG") && _API_DEBUG) { file_put_contents('create_user.txt', "\n\n----------------\n\n".print_r($params, true)." || ".print_r($userdata, true), FILE_APPEND); }

		$set_idst =(isset($params['idst']) ? $params['idst'] : false);
		
		//fix grifomultimedia soap	argument [auth a.b.]
		$userdata = (!isset($userdata['userid'])&&isset($params)) ?$params:$userdata;
		
		if (!isset($userdata['userid'])) return false;
                
                if (!isset($userdata['sendmail']) || $userdata['sendmail'] == "") {
                        $sendMailToUser = false;
                } else {
                        $sendMailToUser = true;
                }
                
                if (!isset($userdata['password']) || $userdata['password'] == "") {
			$userdata['password'] = mt_rand();
                        $sendMailToUser = true;
		}
                
		$id_user = $this->aclManager->registerUser(
			$userdata['userid'],
			(isset($userdata['firstname']) ? $userdata['firstname'] : '' ),
			(isset($userdata['lastname']) ? $userdata['lastname'] : ''),
			(isset($userdata['password']) ? $userdata['password'] : ''),
			(isset($userdata['email']) ? $userdata['email'] : ''),
			'',
			(isset($userdata['signature']) ? $userdata['signature'] : ''),
			false, // alredy_encripted
			$set_idst,
			(isset($userdata['pwd_expire_at']) ? $userdata['pwd_expire_at'] : ''),
			(isset($userdata['force_change']) ? $userdata['force_change'] : 0)
		);

		$event = new \appLms\Events\Api\ApiUserRegistrationEvent();
		$event->setId($id_user);
		\appCore\Events\DispatcherManager::dispatch(\appLms\Events\Api\ApiUserRegistrationEvent::EVENT_NAME, $event);


		// suspend
                if (isset($userdata['valid']) && $userdata['valid'] == '0'){
			$res = $this->aclManager->suspendUser($id_user);
		}
                
		// registration code:
		if ($id_user && !empty($userdata['reg_code']) && !empty($userdata['reg_code_type'])) {
			require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));
			$user_manager = new UserManager();
			$uma = new UsermanagementAdm();
			$reg_code_res =$user_manager->_render->processRegistrationCode(
				$this->aclManager, $uma, $id_user, $userdata['reg_code'], $userdata['reg_code_type']
			);
			if ($reg_code_res['success'] == false) {
				$this->aclManager->deleteUser($id_user);
				$output = array('success'=>false, 'message'=>'Registration Code Error: '.$reg_code_res['msg']);
				$id_user =false;
				return $output;
			}
		}		

		if(!$id_user)  {
			if (defined("_API_DEBUG") && _API_DEBUG) { file_put_contents('create_user.txt', "?!: ".var_export($id_user, true), FILE_APPEND); }
			return false;			
		}

		if ($id_user) {
			if (!isset($userdata['role'])) {
				$level = ADMIN_GROUP_USER;
			} else {
				switch ($userdata['role']) {
					case 'godadmin': 
                                            $level = ADMIN_GROUP_GODADMIN;
                                            break;
					case 'admin': 
                                            $level = ADMIN_GROUP_ADMIN;
                                            break;
					default:
                                            $level = ADMIN_GROUP_USER;
                                            break;
				}
			}

			//subscribe to std groups
			$group = $this->aclManager->getGroupST($level);//'/framework/level/user');
			$this->aclManager->addToGroup($group, $id_user);
			$group = $this->aclManager->getGroupST('/oc_0');
			$this->aclManager->addToGroup($group, $id_user);
			$group = $this->aclManager->getGroupST('/ocd_0');
			$this->aclManager->addToGroup($group, $id_user);

			if (isset($userdata['language'])) {
				require_once(_base_.'/lib/lib.preference.php');
				$user_pref =new UserPreferences($id_user);
				$user_pref->setLanguage($userdata['language']);
			}
						

			//check if some additional fields have been set
			$okcustom = true;
			if(isset($userdata['_customfields'])) {
				require_once(_adm_.'/lib/lib.field.php');
				$fields =& $userdata['_customfields'];
				if (count($fields)>0) {
					$fl = new FieldList();
					$okcustom = $fl->storeDirectFieldsForUser($id_user, $fields);
				}
			}			
			

     	$entities = array();
			if (isset($userdata['orgchart'])) {

				$branches = explode(";", $userdata['orgchart']);
				if (is_array($branches)) {
					foreach ($branches as $branch) {
						$idOrg = $this->_getBranch($branch);

						if ($idOrg !== false) {
							$oc = $this->aclManager->getGroupST('/oc_'.$idOrg);
							$ocd = $this->aclManager->getGroupST('/ocd_'.$idOrg);
							$this->aclManager->addToGroup($oc, $id_user);
							$this->aclManager->addToGroup($ocd, $id_user);
							$entities[$oc] = $oc;
							$entities[$ocd] = $ocd;
						}
					}
				}
			}
			
			$enrollrules = new EnrollrulesAlms();
			$enrollrules->newRules('_NEW_USER',
				array($id_user),
				$userdata['language'],
				0, // idOrg
				(!empty($entities) ? $entities : false)
			);			


			// save external user data:
			if ($params['ext_not_found'] && !empty($params['ext_user']) && !empty($params['ext_user_type'])) {
				$pref_path ='ext.user.'.$params['ext_user_type'];
				$pref_val ='ext_user_'.$params['ext_user_type']."_".(int)$params['ext_user'];
				
				$pref =new UserPreferencesDb();
				$pref->assignUserValue($id_user, $pref_path, $pref_val);
				if (defined("_API_DEBUG") && _API_DEBUG) { file_put_contents('create_user.txt', print_r($id_user, true)." || ".print_r($pref_path, true)." || ".print_r($pref_val, true), FILE_APPEND); }
			}
			else {
				if (defined("_API_DEBUG") && _API_DEBUG) { file_put_contents('create_user.txt', "??: \n\n".print_r($params, true), FILE_APPEND); }
			}

		}

                if ($sendMailToUser) {
                    // Send Message
                    require_once(_base_.'/lib/lib.eventmanager.php');

                    $array_subst = array(
                            '[url]' => Get::sett('url'),
                            '[userid]' => $userdata['userid'],
                            '[password]' => $userdata['password']
                    );

                    $e_msg = new EventMessageComposer();
                    $e_msg->setSubjectLangText('email', '_REGISTERED_USER_SBJ', false);
                    $e_msg->setBodyLangText('email', '_REGISTERED_USER_TEXT', $array_subst );

                    $recipients = array($id_user);

                    if(!empty($recipients)) {
                                    createNewAlert( 'UserNewApi', 'directory', 'edit', '1', 'New user created API', $recipients, $e_msg  );
                    }
                }
                
		return $id_user;
	}

	public function updateUser($id_user, $userdata) {
		
		$acl_man = new DoceboACLManager();
		$output = array();
		
		$user_data = $this->aclManager->getUser($id_user, false);

		if (!$user_data) {
			return -1;
			
		}
		
		if (isset($userdata['valid']) && $userdata['valid'] == '1'){
			$res = $this->aclManager->recoverUser($id_user);
		} elseif (isset($userdata['valid']) && $userdata['valid'] == '0'){
			$res = $this->aclManager->suspendUser($id_user);
		}
		
		$res = $this->aclManager->updateUser(
			$id_user,
			(isset($userdata['userid']) ? $userdata['userid'] :  false),
			(isset($userdata['firstname']) ? $userdata['firstname'] :  false),
			(isset($userdata['lastname']) ? $userdata['lastname'] :  false),
			(isset($userdata['password']) ? $userdata['password'] :  false),
			(isset($userdata['email']) ? $userdata['email'] :  false),
			false,
			(isset($userdata['signature']) ? $userdata['signature'] :  false),
			(isset($userdata['lastenter']) ? $userdata['lastenter'] :  false),
			false
		);

		//additional fields
		$okcustom = true;
		if (isset($userdata['_customfields']) && $res) {
			require_once(_adm_.'/lib/lib.field.php');
			$fields =& $userdata['_customfields'];
			if(count($fields) > 0) {
				$fl = new FieldList();
				$okcustom = $fl->storeDirectFieldsForUser($id_user, $fields);
			}
		}
		return $id_user;
	}

	public function getCustomFields($lang_code=false, $indexes=false) {

		require_once(_adm_.'/lib/lib.field.php');
		$output = array();
		$fl = new FieldList();
		$fields = $fl->getFlatAllFields(false, false, $lang_code);
		foreach ($fields as $key=>$val) {

			if ($indexes)
				$output[$key] = $val;
			else
				$output[]=array('id'=>$key, 'name'=>$val);
		}
		return $output;
	}

	/**
	 * Return all the info about the user
	 * @param <int> $id_user the idst of the user
	 */
	private function getUserDetails($id_user) {
		require_once(_adm_.'/lib/lib.field.php');

		$user_data = $this->aclManager->getUser($id_user, false);
		$output = array();
		if (!$user_data) {
			$output['success'] = false;
			$output['message'] = 'Invalid user ID: '.$id_user.'.';
			$output['details'] = false;
		} else {
			$user_details = array(
				'idst' => $user_data[ACL_INFO_IDST],
				'userid' => $this->aclManager->relativeId($user_data[ACL_INFO_USERID]),
				'firstname' => $user_data[ACL_INFO_FIRSTNAME],
				'lastname' => $user_data[ACL_INFO_LASTNAME],
				//'password' => $user_data[ACL_INFO_PASS],
				'email' => $user_data[ACL_INFO_EMAIL],
				//'avatar' => $user_data[ACL_INFO_AVATAR],
				'signature' => $user_data[ACL_INFO_SIGNATURE],
				'valid' => $user_data[ACL_INFO_VALID],
				'pwd_expire_at' => $user_data[ACL_INFO_PWD_EXPIRE_AT],
				'register_date' => $user_data[ACL_INFO_REGISTER_DATE],
				'last_enter' => $user_data[ACL_INFO_LASTENTER]
			);

			$field_man = new FieldList();
			$field_data = $field_man->getFieldsAndValueFromUser($id_user, false, true);

			$fields = array();
			foreach($field_data as $field_id => $value) {
				$fields[] = array('id'=>$field_id, 'name'=>$value[0], 'value'=>$value[1]);
			}

			$user_details['custom_fields'] = $fields;

			$output['success'] = true;
			$output['message'] = '';
			$output['details'] = $user_details;
		}
		return $output;
	}
	
	
	/**
	 * @param type $params
	 *  - userid
	 *  - password
	 *  - password encoded: if true, it will consider the password as MD5 encoded string; else as plain text
	 * @return array 
	 */
	private function getUserDetailsFromCredentials($params) {
		$output =array();
		
		if (empty($params['userid']) || empty($params['password'])) {
			$output['success']=false;
			$output['message']='Invalid parameters.';
			$output['details']=$params;
		}
		else {
				
			$qtxt ="SELECT idst FROM %adm_user WHERE
					userid='/".$params['userid']."' AND 
					pass='".(!empty($params['password_encoded']) ? $params['password'] : md5($params['password']))."'";

			$q =$this->db->query($qtxt);

			if ($q && $this->db->num_rows($q) > 0) {				
				$row =$this->db->fetch_assoc($q);
				$output =$this->getUserDetails($row['idst']);
			}
			else {
				$output['success'] = false;
				$output['message'] = 'Invalid credentials specified for user: '.$params['userid'].'.';
				$output['details'] = false;
			}
		}
		
		return $output;
	}

	/**
	 * Return the complete user list
	 */
	private function getUsersList() {
		$output = array();
		$query = "SELECT idst, userid, firstname, lastname FROM %adm_user WHERE idst<>".$this->aclManager->getAnonymousId()." ORDER BY userid";
		$res = $this->db->query($query);
		if ($res) {
			$output['success'] = true;
			$output['users_list'] = array();
			while($row = $this->db->fetch_assoc($res)) {
				$output['users_list'][]=array(
					'userid' => $this->aclManager->relativeId($row['userid']),
					'idst' => $row['idst'],
					'firstname' => $row['firstname'],
					'lastname' => $row['lastname']
				);
			}
		} else {
			$output['success'] = false;
		}
		return $output;
	}

	/**
	 * Delete a user
	 * @param <type> $id_user delete the user
	 */
	private function deleteUser($id_user) {
		$output = array();
		if ($this->aclManager->deleteUser($id_user)) {
			$output = array('success'=>true, 'message'=>'User #'.$id_user.' has been deleted.');
		} else {
			$output = array('success'=>false, 'message'=>'Error: unable to delete user #'.$id_user.'.');
		}
		return $output;
	}


	public function getMyCourses($id_user, $params=false) {
		require_once(_lms_.'/lib/lib.course.php');
		$output =array();		

		$output['success']=true;


		$search =array('cu.iduser = :id_user');
		$search_params =array(':id_user' => $id_user);

		
		if (!empty($params['filter'])) {
			switch ($params['filter']) {
				case 'completed': {
					$search[]='cu.status = :status';
					$search_params[':status']=_CUS_END;
				} break;
				case 'notcompleted': {
					$search[]='cu.status >= :status_from';
					$search_params[':status_from']=_CUS_SUBSCRIBED;
					$search[]='cu.status < :status_to';
					$search_params[':status_to']=_CUS_END;
				} break;
				case 'notstarted': {
					$search[]='cu.status = :status';
					$search_params[':status']=_CUS_SUBSCRIBED;
				} break;
			}
		}


		$model = new CourseLms();
		$course_list = $model->findAll($search, $search_params);

		//check courses accessibility
		$keys = array_keys($course_list);
		for ($i=0; $i<count($keys); $i++) {
			$course_list[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($course_list[$keys[$i]]);
		}

		//$output['log']=var_export($course_list, true);

		foreach($course_list as $key=>$course_info) {
			$output[]['course_info']=array(
				'course_id'=>$course_info['idCourse'],
				'course_name'=>str_replace('&', '&amp;', $course_info['name']),
				'course_description'=>str_replace('&', '&amp;', $course_info['description']),
				'course_link'=>Get::sett('url')._folder_lms_.'/index.php?modname=course&amp;op=aula&amp;idCourse='.$course_info['idCourse'],
				'user_status'=>$course_info['user_status'],
			);
		}

		return $output;
	}


	public function KbSearch($id_user, $params) {
		require_once(_lms_.'/lib/lib.course.php');
		$output =array();

		$output['success']=true;

		$filter_text = (!empty($params['search']) ? $params['search'] : "");
		$course_filter = (!empty($params['course_filter']) ? (int)$params['course_filter'] : -1);
		$start_index = (!empty($params['start_index']) ? (int)$params['start_index'] : false);
		$results = (!empty($params['results']) ? (int)$params['results'] : false);

		//TODO: call getSearchFilter()

		$kb_model = new KbAlms();
		$sf =$kb_model->getSearchFilter($id_user, $filter_text, $course_filter);

		$res_arr = $kb_model->getResources(0, $start_index, $results, false,
			false, $sf['where'], $sf['search'], false, true, $sf['show_what']);


		foreach($res_arr["data"] as $key=>$content_info) {
			$output[]['content_info']=$content_info;
		}


		return $output;
	}


	public function importExternalUsers($userdata, $from_email=false) {
		$output =array('success'=>true);

		$i =0;
		foreach($userdata as $user_info) {
			$pref_path ='ext.user.'.$user_info['ext_user_type'];
			$pref_val ='ext_user_'.$user_info['ext_user_type']."_".(int)$user_info['ext_user'];

			$users =$this->aclManager->getUsersBySetting($pref_path, $pref_val);

			// if the user is not yet in sync..
			if (count($users) <= 0) {
				if (!$from_email) {
					// we search for the user from the userid:
					$user =$this->aclManager->getUser(false, $user_info['userid']);
				}
				else { // we search for the user from e-mail:
					$user =$this->aclManager->getUserByEmail($user_info['email']);
				}
				// if found, we link the account to the external one:
				if ($user) {
					$pref =new UserPreferencesDb();
					$pref->assignUserValue($user[ACL_INFO_IDST], $pref_path, $pref_val);
					$output['sync_'.$i]=$user_info['userid'];
					$i++;
				}
			}

		}

		return $output;
	}


	public function importExternalUsersFromEmail($userdata) {
		return $this->importExternalUsers($userdata, true);
	}
	
	
	/**
	 * Count the users of this Forma installation
	 * @param array $params parameters:
	 *  - status: filter by "active", "suspended", "all"
	 * @return array 
	 */
	public function countUsers($params) {
		$output =array('success'=>true);
		
		$status =(!empty($params['status']) ? $params['status'] : 'all');
		
		$qtxt ="SELECT COUNT(*) AS tot FROM %adm_user WHERE
			userid != '/Anonymous' ";
		
		switch ($status) {
			case 'active': {
				$qtxt.="AND valid=1";
			} break;
			case 'suspended': {
				$qtxt.="AND valid=0";
			} break;
		}
		
		$q =$this->db->query($qtxt);
		if ($q) {
			list($tot)=$this->db->fetch_row($q);
			$output['users_count']=$tot;
		}
		else {
			$output['success']=false;
		}
		
		return $output;
	}
	
	
	public function checkRegistrationCode($params) {
		$output =array('success'=>true);
		
		$registration_code_type =$params['reg_code_type'];
		$code =$params['reg_code'];
		
		if (empty($registration_code_type) || empty($code)) {
			$output['success']=false;
		}
		else {
			require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));
			$user_manager = new UserManager();
			
			$res =$user_manager->checkRegistrationCode($code, $registration_code_type);
			if (!$res) {
				$output['success']=false;
			}
		}
		
		return $output;
	}
	
	
	/**
	 * Check if a user exists by its username (userid)
	 * or its email if also_check_as_email is true
	 * @param type $params
	 *   - userid
	 *   - also_check_as_email
	 * @return boolean 
	 */
	public function checkUsername($params) {
		$output =array('success'=>true);
		
		$userid =$params['userid'];
		$query = "SELECT idst, userid, firstname, lastname, pass, email, avatar, signature,"
				." level, lastenter, valid, pwd_expire_at, register_date, lastenter, force_change,
					 facebook_id, twitter_id, linkedin_id, google_id, privacy_policy "
				." FROM ".$this->aclManager->_getTableUser();
		$query .= " WHERE userid = '".$this->aclManager->absoluteId($userid)."'";
		
		if ($params['also_check_as_email']) {
			$query .= " OR email='".$params['also_check_as_email']."'";
		}
		
		$q = $this->aclManager->_executeQuery( $query );
		if( sql_num_rows( $q ) > 0 )
			$res =sql_fetch_row($q);
		else
			$res =false;
		
		
		if (!$res) {
			$output =array(
				'success'=>false,
				'message'=>'User not found',
			);
		}
		else {
			$output['idst']=(int)$res[ACL_INFO_IDST];
		}
		
		return $output;
	}
    
    /**
    * Get the ID related to a profile name
    * @param string $profile_name
    * @return int
    */
    private function getProfilebyName($profile_name){
        $out = 0;
        $q = "SELECT idst from core_group where groupid = '/framework/adminrules/".$profile_name."'";
        $res =$this->db->query($q);
        if ($res) {
            list($out)=$this->db->fetch_row($res);
        }
        return $out;    
    }
    
    
    private function hasAdminProfile($userid){
        $out = false;
        $m = new AdminmanagerAdm();
        $out = $m->getProfileAssociatedToAdmin($userid);
        return $out;
        
    }
    
    private function isAdmin($idst){
        $out = 0;
        $q = "SELECT count(*) as t from core_group_members where idst = 4 and idstMember =".$idst;
        $res =$this->db->query($q);
        if ($res) {
            list($out)=$this->db->fetch_row($res);
        }
        return $out;    

    }
    
   
    /**
       Assign an Admin Profile for a given username or email.
       @param $params
              - profile name
              - username  or user email
       @return boolean
    */
    public function assignProfile($params){
        
      $idst = $this->checkUsername($params);
      if (!array_key_exists("idst",$idst)) {
        $output = array('success'=>false, 'message'=>'Id user not found'); 
      } else {
          if (!$this->isAdmin($idst['idst'])) {
               $output = array('success'=>false, 'message'=>'User is not admin');  
          } else {
              $profile_id = $this->getProfilebyName($params['profile_name']);
              if(!$profile_id) {
                  $output = array('success'=>false, 'message'=>'Input profile does not exist'); 
              } else {
                  $m = new AdminmanagerAdm();
                  $r = $m->saveSingleAdminAssociation($profile_id, $idst['idst']);
                  $output = ($r) ? array('success'=>true, 'message'=>'Profile assigned') : array('success'=>false, 'message'=>'Profile not assigned');
              }
          }    
      }
      return  $output; 
    }
    
    
    /**
    * Assign or revoke user to an admin profile
    * @param $params
    *          username: or  user email
    *          gorup_name: string the name of a group
    *          orgchart_name: string the name of an org_chart
    *          orgchart_code: the code of an org_chart 
    */
    public function admin_assignUsers($params, $op){
        
        $output = array('success'=>false, 'message'=> $op.' user failed'); 
        $selected_group = 0;
        // select group ID
        if (array_key_exists('group_name', $params)){
            $q = "select idst from core_group where core_group.groupid = '/".$params['group_name']. "'";
                  $rs =$this->db->query($q);
                  if ($rs){
                        list($selected_group) = $this->db->fetch_row($rs);
                  }
        }

        // select users in org chart by code
        if (array_key_exists('orgchart_code', $params)){
            $q = "select idst_ocd from core_org_chart_tree where code = '".$params['orgchart_code']."'";
            $rs =$this->db->query($q);
            if ($rs){
                list($selected_group) = $this->db->fetch_row($rs);
            }
        }
        
        // select users in org chart by name
        if (array_key_exists('orgchart_name', $params)){
            $q = "select id_dir from core_org_chart where translation = '".$params['orgchart_name']."' LIMIT 1";
            $rs =$this->db->query($q);
            if ($rs){
                list($id_org) = $this->db->fetch_row($rs);
                $q = "select idst_ocd from core_org_chart_tree where idOrg = ".intval($id_org);
                $rs =$this->db->query($q);
                if ($rs) {
                    list($selected_group) = $this->db->fetch_row($rs);
                }
            }
        }
        
        
        // associates users to admin
        if(array_key_exists('userid', $params) && $selected_group > 0 ){
            $idst = $this->checkUsername($params);            
            if ($this->isAdmin($idst['idst'])) {   // check admin
                $id_admin = intval($idst['idst']);
                if ($this -> hasAdminProfile($id_admin)) {
                    if ($op == 'assign') {
                        $q = "INSERT INTO core_admin_tree (idst, idstAdmin) VALUES (".$selected_group.",".$id_admin.")";
                    } else {
                        $q = "DELETE FROM core_admin_tree WHERE idst=".$selected_group. " and idstAdmin=".$id_admin;
                    }    
                    $r = $this->db->query($q);
                    if ($r) {
                        $output = array('success'=>true, 'message'=>$op.' user success'); 
                    }
                } else {
                    $output = array('success'=>fails, 'message'=>$op.' user has not an admin profile'); 
                }        
            } else {
                $output = array('success'=>fails, 'message'=>$op.' user is not an admin'); 
            }
        }
        return $output;
        
    }
    
    private function getCatalogueID($catalogue_name){
        $cat_str = implode("','",$catalogue_name);
        $q = "select idCatalogue from learning_catalogue where name in ('".$cat_str."')";
        $r = $this->db->query($q);
        if ($r){
            while ($row = $this->db->fetch_array($r)){
                $selected_cat[] = $row[0];    
            }
            return $selected_cat;
        }
        return '';   
    }
    
    
    private function getCoursePathID($coursepath_name){
        $cat_str = implode("','",$coursepath_name);
        $q = "select id_path from learning_coursepath where path_name in ('".$cat_str."')";
        $r = $this->db->query($q);
        if ($r){
            while ($row = $this->db->fetch_array($r)){
                $selected_course_path[] = $row[0];    
            }
            return $selected_course_path;
        }
        return '';   
    }    
    
    /**
    *  Assign catalogues or coursepath to an admin
    * @param $params
    *          userid: string
    *          also_check_as_email: user's email 
    *          coursepath_names: course paths name array
    *          catalogue_names: catalogues name array    
    */
    public function admin_assignCourses($params){
        $idst = $this->checkUsername($params);
        $admin = intval($idst['idst']);
        if ($this->isAdmin($admin) && $this->hasAdminProfile($admin)){
            $coursepath =  isset($params['coursepath_names'])? $this->getCoursePathID($params['coursepath_names']):'';
            $catalogue =  isset($params['catalogue_names'])? $this->getCatalogueID($params['catalogue_names']):'';
            $m = new AdminmanagerAdm();
            $r = $m->saveCoursesAssociation($admin,"",$coursepath,$catalogue);
            if ($r) {
                $output = array('success'=>true, 'message'=>$op.' courses associated to admin');         
            } else {
                $output = array('success'=>fails, 'message'=>$op.' courses not associated to admin');         
            }
        } else {
            $output = array('success'=>fails, 'message'=>$op.' user is not an admin or has not ad admin profile');         
        }
        return $output;
        
    }

	
	
	// ---------------------------------------------------------------------------
	
	
	public function call($name, $params) {
        // WS DEBUGGING
        //DebugBreak('session@localhost');        
		$output = false;

		// Loads user information according to the external user data provided:
		$params =$this->checkExternalUser($params, $_POST);

		if (!empty($params[0]) && !isset($params['idst'])) {
			$params['idst']=$params[0]; //params[0] should contain user idst
		}
		
		if (empty($params['idst']) && !empty($_POST['idst'])) {
			$params['idst']=(int)$_POST['idst'];
		}

		switch ($name) {
			case 'listUsers':
			case 'userslist': {
				$list = $this->getUsersList();
				if ($list['success'])
					$output = array('success'=>true, 'list'=>$list['users_list']);
				else
					$output = array('success'=>false);
			} break;

			case 'userdetails': {
				if (count($params)>0 && !isset($params['ext_not_found'])) { //params[0] should contain user id

					if (is_numeric($params['idst'])) {
						$res = $this->getUserDetails($params['idst']);
						if (!$res) {
							$output = array('success'=>false, 'message'=>"Error: unable to retrieve user details.");
						}else{
							$output = array('success'=>true, 'details'=>$res['details']);
						}
					} else {
						$output = array('success'=>false, 'message'=>'Invalid passed parameter.');
					}
				} else {
					$output = array('success'=>false, 'message'=>'No parameter provided.');
				}
			} break;

			case 'customfields': {
				$tmp_lang = false; //if not specified, use default language
				if (isset($params['language'])) { $tmp_lang = $params['language']; } //check if a language has been specified
				$res = $this->getCustomFields($tmp_lang);
				if ($res != false) {
					$output = array('success'=>true, 'custom_fields'=>$res);
				} else {
					$output = array('success'=>false, 'message'=>'Error: unable to retrieve custom fields.');
				}
			} break;

			case 'create':
			case 'createuser': {
				$res = $this->createUser($params, $_POST);
				if (is_array($res)) {
					$output = $res;
				}
				else if ($res > 0) {
					$output = array('success'=>true, 'idst'=>$res);
				} else {
					$output = array('success'=>false, 'message'=>'Error: unable to create new user.');
				}
			} break;

			case 'edit':
			case 'updateuser': {
				if (count($params)>0 && !isset($params['ext_not_found'])) { //params[0] should contain user id
					$res = $this->updateUser($params['idst'], $_POST);
					
					if ($res > 0) {
						$output = array('success'=>true);
					} elseif ($res < 0) {
						$output = array('success'=>false, 'message'=>'Error: incorrect param idst.');
					}
				} else {
					$output = array('success'=>false, 'message'=>'Error: user id to update has not been specified.');
				}
			} break;

			case 'delete':
			case 'deleteuser': {
				if (count($params)>0 && !isset($params['ext_not_found'])) { //params[0] should contain user id
					$output = $this->deleteUser($params['idst'], $_POST);
				} else {
					$output = array('success'=>false, 'message'=>'Error: user id to update has not been specified.');
				}
			} break;

			case 'userdetailsbyuserid': {
				$acl_man = new DoceboACLManager();
				$idst = $acl_man->getUserST($params['userid']);
				if (!$idst) {
					$output = array('success'=>false, 'message'=>'Error: invalid userid: '.$params['userid'].'.');
				} else {
					$output = $this->getUserDetails($idst);
				}
			} break;
			
			case 'userdetailsfromcredentials': {
				if (!isset($params['ext_not_found'])) {
					$output = $this->getUserDetailsFromCredentials($_POST);
				}
			} break;

			case 'updateuserbyuserid': {
				if (count($params)>0) { //params[0] should contain user id
					$acl_man = new DoceboACLManager();
					$idst = $acl_man->getUserST($params['userid']);
					if (!$idst) {
						$output = array('success'=>false, 'message'=>'Error: invalid userid: '.$params['userid'].'.');
					} else {
						$res = $this->updateUser($idst, $_POST);
						$output = array('success'=>true);
					}
				} else {
					$output = array('success'=>false, 'message'=>'Error: user id to update has not been specified.');
				}
			} break;


			case 'userCourses':
			case 'mycourses': {
				if (!isset($params['ext_not_found'])) {
					$output = $this->getMyCourses($params['idst'], $_POST);
				}
			} break;


			case 'kbsearch': {
				if (!isset($params['ext_not_found'])) {
					$output = $this->KbSearch($params['idst'], $_POST);
				}
			} break;


			case 'importextusers': {
				$output = $this->importExternalUsers($_POST);
			} break;

			case 'importextusersfromemail': {
				$output = $this->importExternalUsersFromEmail($_POST);
			} break;

		
			case 'countusers': {
				$output = $this->countUsers($_POST);
			} break;
		
		
			case 'checkregcode': {
				$output = $this->checkRegistrationCode($_POST);
			} break;
		
		
			case 'checkUsername':
			case 'checkusername': {
				$output = $this->checkUsername($_POST);
			} break;
            
            case 'assignprofile':
            case 'assignProfile':{
                $output = $this->assignProfile($_POST);
            } break;
            
            case 'admin_assignUsers':
            case 'admin_assignusers':{
                $output = $this->admin_assignUsers($_POST, 'assign');
            } break;
            
            case 'admin_revokeUsers':
            case 'admin_revokeusers':{
                $output = $this->admin_assignUsers($_POST, 'revoke');
            } break;
            
            case 'admin_assignCourses':
            case 'admin_assigncourses':{
                $output = $this->admin_assignCourses($_POST);
            } break;
            

		

			default: $output = parent::call($name, $_POST);
		}
		return $output;
	}

}