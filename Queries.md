# Queries

https://koetai.bionames.org/u/0000-0002-7101-9767/ld4bold/sparql

## Map for dataset

PREFIX : <https://schema.org/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX dwc: <http://rs.tdwg.org/dwc/terms/>

SELECT *
WHERE {
?s :isPartOf <https://portal.boldsystems.org/recordset/DS-WIHARN> .
?s wdt:P625 ?pt .
} 
LIMIT 2000

## Map of barcodes in country

PREFIX : <https://schema.org/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX dwc: <http://rs.tdwg.org/dwc/terms/>

SELECT *
WHERE {
?s dwc:countryCode "TH" .
?s wdt:P625 ?pt .
} 
LIMIT 1000

## Barcodes in country that are not species level

PREFIX : <https://schema.org/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX dwc: <http://rs.tdwg.org/dwc/terms/>

SELECT *
WHERE {
?s dwc:countryCode "TH" .
?s dwc:associatedSequences ?accession .
?s dwc:taxonRank ?rank .
FILTER(?rank != "species" && ?rank != "subspecies")
} 
LIMIT 100