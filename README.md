
**The concludis-api-client is a library for local mirroring your jobs advertisements managed with concludis ATS (https://concludis.com).**

This software provides an API Client for the concludis ATS for syncing Job advertisements from your powerful 
applicant tracking system concludis into a local database, which can thus be queried and filtered with high performance.

Multiple ATS instances can be connected and the job postings are normalized into a universal format when imported.
This makes it possible to create job portals that combine the jobs of several ATS instances.

The library also offers the option of implementing your own API client for other ATS systems or job advertisement 
sources and transferring them to the normalized structure.

## Features
- Internationalization
- Geodata for 86 Countries ()

## Requirements
- PHP-Environment >= PHP 8.1
- MariaDB DBMS 

## Installation

- add this library to your project via composer
- create a new database in your dbms
- copy config/config.sample.php to config/config.php
  - modify your database credentials 
  - modify your ATS source credentials and URL
- Execute cli/install.php to initialize the magic
  - this will create all the required caching tables in your database. Please be patient, the library contains geodata for 86 countries, which will be initially imported in this step.
- Execute cli/pull-projects.php to sync all the job advertisements from your ATS

