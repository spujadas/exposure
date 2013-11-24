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

use Sociable\Model\MultiCurrencyValue;

use Doctrine\Common\Collections\ArrayCollection;

class FinancialNeed {
    protected $id = null;    

    /** @var MultiCurrencyValue */
    protected $totalAmount;
    const EXCEPTION_MISSING_TOTAL_AMOUNT = 'missing total amount';
    
    /** @var ArrayCollection of FinancialNeedByAmount */
    protected $needsByAmount;
    const EXCEPTION_INVALID_NEED_BY_AMOUNT = 'invalid need by amount';

    /** @var Project */
    protected $project;
    const EXCEPTION_MISSING_PROJECT = 'missing project';

    public function __construct() {
        $this->needsByAmount = new ArrayCollection;
    }

    public function getId() {
        return $this->id;
    }
    
    public function isFulfilled() {
        if ($this->needsByAmount->count() == 0) { return false; }
        foreach ($this->needsByAmount as $needByAmount) {
            if (!$needByAmount->isFulfilled()) {
                return false;
            }
        }
        return true;
    }

    public function setTotalAmount(MultiCurrencyValue $totalAmount) {
        try {
            $this->validateTotalAmount($totalAmount);
        }
        catch (Exception $e) {
            $this->totalAmount = null;
            throw $e;
        }
        $this->totalAmount = $totalAmount;
        return $this->totalAmount;
    }
    
    public function getTotalAmount() {
        return $this->totalAmount;
    }
    
    protected function validateTotalAmount(MultiCurrencyValue $totalAmount) {
        $totalAmount->validate();
    }

    public function getNeedsByAmount() {
        return $this->needsByAmount;
    }
        
    public function addFinancialNeedByAmount(FinancialNeedByAmount $financialNeedByAmount) {    
        $this->needsByAmount[] = $financialNeedByAmount;
    }

    public function removeFinancialNeedByAmount(FinancialNeedByAmount $financialNeedByAmount) {
        return $this->needsByAmount->removeElement($financialNeedByAmount);
    }
    
    public function compareTotalWithSumOfParts() {
        return $this->totalAmount->compareValues($this->sumOfParts());
    }
    
    public function sumOfParts() {
        $sumOfParts = new MultiCurrencyValue();
        foreach ($this->needsByAmount as $needByAmount) {
            $sumOfParts->sum($needByAmount->getAmount());
        }
        return $sumOfParts;
    }
    
    public function getProject() {
        return $this->project;
    }

    public function setProject(Project $project) {
        $this->project = $project;
        return $this->project;
    }
    
    public function validate() {
        if (is_null($this->totalAmount)) {
            throw new FinancialNeedException(self::EXCEPTION_MISSING_TOTAL_AMOUNT);
        }
        foreach ($this->needsByAmount as $needByAmount) {
            if (!is_a($needByAmount, 'Exposure\Model\FinancialNeedByAmount')) {
                throw new FinancialNeedException(self::EXCEPTION_INVALID_NEED_BY_AMOUNT);
            }
        }
        if (is_null($this->project)) {
            throw new FinancialNeedException(self::EXCEPTION_MISSING_PROJECT);
        }
        $this->validateTotalAmount($this->totalAmount);
    }

    public function isContributedToByOrganisation(SponsorOrganisation $organisation) {
        foreach ($this->needsByAmount as $needByAmount) {
            if ($needByAmount->isFulfilledByOrganisation($organisation)) {
                return true;
            }
        }
        return false;
    }

    public function getContributions() {
        $contributions = new ArrayCollection;
        foreach ($this->needsByAmount as $needByAmount) {
            if (!is_null($contribution = $needByAmount->getContribution())) {
                $contributions->add($contribution);
            }
        }
        return $contributions;
    }

}


