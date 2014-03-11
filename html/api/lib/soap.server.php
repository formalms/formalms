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

require(_base_ . "/addons/nusoap/nusoap.php");
require(_base_ . "/api/lib/lib.api.php");

function getSOAPServer() {
	$namespace = 'http://www.w3.org/2001/XMLSchema';

	$server = new soap_server();
	$server->debug_flag = false;
	$server->configureWSDL("FormaSOAP", $namespace);
	$server->wsdl->schemaTargetNamespace = $namespace;
	
	//----------------------------------------------------------------------------
	//register types
	$server->wsdl->addComplexType(
		'customFieldName',
		'complexType',
		'struct',
		'all',
		'',
		array(
			'id' => array('name' => 'id', 'type' => 'xsd:int'),
			'name' => array('name' => 'name', 'type' => 'xsd:string')
		)
	);

	$server->wsdl->addComplexType(
		'customFieldValue',
		'complexType',
		'struct',
		'all',
		'',
		array(
			'id' => array('name' => 'id', 'type' => 'xsd:int'),
			'value' => array('name' => 'value', 'mixed' => 'true', 'type'=>'xsd:string')
		)
	);

	$server->wsdl->addComplexType(
		'customField',
		'complexType',
		'struct',
		'all',
		'',
		array(
			'id' => array('name' => 'id', 'type' => 'xsd:int'),
			'name' => array('name' => 'name', 'type' => 'xsd:string'),
			'value' => array('name' => 'value', 'mixed' => 'true', 'type'=>'xsd:string')
		)
	);

	$server->wsdl->addComplexType(
		'customFieldValuesArray',
		'complexType',
		'array',
		'',
		'SOAP-ENC:Array',
		array(),
		array(
			array(
				'ref' => 'SOAP-ENC:arrayType',
				'wsdl:arrayType' => 'customFieldValue[]'
			)
		),
		'customFieldValue'
	);

	$server->wsdl->addComplexType(
		'customFieldsArray',
		'complexType',
		'array',
		'',
		'SOAP-ENC:Array',
		array(),
		array(
			array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'customField[]')
		),
		'customField'
	);

	//extended data to create/edit a user
	$server->wsdl->addComplexType(
		'userData',
		'complexType',
		'struct',
		'all',
		'',
		array(
			'userid' => array('name' => 'userid', 'type' => 'xsd:string'),
			'firstname' => array('name' => 'firstname', 'type' => 'xsd:string'),
			'lastname' => array('name' => 'lastname', 'type' => 'xsd:string'),
			'password' => array('name' => 'password', 'type' => 'xsd:string'),
			'email' => array('name' => 'email', 'type' => 'xsd:string'),
			'signature' => array('name' => 'signature', 'type' => 'xsd:string'),
			'lastenter' => array('name' => 'lastenter', 'type' => 'xsd:date'),
			'pwd_expire_at' => array('name' => 'pwd_expire_at', 'type' => 'xsd:date'),
			'valid' => array('name' => 'valid', 'type' => 'xsd:boolean'),
			'custom_fields' => array('name' => '_customfields', 'type' => 'tns:customFieldValuesArray')
		)
	);

	//essential data of a user
	$server->wsdl->addComplexType(
		'user',
		'complexType',
		'struct',
		'all',
		'',
		array(
			'idst' => array('name' => 'idst', 'type' => 'xsd:int'),
			'userid' => array('name' => 'userid', 'type' => 'xsd:string'),
			'firstname' => array('name' => 'firstname', 'type' => 'xsd:string'),
			'lastname' => array('name' => 'lastname', 'type' => 'xsd:string'),
		)
	);

	//list of users
	$server->wsdl->addComplexType(
		'usersArray',
		'complexType',
		'array',
		'',
		'SOAP-ENC:Array',
		array(),
		array(
			array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'user[]')
		),
		'user'
	);

	//learning object id and title
	$server->wsdl->addComplexType(
		'object',
		'complexType',
		'struct',
		'all',
		'',
		array(
			'id' => array('name' => 'id', 'type' => 'xsd:int'),
			'title' => array('name' => 'title', 'type' => 'xsd:string'),
			'description' => array('name' => 'description', 'type' => 'xsd:string'),
		)
	);

	//array of learning objects
	$server->wsdl->addComplexType(
		'objectsArray',
		'complexType',
		'array',
		'',
		'SOAP-ENC:Array',
		array(),
		array(
			array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'object[]')
		),
		'object'
	);
	
	//----------------------------------------------------------------------------
	// register functions
	
	$server->register(
		'authenticate', // method name
		array('username' => 'xsd:string', 'password' => 'xsd:string', 'third_party' => 'xsd:string'), // input parameters
		array('success' => 'xsd:boolean', 'message' => 'xsd:string',   'token' => 'xsd:string', 'expire_at' => 'xsd:string'), // output parameters
        $namespace, // namespace
        $namespace . '#authenticate', // soapaction
        'rpc', // style
        'encoded', // use
        '...' // documentation
	);

	$server->register(
		'getAuthMethod', // method name
		array(), // input parameters
		array('success' => 'xsd:boolean', 'method' => 'xsd:string'), // output parameters
		$namespace, // namespace
		$namespace . '#getAuthMethod', // soapaction
		'rpc', // style
		'encoded', // use
		'...' // documentation
	);

	$server->register('createUser', // method name
		array('auth_code' => 'xsd:string', 'user_data' => 'userData'), // input parameters
		array('success' => 'xsd:boolean'), // output parameters
		$namespace, // namespace
		$namespace . '#createUser', // soapaction
		'rpc', // style
		'encoded', // use
		'...' // documentation
	);

	$server->register('updateUser', // method name
		array('auth_code' => 'xsd:string', 'idst' => 'xsd:int', 'user_data' => 'userData'), // input parameters
		array('success' => 'xsd:boolean'), // output parameters
		$namespace, // namespace
		$namespace . '#updateUser', // soapaction
		'rpc', // style
		'encoded', // use
		'...' // documentation
	);

	$server->register('usersList', // method name
		array('auth_code' => 'xsd:string'), // input parameters
		array('success' => 'xsd:boolean', 'list' => 'usersArray'), // output parameters
		$namespace, // namespace
		$namespace . '#usersList', // soapaction
		'rpc', // style
		'encoded', // use
		'...' // documentation
	);

	$server->register('userDetails', // method name
		array('auth_code' => 'xsd:string', 'idst' => 'xsd:int'), // input parameters
		array('success' => 'xsd:boolean', 'details' => 'userData'), // output parameters
		$namespace, // namespace
		$namespace . '#userDetails', // soapaction
		'rpc', // style
		'encoded', // use
		'...' // documentation
	);

	$server->register('deleteUser', // method name
		array('auth_code' => 'xsd:string', 'idst' => 'xsd:int'), // input parameters
		array('success' => 'xsd:boolean', 'message' => 'xsd:string'), // output parameters
		$namespace, // namespace
		$namespace . '#deleteUser', // soapaction
		'rpc', // style
		'encoded', // use
		'...' // documentation
	);

	//----------------------------------------------------------------------------

	$server->register('searchObjects', // method name
		array('auth_code' => 'xsd:string', 'type' => 'xsd:string', 'key' => 'xsd:string'), // input parameters
		array('success' => 'xsd:boolean', 'objects' => 'objectsArray'), // output parameters
		$namespace, // namespace
		$namespace . '#searchObjects', // soapaction
		'rpc', // style
		'encoded', // use
		'...' // documentation
	);

	$server->register('requestObject', // method name
		array('auth_code' => 'xsd:string', 'id_object' => 'xsd:int', 'type' => 'xsd:string', 'id_user' => 'xsd:int'), // input parameters
		array('success' => 'xsd:boolean', 'object' => 'xsd:string'), // output parameters
		$namespace, // namespace
		$namespace . '#requestObject', // soapaction
		'rpc', // style
		'encoded', // use
		'...' // documentation
	);

	return $server;
}

