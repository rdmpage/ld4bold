<?php

/**
 * @file config.inc.php
 *
 * Global configuration. Values come from environment variables so that PHP and
 * bash scripts share the same names; defaults point at the reference dump.
 *
 * Precedence: shell environment > env.php > defaults below.
 *
 */

global $config;

// Date timezone
date_default_timezone_set('UTC');

// Environment----------------------------------------------------------------------------
// Local overrides, in .gitignore (see env.example.php)
if (file_exists(dirname(__FILE__) . '/env.php'))
{
	include 'env.php';
}

//----------------------------------------------------------------------------------------
function config_env($name, $default)
{
	$value = getenv($name);
	return ($value === false || $value === '') ? $default : $value;
}

// BOLD data package----------------------------------------------------------------------
// Reference dataset is BOLD_Public.06-Sep-2024
$config['bold_tsv'] = config_env('BOLD_TSV',
	'/Volumes/Acer/BOLD data packages/snapshots/BOLD_Public.06-Sep-2024.tar.gz');

$config['bold_parquet'] = config_env('BOLD_PARQUET',
	'/Volumes/Acer/BOLD data packages/parquet/BOLD_Public.06-Sep-2024.parquet');

// DuckDB CLI-----------------------------------------------------------------------------
$config['duckdb'] = config_env('DUCKDB', 'duckdb');

?>
