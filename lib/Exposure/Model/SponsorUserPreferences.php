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

class SponsorUserPreferences extends UserPreferences {
    protected $receiveDailyDigestByEmail = true;
    protected $receivePeriodicDigestByEmail = false;
    
    public function getReceiveDailyDigestByEmail() {
        return $this->receiveDailyDigestByEmail;
    }

    public function setReceiveDailyDigestByEmail($receiveDailyDigestByEmail) {
        $this->receiveDailyDigestByEmail = (bool) $receiveDailyDigestByEmail;
        return $this->receiveDailyDigestByEmail;
    }
    
    public function getReceivePeriodicDigestByEmail() {
        return $this->receivePeriodicDigestByEmail;
    }

    public function setReceivePeriodicDigestByEmail($receivePeriodicDigestByEmail) {
        $this->receivePeriodicDigestByEmail = (bool) $receivePeriodicDigestByEmail;
        return $this->receivePeriodicDigestByEmail;
    }
}


