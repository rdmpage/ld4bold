<?php

// Extract barcode record and output as triples

require_once(dirname(__FILE__) . '/shared.php');
require_once(dirname(__FILE__) . '/taxon_name_parser.php');

// Where possible we align with GBIF and GBIF RDF. There is also a mapping
// at https://github.com/DNAdiversity/BCDM but GBIF does not seem to follow that.

// name parser
$pp = new Parser();

$headings = array();

$row_count = 0;

$filename = "test/examples.tsv";

$filename = "test/DS-CRUSTACE.tsv"; // see also https://www.flickr.com/photos/artour_a/albums/72157606769404758/

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
			
			$rdf_obj = new stdclass;
			
			// we will generate redundant triples for some records as there are barcodes
			// with multiple sequences
			
			$rdf_obj->{'@id'}      = 'https://portal.boldsystems.org/record/' . $data->processid;
			$rdf_obj->{'@type'}    = 'http://rs.tdwg.org/dwc/terms/Occurrence';
			
			// catalogue numbers----------------------------------------------------------
			$rdf_obj->occurrenceID = $data->processid; // GBIF RDF ignores this
			
			if (isset($data->museumid))
			{
				$rdf_obj->catalogNumber = $data->museumid;
			}
			if (isset($data->fieldid))
			{
				$rdf_obj->fieldNumber = $data->fieldid; // GBIF RDF ignores this
			}

			if (isset($data->inst))
			{
				$rdf_obj->institutionCode = $data->inst; 
			}

			// taxonomy-------------------------------------------------------------------
			// BIN
			if (isset($data->bin_uri))
			{
				// GBIF maps this to dwc:taxonConceptID
				// GBIF RDF ignores taxonConceptID and maps occurrences to GBIF taxon ids using dwciri:toTaxon
			
				// http://rs.tdwg.org/dwc/iri/toTaxon
				$rdf_obj->{'dwciri:toTaxon'}[] = 'https://portal.boldsystems.org/bin/' . $data->bin_uri;
				
				// if we treat a BIN simply as a set
				$rdf_obj->isPartOf[] = 'https://portal.boldsystems.org/bin/' . $data->bin_uri;				
			}
			
			// BOLD taxonomy id
			if (isset($data->taxid))
			{
				// http://rs.tdwg.org/dwc/iri/toTaxon
				// Identifiers.org
				$rdf_obj->{'dwciri:toTaxon'}[] = 'https://identifiers.org/bold.taxonomy:' . $data->taxid;
			}
			
			// identification-----------------------------------------------------------------			
			// are we a formal or informal taxon?
			if (isset($data->identification))
			{
				$r = $pp->parse($data->identification);
				
				if (isset($r->scientificName) && $r->scientificName->parsed)
				{
					$rdf_obj->scientificName = $data->identification;	
				}
				else
				{
					// informal name
					$rdf_obj->verbatimIdentification = $data->identification;					
				}
			}
				
			if (isset($data->identification_rank))
			{
				$rdf_obj->taxonRank = $data->identification_rank; 
			}			
			
			// identification by
			if (isset($data->identified_by))
			{
				$rdf_obj->identifiedBy = $data->identified_by; 
			}
			
			// collection-----------------------------------------------------------------			
			if (isset($data->collectors))
			{
				$rdf_obj->recordedBy = $data->collectors; 
			}
			
			// location-------------------------------------------------------------------
			if (isset($data->{'country/ocean'}))
			{
				$rdf_obj->country = $data->{'country/ocean'}; 
			}
			if (isset($data->{'province/state'}))
			{
				$rdf_obj->stateProvince = $data->{'province/state'}; 
			}
			if (isset($data->site))
			{
				$rdf_obj->locality = $data->site; 
			}
			if (isset($data->country_iso))
			{
				$rdf_obj->countryCode = $data->country_iso; 
			}
			
			// geocoordinates-------------------------------------------------------------
			if (isset($data->coord))
			{
				$go = true;
				
				// filter out country centroids
				if (isset($data->coord_source) && ($data->coord_source === "Coordinates from country centroid"))
				{
					$go = false;
				}
			
				if ($go)
				{
					if (preg_match('/[\(|\[](.*),\s*(.*)[\)|\]]/', $data->coord, $m))
					{	
						// Darwin core
						$rdf_obj->decimalLatitude = (float) $m[1];
						$rdf_obj->decimalLongitude = (float) $m[2];
						
						$rdf_obj->{'wdt:P625'} = 'POINT(' . format_coordinate($rdf_obj->decimalLongitude) . ' ' . format_coordinate($rdf_obj->decimalLatitude) . ')';
					}						
				}			
			}
			
			// sequence(s)----------------------------------------------------------------
			if (isset($data->insdc_acs))
			{
				// NCBI URL
				$rdf_obj->associatedSequences = 'https://www.ncbi.nlm.nih.gov/nuccore/' . $data->insdc_acs;
				
				// Identifiers.org
				$rdf_obj->associatedSequences = 'https://identifiers.org/nucleotide:' . $data->insdc_acs;
			}
						
			// dataset membership---------------------------------------------------------
			if (isset($data->bold_recordset_code_arr))
			{
				$string = $data->bold_recordset_code_arr;
				$string = preg_replace('/^\[/', '', $string);						
				$string = preg_replace('/\]$/', '', $string);
				$string = preg_replace('/\'/', '', $string);
				
				$datasets = preg_split('/,\s+/', $string);
				
				foreach ($datasets as $recordset)
				{
					$rdf_obj->isPartOf[] = 'https://portal.boldsystems.org/recordset/' . $recordset;
				}
			}
			
			// print_r($rdf_obj);	
			
			// triples
			$triples = [];
			
			// dwc:Occurrence
			$s = $rdf_obj->{'@id'};
			
			foreach ($rdf_obj as $k => $v)
			{
				switch ($k)
				{
					case '@type':
						$p = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
						$o = $rdf_obj->{'@type'};	
						$triples[] = [$s, $p, $o];						
						break;
						
					case 'dwciri:toTaxon':
						foreach ($v as $o)
						{
							$p = 'http://rs.tdwg.org/dwc/iri/toTaxon';
							$triples[] = [$s, $p, $o];	
						}
						break;
						
					case 'isPartOf':
						foreach ($v as $o)
						{
							$p = 'https://schema.org/isPartOf';
							$triples[] = [$s, $p, $o];	
						}
						break;
						
					case 'decimalLatitude':
						$p = 'http://rs.tdwg.org/dwc/terms/' . $k;
						$o = '"' . $v . '"^^<http://www.w3.org/2001/XMLSchema#decimal>';
						$triples[] = [$s, $p, $o];
						break;

					case 'decimalLongitude':
						$p = 'http://rs.tdwg.org/dwc/terms/' . $k;
						$o = '"' . $v . '"^^<http://www.w3.org/2001/XMLSchema#decimal>';
						$triples[] = [$s, $p, $o];
						break;

					case 'wdt:P625':
						$p = 'http://www.wikidata.org/prop/direct/P625';
						$o = '"' . $v . '"^^<http://www.opengis.net/ont/geosparql#wktLiteral>';
						$triples[] = [$s, $p, $o];
						break;
						
					case 'associatedSequences':
						$p = 'http://rs.tdwg.org/dwc/terms/' . $k;
						$triples[] = [$s, $p, $v];	
						break;
				
					default:
						if (!is_array($v))
						{
							$p = 'http://rs.tdwg.org/dwc/terms/' . $k;
							$o = '"' . nice_literal($v) . '"';
							$triples[] = [$s, $p, $o];						
						}					
						break;
				}
			
			}
			
			
			$output = dump_triples($triples);			
		echo $output . "\n";
		
			
			
			
	
			
			
			
		}
	}
	$row_count++;
}	

?>
