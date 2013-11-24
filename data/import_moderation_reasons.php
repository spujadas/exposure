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
 * Imports moderation reasons from TSV file
 * 
 * SYNTAX
 * php import_moderation_reasons.php <file.tsv>
 * e.g.
 * php import_moderation_reasons.php moderation_reasons.tsv
 * 
 * NOTES
 * Make *absolutely sure* that the file is UTF-8-encoded.
 * Lines containing a '#' are ignored
 * Line format is:
 * <code>   <French name>  <English name>
 * e.g.
 * other	autre	other
 */

require_once 'bootstrap.php' ;
require_once (ROOT.'/sys/config/config.inc.php') ; // initialises $config

use Exposure\Model\ModerationReason,
    Sociable\Model\MultiLanguageString;

if ($argc < 2) {
    echo 'Syntax: ' . $argv[0] . ' <moderation_reason_file>';
    return;
}

$file = $argv[1];
$lines = file($file);

$dm = $config->getDocumentManager() ;

foreach ($lines as $line) {
    if (preg_match('/#/', $line)) {
        continue;
    }
    list($code, $content_fr, $content_en) = explode("\t", trim($line));
    $moderationReason = new ModerationReason();
    $moderationReason->setCode($code);
    $content = new MultiLanguageString();
    $content->addStringByLanguageCode($content_en, 'en');
    $content->addStringByLanguageCode($content_fr, 'fr');
    $content->setDefaultLanguageCode('fr');
    $moderationReason->setContent($content);
    $moderationReason->validate();
    echo "$code\n" ;
    $dm->persist($moderationReason);
}

$dm->flush();

?>