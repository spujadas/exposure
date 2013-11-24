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
 * Creates superadmin
 * 
 * SYNTAX
 * php new_superadmin <email> <password>
 * e.g.
 * php new_superadmin spujadas@gmail.com 123456789
 */

require_once 'bootstrap.php' ;

use Exposure\Model\AdminUser,
	Exposure\Model\AdminRights,
	Sociable\Model\PasswordAuthenticator,
    Sociable\Common\Configuration;

if ($argc < 3) {
    echo 'Syntax: ' . $argv[0] . ' <email> <password>';
    return;
}

$config = Configuration::getDefaultConfiguration() ;
$dm = $config->getDocumentManager() ;
$adminUser = new AdminUser ;
$adminUser->setEmail($argv[1]) ;

$authenticator = new PasswordAuthenticator() ;
$authenticator->setParams(array('password' => $argv[2])) ;       

$adminRights = new AdminRights() ;
$adminRights->setCrud(true) ;
$adminRights->setApprove(true) ;
$adminRights->setAdmin(true) ;

$adminUser->setAuthenticator($authenticator);
$adminUser->setAdminRights($adminRights);
$adminUser->validate() ;

$dm->persist($adminUser);
$dm->flush();

?>