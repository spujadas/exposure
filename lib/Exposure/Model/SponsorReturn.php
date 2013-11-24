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

use Sociable\Model\MultiLanguageString;

use Doctrine\Common\Collections\ArrayCollection;

class SponsorReturn {
    protected $id = null;

    /** @var MultiLanguageString */
    protected $description = null;
    const DESCRIPTION_MAX_LENGTH = 250;
    const EXCEPTION_INVALID_DESCRIPTION = 'missing description';
    
    /** @var SponsorReturnType */
    protected $type = null;
    const EXCEPTION_INVALID_TYPE = 'invalid type';
    
    protected $status = null;
    const STATUS_NOT_STARTED = 'not started';
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_COMPLETED_BY_PROJECT_OWNER = 'completed by project owner';
    const STATUS_APPROVED = 'approved';
    const EXCEPTION_INVALID_STATUS = 'invalid status';
    
    /** @var ArrayCollection */
    protected $comments; // inverse side
    
    /** @var NonFinancialNeed */
    protected $returnedNonFinancialNeed; // inverse side

    /** @var FinancialNeedByAmount */
    protected $returnedFinancialNeedByAmount; // inverse side
    
    public function __construct() {
        $this->comments = new ArrayCollection;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setDescription(MultiLanguageString $description) {
        try {
            $this->validateDescription($description);
        } catch (Exception $e) {
            $this->description = null;
            throw $e;
        }
        $this->description = $description;
        return $this->description;
        
    }
    
    protected function validateDescription(MultiLanguageString $description) {
        $description->validate(array(
            'not_empty' => true,
            'max_length' => self::DESCRIPTION_MAX_LENGTH));
    }

    public function getDescription() {
        return $this->description;
    }

    public function getType() {
        return $this->type;
    }

    public function setType(SponsorReturnType $type) {
        $this->type = $type;
        return $this->type;
    }

    public function getComments() {
        return $this->comments;
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
        if (($status != self::STATUS_NOT_STARTED) 
                && ($status != self::STATUS_APPROVED)
                && ($status != self::STATUS_COMPLETED_BY_PROJECT_OWNER)
                && ($status != self::STATUS_IN_PROGRESS)) {
            throw new SponsorReturnException(self::EXCEPTION_INVALID_STATUS);
        }
    }    
    
    public function validate() {
        if (!is_a($this->description, 'Sociable\Model\MultiLanguageString')) {
            throw new SponsorReturnException(self::EXCEPTION_INVALID_DESCRIPTION);
        }
        $this->validateDescription($this->description);
        $this->validateStatus($this->status);
        if (!is_a($this->type, 'Exposure\Model\SponsorReturnType')) {
            throw new SponsorReturnException(self::EXCEPTION_INVALID_TYPE);
        }
    }

    public function getNeed() {
        if (!is_null($this->returnedNonFinancialNeed)) {
            return $this->returnedNonFinancialNeed;
        }
        if (!is_null($this->returnedFinancialNeedByAmount)) {
            return $this->returnedFinancialNeedByAmount;
        }
        return null;
    }

    public function getReturnedNonFinancialNeed() {
        return $this->returnedNonFinancialNeed;
    }

    public function getReturnedFinancialNeedByAmount() {
        return $this->returnedFinancialNeedByAmount;
    }
}


