<?php

class CartAPI_Mediums_Encoder
{
	public function createRoot()
	{
		return array();
	}

	public function addString(&$container, $fieldname, $string)
	{
		$this->addFieldAutoArray($container, $fieldname, (string)$string);
	}

	public function addNumber(&$container, $fieldname, $number)
	{
		$this->addFieldAutoArray($container, $fieldname, floatval($number));
	}

	public function addBoolean(&$container, $fieldname, $boolean)
	{
		$this->addFieldAutoArray($container, $fieldname, (bool)$boolean);
	}

	public function &addContainer(&$container, $fieldname)
	{
		return $this->addFieldAutoArray($container, $fieldname, array());
	}

	public function &addArray(&$container, $fieldname)
	{
		$container[$fieldname] = array();
		return $container[$fieldname];
	}
	
	public function addStringToArray(&$array, $string)
	{
		$array[] = (string)$string;
	}

	public function addNumberToArray(&$array, $number)
	{
		$array[] = floatval($number);
	}

	public function addBooleanToArray(&$array, $boolean)
	{
		$array[] = (bool)$boolean;
	}

	public function &addContainerToArray(&$array)
	{
		$array[] = array();
		return $array[count($array)-1];
	}
	
	// the following 2 functions are used to encode an entire php array at once
	// this should normally NOT be used, only in special cases
	// since our implementation of this encoder is by representing everything as native php arrays anyways,
	// the implementation is very simple
	// if anyone makes a more complex Encoder, please note that this implementation may take much more work
	//  (recursively go over the php array and encode every field)
	public function &addPhpArray(&$container, $fieldname, $phpArray)
	{
		return $this->addFieldAutoArray($container, $fieldname, $phpArray);
	}
	public function &addPhpArrayToArray(&$array, $phpArray)
	{
		$array[] = $phpArray;
		return $array[count($array)-1];
	}

	// adds a field and auto-expands it to an array as needed
	protected function &addFieldAutoArray(&$container, $fieldname, $fieldvalue)
	{
		if (!isset($container[$fieldname])) 
		{
			$container[$fieldname] = $fieldvalue;
			return $container[$fieldname];
		}
		else
		{
			if (!is_array($container[$fieldname]))
			{
				$container[$fieldname] = array($container[$fieldname], $fieldvalue);
				return $container[$fieldname][1];
			}
			else
			{
				reset($container[$fieldname]);
				if ((count($container[$fieldname]) > 0) && (is_numeric(key($container[$fieldname]))))
				{
					// array
					$container[$fieldname][] = $fieldvalue;
					return $container[$fieldname][count($container[$fieldname])-1];
				}
				else
				{
					// dictionary
					$container[$fieldname] = array($container[$fieldname], $fieldvalue);
					return $container[$fieldname][1];
				}
			}
		}
	}




}

?>