// ----------------------------------------------------------------------------------
// functions list (wrapping classes)

function authenticate($username, $password , $third_party) {
	$module = 'auth';
	$function = 'authenticate';
	$params = array(
		'username' => $username,
		'password' => $password, 
        'third_party' => $third_party
	);

	$output = API::Execute(false, $module, $function, $params);
	return $output;
}

function getAuthMethod() {
	$module = 'auth';
	$function = 'getauthmethod';
	$params = false;

	$output = API::Execute(false, $module, $function, $params);
	return $output;
}

function createUser($auth_code, $params) {
	$module = 'user';
	$function = 'createuser';
	//$params = $params;

	$output = API::Execute($auth_code, $module, $function, $params);
	return $output;
}

function editUser($auth_code, $idst, $params) {
	$module = 'user';
	$function = 'updateuser';
	$params['idst'] = $idst;

	$output = API::Execute($auth_code, $module, $function, $params);
	return $output;
}

function userDetails($auth_code, $idst) {
	$module = 'user';
	$function = 'userdetails';
	$params = array('idst' => $idst);

	$output = API::Execute($auth_code, $module, $function, $params);
	return $output;
}

function customFields($auth_code, $language) {
	$module = 'user';
	$function = 'customfields';
	$params = array('language' => $language);

	$output = API::Execute($auth_code, $module, $function, $params);
	return $output;
}

function usersList($auth_code) {
	$module = 'user';
	$function = 'userslist';
	$params = false;

	$output = API::Execute($auth_code, $module, $function, $params);
	return $output;
}

function deleteUser($auth_code, $idst) {
	$module = 'user';
	$function = 'deleteUser';
	$params = false;

	$output = API::Execute($auth_code, $module, $function, $params);
	return $output;
}

function searchObjects($auth_code, $type, $key) {
	$module = 'LO';
	$function = 'searchObjects';
	$params = array(
		'type' => $type,
		'key' => $key
	);

	$output = API::Execute($auth_code, $module, $function, $params);
	return $output;
}

function requestObject($auth_code, $id_object, $type, $id_user) {
	$module = 'LO';
	$function = 'requestObject';
	$params = array(
		'id_object' => $id_object,
		'type' => $type,
		'id_user' => $id_user
	);

	$output = API::Execute($auth_code, $module, $function, $params);
	return $output;
}
