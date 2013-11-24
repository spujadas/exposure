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

abstract class UserPreferences {
    protected $receiveNotificationsByEmail = true;
    
    public function getReceiveNotificationsByEmail() {
        return $this->receiveNotificationsByEmail;
    }

    public function setReceiveNotificationsByEmail($receiveNotificationsByEmail) {
        $this->receiveNotificationsByEmail = (bool) $receiveNotificationsByEmail;
        return $this->receiveNotificationsByEmail;
    }
}


