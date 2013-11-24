<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

/*
 * Imports sublocations from TSV file
 * 
 * SYNTAX
 * php import_sublocations.php <file.tsv>
 * e.g.
 * php import_sublocations.php sublocations.tsv
 * 
 * NOTES
 * Make *absolutely sure* that the country file is UTF-8-encoded.
 * Lines containing a '#' are ignored
 * Line format is:
 * <parent location label>   <location label>  <name>  [<locations name>]
 * e.g.
 * fr-hauts-de-seine    chatenay-malabry    Châtenay-Malabry
 */

require_once 'bootstrap.php' ;
require_once (ROOT.'/sys/config/config.inc.php') ; // initialises $config

use Sociable\Model\Location;

if ($argc < 2) {
    echo 'Syntax: ' . $argv[0] . ' <sublocations_file>';
    return;
}

$file = $argv[1];
$lines = file($file);

$dm = $config->getDocumentManager() ;

foreach ($lines as $line) {
    if (preg_match('/#/', $line)) {
        continue;
    }
    $components = explode("\t", trim($line));
    $location = $dm->getRepository('Sociable\Model\Location')->findOneByLabel($components[0]) ;
    if (is_null($location)) {
        throw new Exception ('Location ' . $components[0] . ' not found in database') ;
    }
    $location->setSublocationsName('ville') ;
    $sublocation = new Location() ;
    $sublocation->setLabel($components[1]) ;
    $sublocation->setName($components[2]) ;
    $sublocation->setParentLocation($location) ;
    if (isset($components[3])) {
        $sublocation->setSublocationsName($components[3]) ;
    }
    $sublocation->validate();
    echo $components[1] . "\n" ;
    $dm->persist($sublocation);
}

$dm->flush();

?>