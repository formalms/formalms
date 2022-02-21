<?php




function addRoles($roles, $group_id) {

	foreach ($roles as $val) {

		$role=trim($val);

		if (!empty($role)) {

			$qtxt="SELECT * FROM core_role WHERE roleid='".$role."'";
			$q=sql_query($qtxt);
			if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }

			if (($q) && (sql_num_rows($q) == 0)) {

				// echo("Adding role: ".$role."<br />\n");

				$qtxt="INSERT INTO core_st (idst) VALUES ('')";
				$q=sql_query($qtxt);
				if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }

				$qtxt="INSERT INTO core_role (idst, roleid) VALUES (LAST_INSERT_ID() , '".$role."')";
				$q=sql_query($qtxt);
				if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }

				if ( !empty($group_id) ) {

					$qtxt="INSERT INTO core_role_members (idst, idstMember) VALUES ( LAST_INSERT_ID(), '".$group_id."')";
					$q=sql_query($qtxt);
					if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }
				}

			} else {

				if ( !empty($group_id) ) {
					$obj = sql_fetch_object($q);

					$qtxt="INSERT IGNORE INTO core_role_members (idst, idstMember) VALUES ( '".$obj->idst."', '".$group_id."')";
					$q=sql_query($qtxt);
					if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }
				}
			}

		}

	}
}


function getGroupIdst($group) {
	$res =0;

	$qtxt="SELECT idst FROM core_group WHERE groupid='".$group."'";
	$q=sql_query($qtxt);
	if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }

	if (($q) && (sql_num_rows($q) > 0)) {
		$row=sql_fetch_assoc($q);
		$res=$row["idst"];
	}

	if ($res == 0)
		die('Group not found');

	return $res;
}