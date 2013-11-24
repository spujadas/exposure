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

class SponsorContribution {
    protected $id = null;
    
    /** @var SponsorOrganisation */
    protected $contributor = null;
    const EXCEPTION_INVALID_SPONSOR_ORGANISATION = 'invalid sponsor organisation';
    
    protected $status = null;
    const STATUS_PROPOSAL_SUBMITTED_BY_SPONSOR = 'proposal submitted by sponsor';
    const STATUS_PROPOSAL_APPROVED = 'proposal approved';
    const STATUS_SENT = 'sent';
    const STATUS_RECEIVED = 'received';
    const EXCEPTION_INVALID_STATUS = 'invalid status';

    /** @var FinancialNeedByAmount */
    protected $contributedFinancialNeedByAmount = null; // inverse side
    
    /** @var NonFinancialNeed */
    protected $contributedNonFinancialNeed = null; // inverse side
    
    public function getId() {
        return $this->id;
    }

    public function getContributor() {
        return $this->contributor;
    }

    public function setContributor(SponsorOrganisation $contributor) {
        $this->contributor = $contributor;
        return $this->contributor;
    }

    public function setStatus($status) {
        try {
            $this->validateStatus($status);
        }
        catch (Exception $e) {
            $this->status = null;
            throw $e;
        }
        $this->status = $status;
        return $this->status;

    }

    public function getStatus() {
        return $this->status;
    }
    
    protected function validateStatus($status) {
        if (!in_array($status, array(self::STATUS_PROPOSAL_APPROVED, 
                self::STATUS_PROPOSAL_SUBMITTED_BY_SPONSOR, 
                self::STATUS_RECEIVED,
                self::STATUS_SENT))) {
            throw new SponsorContributionException(self::EXCEPTION_INVALID_STATUS);
        }
    }

    public function getContributedNonFinancialNeed() {
        return $this->contributedNonFinancialNeed;
    }

    public function getContributedFinancialNeedByAmount() {
        return $this->contributedFinancialNeedByAmount;
    }

    public function getContributedNeed() {
        if (!is_null($this->contributedNonFinancialNeed)) {
            return $this->contributedNonFinancialNeed;
        }
        if (!is_null($this->contributedFinancialNeedByAmount)) {
            return $this->contributedFinancialNeedByAmount;
        }
        return null;
    }

    public function getProject() {
        if (is_null($need = $this->getContributedNeed())) {
            return null;
        }
        return $need->getProject();
    }
    
    public function validate() {
        if (!is_a($this->contributor, 'Exposure\Model\SponsorOrganisation')) {
            throw new SponsorContributionException(self::EXCEPTION_INVALID_SPONSOR_ORGANISATION);
        }
        $this->validateStatus($this->status);
    }
}


