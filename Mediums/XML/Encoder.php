<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Encoder.php');

// TODO: add support for on-the-fly of array encoding (at least for array parts)

class CartAPI_Mediums_XML_Encoder extends CartAPI_Mediums_Encoder
{
	public function render($root)
	{
		$this->renderHeader();
		$this->renderField($root);
		$this->renderFooter();
	}

	private function renderHeader()
	{
		header("Content-Type:text/xml; charset=utf-8");
		print '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<api>'."\n";
	}

	private function renderFooter()
	{
		print '</api>';
	}

	private function renderFieldHeader($fieldname = false, $newline = '')
	{
		if ($fieldname !== false) print '<'.$fieldname.'>' . $newline;
	}

	private function renderFieldFooter($fieldname = false, $newline = '')
	{
		if ($fieldname !== false) print '</'.$fieldname.'>' . $newline;
	}
	
	private function renderFieldEmpty($fieldname = false, $newline = '')
	{
		if ($fieldname !== false) print '<'.$fieldname.'/>' . $newline;
	}

	private function renderField($fieldvalue, $fieldname = false)
	{
		if (!is_array($fieldvalue)) 
		{
			$this->renderFieldHeader($fieldname);
			$this->renderString($fieldvalue);
			$this->renderFieldFooter($fieldname, "\n");
		}
		else
		{
			if (count($fieldvalue) > 0)
			{
				reset($fieldvalue);
				if (is_numeric(key($fieldvalue)))
				{
					// container is array
					foreach ($fieldvalue as $fieldvalue2)
					{
						$this->renderField($fieldvalue2, $fieldname);
					}			
				}
				else
				{
					// container is dictionary
					$this->renderFieldHeader($fieldname, "\n");
					foreach ($fieldvalue as $fieldname2 => $fieldvalue2)
					{
						$this->renderField($fieldvalue2, $fieldname2);
					}
					$this->renderFieldFooter($fieldname, "\n");
				}
			}
			else
			{
				$this->renderFieldEmpty($fieldname, "\n");
			}
		}
	}

	private function renderString($string, $newline = '')
	{
		if ($string === false) $string = 'false';
		if ($string === true) $string = 'true';
		print str_replace(array("&", "<", ">", "\"", "'"), array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;"), $string) . $newline;
	}
}

?>