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
 * Imports sponsor return types from TSV file
 * 
 * SYNTAX
 * php import_sponsor_return_types.php <file.tsv>
 * e.g.
 * php import_sponsor_return_types.php sponsor_return_types.tsv
 * 
 * NOTES
 * Make *absolutely sure* that the file is UTF-8-encoded.
 * Lines containing a '#' are ignored
 * Line format is:
 * <label>   <French name>  <English name>
 * e.g.
 * offline	Hors ligne	Offline
 */

require_once 'bootstrap.php' ;
require_once (ROOT.'/sys/config/config.inc.php') ; // initialises $config

use Exposure\Model\SponsorReturnType,
    Sociable\Model\MultiLanguageString;

if ($argc < 2) {
    echo 'Syntax: ' . $argv[0] . ' <sponsor_return_type_file>';
    return;
}

$file = $argv[1];
$lines = file($file);

$dm = $config->getDocumentManager() ;

foreach ($lines as $line) {
    if (preg_match('/#/', $line)) {
        continue;
    }
    list($label, $description_fr, $description_en) = explode("\t", trim($line));
    $sponsorReturnType = new SponsorReturnType();
    $sponsorReturnType->setLabel($label);
    $description = new MultiLanguageString();
    $description->addStringByLanguageCode($description_fr, 'fr');
    $description->addStringByLanguageCode($description_en, 'en');
    $description->setDefaultLanguageCode('fr');
    $sponsorReturnType->setDescription($description);
    $sponsorReturnType->validate();
    echo "$label\n" ;
    $dm->persist($sponsorReturnType);
}

$dm->flush();

?>