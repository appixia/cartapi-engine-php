<?php

class CartAPI_Engine
{
	public static $REQUEST_METADATA_FIELDS = array('X-OPERATION','X-VERSION','X-TOKEN','X-FORMAT','X-LANGUAGE','X-CURRENCY','X-FORMFACTOR','X-SESSION');

	// returns false on failure
	public static function handleRequest()
	{
		$request = array();

		// parse the request metadata
		$request['metadata'] = CartAPI_Engine::getRequestMetadata();

		// parse the request post data (if found)
		$request['data'] = array();
		$post_data = CartAPI_Engine::getRequestPostData();
		if ($post_data !== false)
		{
			$decoder = CartAPI_Engine::getDecoder($request['metadata']['X-FORMAT']);
			if ($decoder !== false) $request['data'] = $decoder->parse($post_data);
		}

		// override with parameters passed on the URL
		CartAPI_Engine::parseUrlRequestData($request['data']);

		// prepare an encoder for the response
		$request['encoder'] = CartAPI_Engine::getEncoder($request['metadata']['X-FORMAT']);
		if ($request['encoder'] === false) return false;

		// do some sanity checking
		if (!isset($request['metadata']['X-OPERATION'])) CartAPI_Helpers::dieOnError($request['encoder'], 'IncompleteMetadata', 'X-OPERATION missing from metadata');
		
		return $request;
	}

	public static function getEncoder($medium)
	{
		return CartAPI_Engine::_newMediumClass($medium, 'Encoder');
	}

	public static function getDecoder($medium)
	{
		return CartAPI_Engine::_newMediumClass($medium, 'Decoder');
	}

	public static function getRequestMetadata()
	{
		$res = array();

		// defaults
		$res['X-FORMAT'] = 'XML';
		$res['X-VERSION'] = 1;
		
		// both are remarked since we take locale defaults from the cart
		//$res['X-LANGUAGE'] = 'en';
		//$res['X-CURRENCY'] = 'USD';

		// first look in HTTP headers
		foreach (CartAPI_Engine::$REQUEST_METADATA_FIELDS as $field)
		{
			// first check in HTTP headers
			$server_var = 'HTTP_'.$field;
			if (isset($_SERVER[$server_var])) $res[$field] = $_SERVER[$server_var];

			// override with URL
			if (isset($_GET[$field])) $res[$field] = $_GET[$field];
		}

		return $res;
	}

	public static function parseUrlRequestData(&$request_data)
	{
		foreach ($_GET as $param_name => $param_value) $request_data[$param_name] = $param_value;
	}

	// return false if none
	public static function getRequestPostData()
	{
		if ($_SERVER['REQUEST_METHOD'] != 'POST') return false;
		$post_data = file_get_contents('php://input');
		if (($post_data === false) || empty($post_data)) return false;
		return $post_data;
	}

	private static function _newMediumClass($medium, $class)
	{
		$filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Mediums' . DIRECTORY_SEPARATOR . $medium . DIRECTORY_SEPARATOR . $class . '.php';
		$classname = 'CartAPI_Mediums_' . $medium . '_' . $class;
		if (!file_exists($filename)) { print 'not exist';  return false; }
		require_once($filename);
		if (!class_exists($classname, false)) { print 'class'; return false; }
		return new $classname();
	}

}

?>