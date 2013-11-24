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

class SponsorContributionTypes {
    protected $financial = true;
    protected $service = true;
    protected $equipment = true;
    
    public function getFinancial() {
        return $this->financial;
    }

    public function setFinancial($financial) {
        $this->financial = (bool) $financial;
        return $this->financial;
    }

    public function getService() {
        return $this->service;
    }

    public function setService($service) {
        $this->service = (bool) $service;
        return $this->service;
    }

    public function getEquipment() {
        return $this->equipment;
    }

    public function setEquipment($equipment) {
        $this->equipment = (bool) $equipment;
        return $this->equipment;
    }
}


