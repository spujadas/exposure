<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Controller;

use Exposure\Model\User,
    Exposure\Model\AdminUser,
    Sociable\Common\Configuration;

class SessionUtils extends \Sociable\Controller\SessionUtils {
    public static function setUserSessionParams(User $user) {
        if (!session_id()) { return; }

        unset($_SESSION['adminuser']); // auto-sign out admin

        $_SESSION['user'] = array();
        $_SESSION['user']['id'] = $user->getId();
        $_SESSION['user']['type'] = $user->getType();
        $_SESSION['user']['status'] = $user->getStatus();
        
        $_SESSION['language'] = $user->getLanguageCode();
        $_SESSION['currency'] = $user->getCurrencyCode();
    }

    public static function setAdminSessionParams(AdminUser $adminUser, 
        $languageCode, $currencyCode) {
        if (!session_id()) { return; }
        
        unset($_SESSION['user']); // auto-sign out user

        $_SESSION['adminuser'] = array();
        $_SESSION['adminuser']['id'] = $adminUser->getId();
        
        $_SESSION['language'] = $languageCode;
        $_SESSION['currency'] = $currencyCode;
    }

}


