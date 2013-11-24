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
 * Imports themes/subthemes from TSV file
 * 
 * SYNTAX
 * php import_themes.php <file.tsv>
 * e.g.
 * php import_themes.php themes.tsv
 * 
 * NOTES
 * Make *absolutely sure* that the country file is UTF-8-encoded.
 * Lines containing a '#' are ignored
 * Line format is:
 * <theme label>   <name FR> <name EN>  [<parent theme label>]
 * e.g.
 * wedding    mariage    wedding    private-event
 */

require_once 'bootstrap.php' ;
require_once (ROOT.'/sys/config/config.inc.php') ; // initialises $config

use Exposure\Model\Theme,
    Sociable\Model\MultiLanguageString;

if ($argc < 2) {
    echo 'Syntax: ' . $argv[0] . ' <themes_file>';
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
    $theme = new Theme() ;
    $theme->setLabel($components[0]) ;
    $name = new MultiLanguageString();
    $name->addStringByLanguageCode($components[1], 'fr');
    $name->addStringByLanguageCode($components[2], 'en');
    $name->setDefaultLanguageCode('fr');
    $theme->setName($name) ;
    if (isset($components[3])) {
        $parentTheme = $dm->getRepository('Exposure\Model\Theme')->findOneByLabel($components[3]) ;
        if (is_null($parentTheme)) {
            throw new Exception ('Theme ' . $components[3] . ' not found in database') ;
        }
        $theme->setParentTheme($parentTheme) ;
    }
    $theme->validate();
    echo $components[0] . "\n" ;
    $dm->persist($theme);
}

$dm->flush();

?>