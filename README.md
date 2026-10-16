# LD4BOLD - Linked data for BOLD

Linked data representation of DNA barcoding data.

## Goals

The goal is to have a RDF representation of BOLD DNA barcode data with a focus on connecting those barcodes to the research that generated and uses the barcodes. The term “barcode” in this context means a DNA sequence intended to be used to identify an organism, together with any relevant information about how and where that sequence was collected.

## Related work

There is a mapping between Barcode Core Data Model(BCDM) and Darwin Core at https://github.com/DNAdiversity/BCDM.

The pipeline GBIF use to import BOLD data is https://github.com/gbif/bold-dwca-pipeline

The pipleine Jerven Bolleman et al. use to convert GBIF occurrences to RDF is https://github.com/Micelio/gbif_parquet. That project makes extensive use of the Darwin Core IRI namespace http://rs.tdwg.org/dwc/iri/ described in Baskauf et al. (2016).

## BOLD data

The primary source for DNA barcodes is the BOLD dataset. The experiments reported here use the data dump BOLD_Public.06-Sep–2024.tar.gz, which is the same dataset used to create BOLD View https://boldview.iphylo.org.

## Barcode records

The core barcode record information is mapped to Darwin Core following a subset of the [mapping used by GBIF](https://github.com/gbif/bold-dwca-pipeline). This means it is easy to compare BOLD and GBIF records, and also helps matching to the GBIF RDF project.

### Taxonomic identification

The identification is parsed using `taxon_name_parser.php`, if the parser fails then the name is regarded as “informal” (AKA “dark taxa” sensu Page).

The BOLD taxonomy ids are represented using a https://identifiers.org compact identifier.

### Location

I use geographical coordinates (latitude and longitude) if they are present and if they aren’t flagged as “Coordinates from country centroid” in the `coord` field in a BOLD record. For consistency with GBIF RDF, geographic points are also stored as `geo:wktLiterals` with the Wikidata predicate `wdt:P625` (http://www.wikidata.org/prop/direct/P625). The use of `geo:wktLiterals` means we can do spatial queries with GeoSPARQL.

GBIF RDF also creates arbitrary URIs for geographic location, and links them to the barcode using `dwciri:inDescribedPlace`. I have avoided doing this, in the hope in the future to perhaps use OpenStreetMap as the source of URIs for places. Note that OpenStreetMap is in QLever https://qlever.dev/api/osm-planet (Bast et al, 2021).

### Accession numbers

Some barcodes have GenBank accession numbers (`insdc_accs`). We store these using https://identifiers.org compact identifier scheme for the [nucleotide](https://registry.identifiers.org/registry/nucleotide) namespace (Bernal-Llinares et al., 2021). For example, `NC_021001` is https://identifiers.org/nucleotide:NC_021001. Originally I planned to use the `insdc` namespace but that does not include sequences such as whole mitochondrial genomes. Note that values in the `insdc_accs` are messy, and may include qualifiers such as `-SUPPRESSED`.

Note also that there are records where `inst` = `Mined from GenBank, NCBI` but `insdc_accs` is NULL. In these cases the accession number appears in the column `sampleid`. Yes, really.

### Datasets

A barcode may be part of one or more datasets (“recordsets” in BOLD terminology). Some of those datasets may have a DataCite DOI, and some of those DOIs may in turn be cited in the literature (for example, by the paper that published the dataset). The BOLD data dump lists the record sets that a barcode belongs to, but does not provide dataset DOIs, nor dataset citations. The file `recordsets.php` extracts distinct values for `bold_recordset_code_arr` from the parquet version of the datafile and outputs triples that state that each dataset is of type `https://schema.org/Dataset`. 

Note that we only do this for record sets with either the `DS-` or `DATASET-` prefix.

## Mapping datasets to citations

The project `bold-dataset-citations` maps BOLD datasets to DataCite DOIs (where available), and also has a manually created list of citations of BOLD datasets.

## Mapping accessions to citations

The projects https://github.com/rdmpage/genbank-pubmed-links and  https://github.com/rdmpage/genbank-publication-links map accessions to the literature, either to directly to PMIDs with associated DOIs via `sameAs`, or directly to DOIs.

### PubMed

`genbank-pubmed-links` matches accessions to PubMed IDs (PMID) using a list of accession numbers and PMIDs pairs downloaded from the NCBI. It then decorates those PMIDs with DOIs if the PubMed record includes a DOI. The link between PMID and accession is made using `https://schema.org/citation` and PMID and accession are represented by identifiers.org compact identifiers. If a work with a PMID also has a DOI, that link is represented by `https://schema.org/sameAs` and the DOI is represented using `https://doi.org/` and the DOI is in lower case.

### Accessions to literature

Many GenBank records have literature links that are not in PubMed. These records may have DOIs linking to the work that published the sequence, or they may have sufficient bibliographic information for the DOI to be discovered (either manually, or using webservices such as those provided by CrossRef). `genbank-publication-links` maps accession numbers to DOIs, and that mapping is expressed using `https://schema.org/citation`.


## Taxonomic names

The [BioNames](https://bionames.org) database maps animal taxonomic names to original publications. As a test case, rather than import the entire BioNames database I created two lists. The first is a list of all taxonomic names published in any of the DOIs from the accession to literature mapping, which capture new names published in papers that also published DNA barcodes. The second list was taxonomic names with publications with DOIs that also occurred in the BOLD database. This gives us the taxonomic literature relevant to BOLD taxa for which we have links to the literature. These two lists of names were merged, creating `bionames.tsv`, which was then converted into triples using `bionames.php`.


## Literature

Mapping accessions and taxonomic names to PMIDs and DOIs means we have links to literature, but no triples about those publications (beyond their links to sequences and/or names).

A separate project `csl-to-rdf` creates triples for bibliographic records, primarily those with DOIs.

## People

People are added as part of adding publications. Some will have ORCID ids.

To do: consider adding information on people who have identified and/or collected specimens.


## Organisations

Organisations are added as part of adding publications.

To do: consider adding information on funding organisations.

## GBIF

To do: consider adding `sameAs` links to the barcode records.

## Plazi

To do: consider adding links from publication DOIs to Plazi records in Zenodo.

## Make Data Count (MDC)

The Make Data Count project includes citations of GenBank accessions and DataCite DOIs, such as BOLD datasets.

To do: consider adding MDC citations.


## References

Baskauf, Steven J., et al. ‘Lessons Learned from Adapting the Darwin Core Vocabulary Standard for Use in RDF’. Semantic Web, edited by Pascal Hitzler and Krzysztof Janowicz, vol. 7, no. 6, Oct. 2016, pp. 617–27. DOI.org (Crossref), https://doi.org/10.3233/SW-150199.

Bast, Hannah, et al. ‘An Efficient RDF Converter and SPARQL Endpoint for the Complete OpenStreetMap Data’. Proceedings of the 29th International Conference on Advances in Geographic Information Systems [Beijing China], 2021, pp. 536–39. DOI.org (Crossref), https://doi.org/10.1145/3474717.3484256.

Bernal-Llinares, Manuel, et al. ‘Identifiers.Org: Compact Identifier Services in the Cloud’. Bioinformatics, edited by Lu Zhiyong, vol. 37, no. 12, July 2021, pp. 1781–82. DOI.org (Crossref), https://doi.org/10.1093/bioinformatics/btaa864.



