<?php

class CartAPI_Helpers
{
	public static function createSuccessResponse($encoder, $locale = null)
	{
		$root = $encoder->createRoot();
		$result = &$encoder->addContainer($root, 'Result');
		$encoder->addBoolean($result, 'Success', true);
		if ($locale !== null) CartAPI_Helpers::addLocale($encoder, $locale, $result);
		return $root;
	}

	// if $total_elements === false TotalPages and TotalElements do not appear in the response
	public static function createSuccessResponseWithPaging($encoder, $paging_request, $total_elements = false, $locale = null)
	{
		$root = $encoder->createRoot();
		$result = &$encoder->addContainer($root, 'Result');
		$encoder->addBoolean($result, 'Success', true);
		if ($locale !== null) CartAPI_Helpers::addLocale($encoder, $locale, $result);
		
		$paging = &$encoder->addContainer($root, 'Paging');
		$encoder->addNumber($paging, 'PageNumber', $paging_request['PageNumber']);
		$encoder->addNumber($paging, 'ElementsPerPage', $paging_request['ElementsPerPage']);
		if ($total_elements !== false)
		{
			$encoder->addNumber($paging, 'TotalPages', ceil($total_elements / $paging_request['ElementsPerPage']));
			$encoder->addNumber($paging, 'TotalElements', $total_elements);
		}
		return $root;
	}

	public static function dieOnError($encoder, $error, $message)
	{
		$root = $encoder->createRoot();
		$result = &$encoder->addContainer($root, 'Result');
		$encoder->addBoolean($result, 'Success', false);
		$details = &$encoder->addArray($result, 'Detail');
		$detail = &$encoder->addContainerToArray($details);
		$encoder->addString($detail, 'Error', $error);
		$encoder->addString($detail, 'Message', $message);
		$encoder->render($root);
		exit;
	}
	
	public static function dieOnErrors($encoder, $errors, $messages)
	{
		$total = 0;
		if (!is_array($errors)) 
		{
			$total = count($messages);
			$errors = array($errors); for ($i=1; $i<$total; $i++) $errors[] = $errors[0];
		}
		else if (!is_array($messages))
		{
			$total = count($errors);
			$messages = array($messages); for ($i=1; $i<$total; $i++) $messages[] = $messages[0];
		}
	
		$root = $encoder->createRoot();
		$result = &$encoder->addContainer($root, 'Result');
		$encoder->addBoolean($result, 'Success', false);
		$details = &$encoder->addArray($result, 'Detail');
		for ($i=0; $i<$total; $i++)
		{
			$detail = &$encoder->addContainerToArray($details);
			$encoder->addString($detail, 'Error', $errors[$i]);
			$encoder->addString($detail, 'Message', $messages[$i]);
		}
		$encoder->render($root);
		exit;
	}
	
	public static function addLocale($encoder, $locale, &$result)
	{
		$_locale = &$encoder->addContainer($result, 'Locale');
		if (isset($locale['Language'])) $encoder->addString($_locale, 'Language', $locale['Language']);
		if (isset($locale['Currency'])) $encoder->addString($_locale, 'Currency', $locale['Currency']);
	}

	public static function validatePagingRequest($encoder, $paging_request)
	{
		if (!is_array($paging_request)) CartAPI_Helpers::dieOnError($encoder, 'InvalidRequest', 'PagingRequest is invalid');
		if (!isset($paging_request['PageNumber'])) CartAPI_Helpers::dieOnError($encoder, 'IncompleteRequest', 'PagingRequest.PageNumber missing');
		if (!is_numeric($paging_request['PageNumber'])) CartAPI_Helpers::dieOnError($encoder, 'InvalidRequest', 'PagingRequest.PageNumber not numeric');
		if ($paging_request['PageNumber'] < 1) CartAPI_Helpers::dieOnError($encoder, 'PageOutOfBounds', 'PagingRequest.PageNumber below 1');
		if (!isset($paging_request['ElementsPerPage'])) CartAPI_Helpers::dieOnError($encoder, 'IncompleteRequest', 'PagingRequest.ElementsPerPage missing');
		if (!is_numeric($paging_request['ElementsPerPage'])) CartAPI_Helpers::dieOnError($encoder, 'InvalidRequest', 'PagingRequest.ElementsPerPage not numeric');
		if ($paging_request['ElementsPerPage'] < 1) CartAPI_Helpers::dieOnError($encoder, 'PageSizeInvalid', 'PagingRequest.ElementsPerPage below 1');
	}
	
