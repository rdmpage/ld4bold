<?php

require_once(dirname(dirname(__FILE__)) . '/taxon_name_parser.php');

// name parser
$pp = new Parser();

$name = 'Pagurus nr. criniticornis';
				
$r = $pp->parse($name);

print_r($r);

?>
