<?php

// Copy to env.php (which is in .gitignore) to override the defaults in config.inc.php.
// Only set a variable if it isn't already set, so values given on the command line win,
// e.g. BOLD_PARQUET=/path/to/other.parquet php recordsets.php

if (getenv('BOLD_TSV') === false)
{
	putenv('BOLD_TSV=XXX.tar.gz');
}

if (getenv('BOLD_PARQUET') === false)
{
	putenv('BOLD_PARQUET=XXX.parquet');
}

if (getenv('DUCKDB') === false)
{
	putenv('DUCKDB=duckdb');
}

?>
