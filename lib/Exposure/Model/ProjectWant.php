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

class ProjectWant extends Comment {
    protected $id = null;
    
    /** @var Project */
    protected $project = null;
    const EXCEPTION_INVALID_PROJECT = 'invalid project';

    /** @var SponsorOrganisation */
    protected $sponsorOrganisation = null;
    const EXCEPTION_INVALID_SPONSOR_ORGANISATION = 'invalid sponsor organisation';

    /** @var \DateTime */
    protected $dateTime = null;
    const EXCEPTION_INVALID_DATE_TIME = 'invalid date time';

    public function getProject() {
        return $this->project;
    }

    public function setProject(Project $project) {
        $this->project = $project;
        return $this->project;
    }

    public function getSponsorOrganisation() {
        return $this->sponsorOrganisation;
    }

    public function setSponsorOrganisation(SponsorOrganisation $sponsorOrganisation) {
        $this->sponsorOrganisation = $sponsorOrganisation;
        return $this->sponsorOrganisation;
    }

    public function getDateTime() {
        return $this->dateTime;
    }
            
    public function setDateTime(\DateTime $datetime) {
        $this->dateTime = $datetime;
        return $this->dateTime;
    }

    public function validate() {
        if (!is_a($this->project, 'Exposure\Model\Project')) {
            throw new ProjectWantException(self::EXCEPTION_INVALID_PROJECT);
        }
        if (!is_a($this->sponsorOrganisation, 'Exposure\Model\SponsorOrganisation')) {
            throw new ProjectWantException(self::EXCEPTION_INVALID_SPONSOR_ORGANISATION);
        }
        if (!is_a($this->dateTime, 'DateTime')) {
            throw new ProjectWantException(self::EXCEPTION_INVALID_DATE_TIME);
        }
    }
}


