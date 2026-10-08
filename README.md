# LD4BOLD - LInked data for BOLD

Linked data representation of DNA barcoding data.

## Goals

The goal is to have a RDF representation of BOLD DNA barcode data with a focus on connecting those barcodes to the research that generated and uses the barcodes. The term “barcode” in this context means a DNA sequence intended to be used to identify an organism, together with any relevant information about how and where that sequence was collected.

## Related work

There is a mapping between Barcode Core Data Model(BCDM) and Darwin Core at https://github.com/DNAdiversity/BCDM.

The pipeline GBIF use to import BOLD data is https://github.com/gbif/bold-dwca-pipeline

The pipleine Jerven Bolleman et al. use to convert GBIF occurrences to RDF is https://github.com/Micelio/gbif_parquet. That project makes extensive use of the Darwin Core IRI namespace http://rs.tdwg.org/dwc/iri/ described in Baskauf et al. (2016).

## BOLD data

The primary source for DNA barcodes is the BOLD dataset. The experiments reported here use the data dump BOLD_Public.06-Sep–2024.tar.gz, whcih is the same dataset used to create BOLD View https://boldview.iphylo.org.

## Barcode records

The core barcode record information is mapped to Darwin Core following a subset of the mapping used by GBIF. This means it is easy to compare BOLD and GBIF records, and also helps matching to the GBIF RDF project.

### Taxonomic identification



### Location

I use geographical coordinates (latitude and longitude) if they are present and if they aren’t flagged as “Coordinates from country centroid” in the `coord` field in a BOLD record. For consistency with GBIF RDF geographic points are also stored as `geo:wktLiterals` with Wikidata predicate `wdt:P625` (http://www.wikidata.org/prop/direct/P625). The use of `geo:wktLiterals` means we can do spatial queries with GeoSPARQL.

GBIF RDF also creates arbitrary URIs for geographic location, and links them to the barcode using `dwciri:inDescribedPlace`. I have avoided doing this, in the hope in the future to perhaps use OpenStreetMap as the source of URIs for places. Note that OpenStreetMap is in QLever https://qlever.dev/api/osm-planet (Bast et al, 2021).

### Accession numbers

Some barcodes have GenBank accession numbers (`insdc_accs`). We store these using https://identifiers.org compact identifier scheme for the [nucleotide](https://registry.identifiers.org/registry/nucleotide) namespace (Bernal-Llinares et al., 2021). For example, `NC_021001` is https://identifiers.org/nucleotide:NC_021001

### Datasets

A barcode may be part of one or more datasets (“recordsets” in BOLD terminology). Some of those datasets may have a DataCite DOI, and some of those DOIs may in turn be cited in the literature (for example, by the paper that published the dataset). The BOLD data dump lists the record sets that a barcode belongs to, but does not provide dataset DOIs, nor dataset citations. I have created a dataset with this information elsewhere.



## References

Baskauf, Steven J., et al. ‘Lessons Learned from Adapting the Darwin Core Vocabulary Standard for Use in RDF’. Semantic Web, edited by Pascal Hitzler and Krzysztof Janowicz, vol. 7, no. 6, Oct. 2016, pp. 617–27. DOI.org (Crossref), https://doi.org/10.3233/SW-150199.

Bast, Hannah, et al. ‘An Efficient RDF Converter and SPARQL Endpoint for the Complete OpenStreetMap Data’. Proceedings of the 29th International Conference on Advances in Geographic Information Systems [Beijing China], 2021, pp. 536–39. DOI.org (Crossref), https://doi.org/10.1145/3474717.3484256.

Bernal-Llinares, Manuel, et al. ‘Identifiers.Org: Compact Identifier Services in the Cloud’. Bioinformatics, edited by Lu Zhiyong, vol. 37, no. 12, July 2021, pp. 1781–82. DOI.org (Crossref), https://doi.org/10.1093/bioinformatics/btaa864.



