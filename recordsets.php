<?php

// Output one triple per BOLD dataset (DS- and DATASET- recordset codes) asserting
// that it is a schema:Dataset. Project codes (e.g. ABBAB) are skipped.
//
// Uses the Parquet version of the dump via the DuckDB CLI, as getting the distinct
// codes from the TSV would mean scanning the whole file.
//
// Usage: php recordsets.php > recordsets.nt

require_once(dirname(__FILE__) . '/config.inc.php');

if (!file_exists($config['bold_parquet']))
{
	fwrite(STDERR, "Parquet file not found: " . $config['bold_parquet'] . "\n");
	exit(1);
}

// SQL string literal
function sql_quote($string)
{
	return "'" . str_replace("'", "''", $string) . "'";
}

$sql = "
COPY (
	WITH codes AS (
		SELECT DISTINCT unnest(string_split(
			regexp_replace(bold_recordset_code_arr, '[\\[\\]'' ]', '', 'g'), ',')) AS code
		FROM read_parquet(" . sql_quote($config['bold_parquet']) . ")
		WHERE bold_recordset_code_arr NOT IN ('', 'None', '[]')
	)
	SELECT '<https://portal.boldsystems.org/recordset/' || code
		|| '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <https://schema.org/Dataset> .'
	FROM codes
	WHERE code LIKE 'DS-%' OR code LIKE 'DATASET-%'
	ORDER BY 1
) TO '/dev/stdout' (HEADER false, QUOTE '', DELIMITER '\t');
";

$command = escapeshellcmd($config['duckdb']) . ' -c ' . escapeshellarg($sql);

passthru($command, $status);

if ($status != 0)
{
	fwrite(STDERR, "DuckDB failed with exit code $status\n");
	exit($status);
}

?>
