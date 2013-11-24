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

class FinancialNeedByAmount extends ProjectNeed {
    const EXCEPTION_RETURN_ALREADY_ASSIGNED_TO_NON_FINANCIAL_NEED
        = 'return already assigned to non-financial need';
    
    /** @var MultiCurrencyValue */
    protected $amount = null;
    const EXCEPTION_INVALID_AMOUNT = 'invalid amount';
    
    const EXCEPTION_CONTRIBUTION_ALREADY_ASSIGNED_TO_NON_FINANCIAL_NEED
        = 'contribution already assigned to non-financial need';
    
    /** @var FinancialNeed */
    protected $contributedTotal = null; // inverse side

    public function __construct() {
        $this->type = parent::TYPE_FINANCIAL;
    }

    protected function validateType($type) {
        if ($type != ProjectNeed::TYPE_FINANCIAL) {
            throw new ProjectNeedException(parent::EXCEPTION_INVALID_TYPE);
        }
    }

    public function setReturn(SponsorReturn $return) {
        if (!is_null($return->getReturnedNonFinancialNeed())) {
            $this->return = null;
            throw new FinancialNeedByAmountException(self::EXCEPTION_RETURN_ALREADY_ASSIGNED_TO_NON_FINANCIAL_NEED);
        }
        $this->return = $return;
        return $this->return;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount(MultiCurrencyValue $amount) {
        $this->amount = $amount;
        return $this->amount;
    }
    
    protected function validateAmount(MultiCurrencyValue $amount) {
        $amount->validate();
    }

    public function setContribution(SponsorContribution $contribution = null) {
        if (!is_null($contribution) && !is_null($contribution->getContributedNonFinancialNeed())) {
            $this->contribution = null;
            throw new FinancialNeedByAmountException(self::EXCEPTION_CONTRIBUTION_ALREADY_ASSIGNED_TO_NON_FINANCIAL_NEED);
        }
        $this->contribution = $contribution;
        return $this->contribution;
    }
    
    public function getContributedTotal() {
        return $this->contributedTotal;
    }
    
    public function getProject() {
        if (is_null($this->contributedTotal)) { return null; }
        return $this->contributedTotal->getProject();
    }
    
    public function validate() {
        parent::validate();
        if (!is_a($this->return, 'Exposure\Model\SponsorReturn')) {
            throw new FinancialNeedByAmountException(self::EXCEPTION_INVALID_SPONSOR_RETURN);
        }
        if (!is_null($this->contribution) && !is_a($this->contribution, 'Exposure\Model\SponsorContribution')) {
            throw new FinancialNeedByAmountException(self::EXCEPTION_INVALID_SPONSOR_CONTRIBUTION);
        }
        if (!is_a($this->amount, 'Sociable\Model\MultiCurrencyValue')) {
            throw new FinancialNeedByAmountException(self::EXCEPTION_INVALID_AMOUNT);
        }
        $this->validateAmount($this->amount);
    }
}


