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
 * Generates admin singleton
 * 
 * SYNTAX
 * php init_administration.php <label>
 * e.g.
 * php init_administration.php ADMIN
 */

require_once 'bootstrap.php' ;
require_once (ROOT.'/sys/config/config.inc.php') ; // initialises $config

use Exposure\Model\Administration;

if ($argc < 2) {
    echo 'Syntax: ' . $argv[0] . ' <label>';
    return;
}

$dm = $config->getDocumentManager() ;
$admin = new Administration ;
$admin->setLabel($argv[1]) ;
$dm->persist($admin);
$dm->flush();

?>