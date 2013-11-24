<?php

/*
 * This file is part of the Sociable package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

/*
 * Ensures index creation by Doctrine ODM
 * 
 * SYNTAX
 * php ensure_indexes.php
 */

require_once 'bootstrap.php' ;
require_once (ROOT.'/sys/config/config-test.inc.php') ; // initialises $config

$dm = $config->getDocumentManager() ;
$dm->getSchemaManager()->deleteIndexes();
$dm->getSchemaManager()->ensureIndexes();

?>
