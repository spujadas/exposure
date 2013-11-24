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

use Sociable\Model\MultiCurrencyValue,
    Sociable\Utility\NumberValidator;

class SubscriptionPrice {
    protected $durationInMonths = null;
    const DURATION_MIN = 1;
    
    /** @var MultiCurrencyValue */
    protected $monthlyPrice = null;
    const EXCEPTION_INVALID_MONTHLY_PRICE = 'invalid monthly price';
    
    public function getDurationInMonths() {
        return $this->durationInMonths;
    }

    public function setDurationInMonths($durationInMonths) {
        try {
            $this->validateDurationInMonths($durationInMonths);
        }
        catch (Exception $e) {
            $this->durationInMonths = null;
            throw $e;
        }
        $this->durationInMonths = $durationInMonths;
        return $this->durationInMonths;
    }

    protected function validateDurationInMonths($durationInMonths) {
        NumberValidator::validate($durationInMonths, 
                array('int' => true, 'min' => self::DURATION_MIN));
    }

    public function getMonthlyPrice() {
        return $this->monthlyPrice;
    }

    public function setMonthlyPrice(MultiCurrencyValue $monthlyPrice) {
        $this->monthlyPrice = $monthlyPrice;
        return $this->monthlyPrice;
    }

    protected function validateMonthlyPrice(MultiCurrencyValue $monthlyPrice) {
        $monthlyPrice->validate();
    }
    
    public function validate() {
        $this->validateDurationInMonths($this->durationInMonths);
        if (!is_a($this->monthlyPrice, 'Sociable\Model\MultiCurrencyValue')) {
            throw new SubscriptionPriceException(self::EXCEPTION_INVALID_MONTHLY_PRICE);
        }
        $this->validateMonthlyPrice($this->monthlyPrice);
    }
}

