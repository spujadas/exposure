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

class AdminRights {
    protected $crud = false;
    protected $approve = false;
    protected $admin = false;
    
    public function getCrud() {
        return $this->crud;
    }

    public function setCrud($crud) {
        $this->crud = (bool) $crud;
        return $this->crud;
    }

    public function getApprove() {
        return $this->approve;
    }

    public function setApprove($approve) {
        $this->approve = (bool) $approve;
        return $this->approve;
    }

    public function getAdmin() {
        return $this->admin;
    }

    public function setAdmin($admin) {
        $this->admin = (bool) $admin;
        return $this->admin;
    }
}


