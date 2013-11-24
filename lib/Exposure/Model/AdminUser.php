<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Model;

class AdminUser extends \Sociable\Model\User {
    /** @var AdminRights */
    protected $adminRights = null;
    const EXCEPTION_INVALID_ADMIN_RIGHTS = 'invalid admin rights';
    
    public function getAdminRights() {
        return $this->adminRights;
    }

    public function setAdminRights(AdminRights $adminRights) {
        $this->adminRights = $adminRights;
        return $this->adminRights;
    }

    public function validate() {
        parent::validate();
        if (!is_a($this->adminRights, 'Exposure\Model\AdminRights')) {
            throw new AdminUserException(self::EXCEPTION_INVALID_ADMIN_RIGHTS);
        }
    }
}


