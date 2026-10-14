<?php

// Links between new names and DOIs

ini_set('memory_limit', '-1');

$headings = array();

$row_count = 0;

$filename = "bionames.tsv";

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
		
	$row = explode("\t",$line);
	
	$go = is_array($row) && count($row) > 1;
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;	
		}
		else
		{
			$data = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if (trim($v) != '' && $v != "None")
				{
					$data->{$headings[$k]} = $v;
				}
			}
		
			// print_r($data);	
			
			// triples
			
			echo "\n# $data->id\n";
			
			// name				
			echo 
				  '<https://bionames.org/names/urn:lsid:organismnames.com:name:' . $data->id . '> '
				. '<https://schema.org/name> '
				. '"' . $data->nameComplete . '" .'
				. "\n";
				
			// type 
			echo 
				  '<https://bionames.org/names/urn:lsid:organismnames.com:name:' . $data->id . '> '
				. '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '
				. '<https://schema.org/TaxonName> .' 
				. "\n";
				
			$doi = $data->doi;
			
			// clean DOI
			$doi = strtolower($doi);
			$doi = str_replace('<', '%3c', $doi);
			$doi = str_replace('>', '%3e', $doi);
			$doi = str_replace('[', '%5b', $doi);
			$doi = str_replace(']', '%5d', $doi);
		
			// DOI
			echo 
				'<https://bionames.org/names/urn:lsid:organismnames.com:name:' . $data->id . '> '
				. '<https://schema.org/isBasedOn> '
				. '<https://doi.org/' . $doi . '> .'
				. "\n";

		}
	}

	$row_count++;
}	


?>
