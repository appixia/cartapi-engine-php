<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Encoder.php');

// TODO: add support for on-the-fly of array encoding (at least for array parts)

class CartAPI_Mediums_JSON_Encoder extends CartAPI_Mediums_Encoder
{
	public function render($root)
	{
		$this->renderHeader();
		$this->renderField($root);
		$this->renderFooter();
	}

	private function renderHeader()
	{
		header('Content-Type: text/javascript');
		print '';
	}

	private function renderFooter()
	{
		print '';
	}

	private function renderFieldHeader($fieldname = false, $newline = '')
	{
		if ($fieldname !== false) print '"'.$fieldname.'":' . $newline;
	}

	private function renderFieldFooter($fieldname = false, $newline = '')
	{
		if ($fieldname !== false) print $newline;
	}

	private function renderField($fieldvalue, $fieldname = false)
	{
		if (!is_array($fieldvalue)) 
		{
			$this->renderFieldHeader($fieldname);
			$this->renderString($fieldvalue);
			$this->renderFieldFooter($fieldname);
		}
		else
		{
			if (count($fieldvalue) > 0)
			{
				reset($fieldvalue);
				if (is_numeric(key($fieldvalue)))
				{
					// container is array
					$this->renderFieldHeader($fieldname);
					print '[';
					$first = true;
					foreach ($fieldvalue as $fieldvalue2)
					{
						if ($first) $first = false;
						else print ',';
						$this->renderField($fieldvalue2);
					}
					print ']';
					$this->renderFieldFooter($fieldname);
				}
				else
				{
					// container is dictionary
					$this->renderFieldHeader($fieldname);
					print '{';
					$first = true;
					foreach ($fieldvalue as $fieldname2 => $fieldvalue2)
					{
						if ($first) $first = false;
						else print ',';
						$this->renderField($fieldvalue2, $fieldname2);
					}
					print '}';
					$this->renderFieldFooter($fieldname);
				}
			}
			else
			{
				$this->renderFieldHeader($fieldname);
				print '[]';
				$this->renderFieldFooter($fieldname);
			}
		}
	}

	private function renderString($string, $newline = '')
	{
		if ($string === false) print 'false' . $newline;
		else if ($string === true) print 'true' . $newline;
		else if (is_numeric($string)) print $string . $newline;
		else print '"' . addslashes($string) . '"' . $newline;
	}
}

?>