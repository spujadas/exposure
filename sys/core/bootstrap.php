<?php

/*
 * This file is part of the Sociable package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

if (!file_exists($file = __DIR__.'/../../vendor/autoload.php')) {
    throw new RuntimeException('Install dependencies before use.');
}

define ('EXPOSURE_ROOT', __DIR__.'/../..');

$loader = require_once $file;
