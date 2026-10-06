<?php
/*
MIT License

Copyright (c) 2018-2026 Geonovum, The Netherlands

Configuration for the technisch register web pages.

The register configuration (repositories, clusters and artefact types) lives
in this repository under config/ and is read from the local filesystem.
Content updates arrive as git commits (see .github/workflows/) and are
deployed to the webserver with rsync, so no remote fetches are needed at
request time.
*/

// Local JSON configuration files
$configDir = __DIR__ . '/config';
$descriptionsURL = $configDir . '/descriptions.json';
$reposURL = $configDir . '/repos.json';
$clustersURL = $configDir . '/cluster.json';

// Base URL of the register, needed for creation of some links.
// A root-relative URL works for both production and the test environment.
$baseURL = '/';
?>
