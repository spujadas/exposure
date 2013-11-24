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

class ViewRights {
    protected $canSeeSponsors = false;
    
    public function getCanSeeSponsors() {
        return $this->canSeeSponsors;
    }

    public function setCanSeeSponsors($canSeeSponsors) {
        $this->canSeeSponsors = (bool) $canSeeSponsors;
        return $this->canSeeSponsors;
    }
}