	public static function getPagingRequestSubArrayFromAllElementsArray($paging_request, $all_elements)
	{
		$result = array();
		$i = CartAPI_Helpers::getZbOffsetFromPagingRequest($paging_request);
		$max = intval($paging_request['ElementsPerPage']);
		while (($i < count($all_elements)) && (count($result) < $max))
		{
			$result[] = $all_elements[$i];
			$i++;
		}
		return $result;
	}

	public static function getZbOffsetFromPagingRequest($paging_request)
	{
		$page_number = intval($paging_request['PageNumber']);
		$elements_per_page = intval($paging_request['ElementsPerPage']);
		$first_index_zb = ($page_number - 1) * $elements_per_page;
		return $first_index_zb;
	}

	public static function getSqlLimitFromPagingRequest($encoder, $paging_request)
	{
		CartAPI_Helpers::validatePagingRequest($encoder, $paging_request);
		$elements_per_page = intval($paging_request['ElementsPerPage']);
		$first_index_zb = CartAPI_Helpers::getZbOffsetFromPagingRequest($paging_request);
		return 'LIMIT '.(int)$first_index_zb.', '.(int)$elements_per_page;
	}

	public static function getSqlWhereFromSqlFilters($sql_filters)
	{
		if (count($sql_filters) == 0) return '';
		return 'WHERE '.implode(' AND ', $sql_filters);
	}

	public static function getSqlFilterFromFilter($encoder, $filter, $db_field_name_map)
	{
		CartAPI_Helpers::validateFilter($encoder, $filter, $db_field_name_map);
		$db_field_name = $db_field_name_map[$filter['Field']];
		
		$or_sections = array();
		if (!is_array($filter['Value'])) $filter['Value'] = array($filter['Value']);
		foreach ($filter['Value'] as $value)
		{
			$or_sections[] = CartAPI_Helpers::getSqlSectionFromFilter($encoder, $db_field_name, $filter['Relation'], $value);
		}
		
		return "( ".implode(" OR ",$or_sections)." )";
	}
	
	public static function getSqlSectionFromFilter($encoder, $db_field_name, $relation, $value)
	{
		$value = CartAPI_Helpers::sanitizeSql($value);
		
		if ($relation == 'Contains') return $db_field_name." LIKE '%".$value."%'";
		if ($relation == 'Equal') return $db_field_name." = '".$value."'";
		if ($relation == 'AboveEqual') return $db_field_name." >= ".$value;
		if ($relation == 'InGroup') return $db_field_name." IN ('".implode("','",explode(",",$value))."')";
		if ($relation == 'InRange') 
		{
			$parts = explode(",",$value);
			if (count($parts) != 2) CartAPI_Helpers::dieOnError($encoder, 'UnsupportedFilter', 'InRange does not have a valid range');
			return "( ".$db_field_name." >= ".min($parts)." AND ".$db_field_name." <= ".max($parts)." )";
		}
		
		CartAPI_Helpers::dieOnError($encoder, 'UnsupportedFilter', $relation.' filter relation is unsupported');
	}

	public static function validateFilter($encoder, $filter, $db_field_name_map = false)
	{
		if (!is_array($filter)) CartAPI_Helpers::dieOnError($encoder, 'InvalidRequest', 'Filter is invalid');
		if (!isset($filter['Field'])) CartAPI_Helpers::dieOnError($encoder, 'IncompleteRequest', 'Filter.Field missing');
		if (!isset($filter['Relation'])) CartAPI_Helpers::dieOnError($encoder, 'IncompleteRequest', 'Filter.Relation missing');
		if (!isset($filter['Value'])) CartAPI_Helpers::dieOnError($encoder, 'IncompleteRequest', 'Filter.Value missing');
		if ($db_field_name_map !== false)
		{
			if (!isset($db_field_name_map[$filter['Field']])) CartAPI_Helpers::dieOnError($encoder, 'UnsupportedFilter', $filter['Field'].' filter is unsupported');
		}
	}

	public static function sanitizeSql($string)
	{
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string);
    }
    
    public static function getDictionaryKeyAsArray($dictionary, $key)
    {
    	if (!isset($dictionary[$key])) return array();
    	if (!is_array($dictionary[$key])) return array($dictionary[$key]);
    	if (isset($dictionary[$key][0])) return $dictionary[$key];
    	return array($dictionary[$key]);
    }
    
    public static function replaceArrayContents(&$original, $new)
    {
    	array_splice($original, 0, count($original), $new);
    }
}

?>