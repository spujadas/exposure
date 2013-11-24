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

use Sociable\Utility\NumberValidator;

class SubscriptionTypeAndDuration {
    protected $id = null;
    
    /** @var SubscriptionType */
    protected $type = null;
    const EXCEPTION_INVALID_TYPE = 'invalid type';

    protected $durationInMonths = null;
    const DURATION_MIN = 1;
    const EXCEPTION_INVALID_DURATION_IN_MONTHS = 'invalid duration in months';
    const EXCEPTION_TYPE_DURATION_MISMATCH = 'type duration mismatch';
    
    public function getId() {
        return $this->id;
    }
    
    public function getType() {
        return $this->type;
    }

    public function setType(SubscriptionType $type) {
        $this->type = $type;
        return $this->type;
    }

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
    
    protected function existsDurationForSubscriptionType($durationInMonths) {
        foreach ($this->type->getSubscriptionPrices() as $subscriptionPrice) {
            if ($subscriptionPrice->getDurationInMonths() == $this->durationInMonths) {
                return true;
            }
        }
        return false ;
    }

    public function getMonthlyPrice() {
        if (is_null($this->durationInMonths)) {
            return null ;
        }
        foreach ($this->type->getSubscriptionPrices() as $subscriptionPrice) {
            if ($subscriptionPrice->getDurationInMonths() == $this->durationInMonths) {
                return $subscriptionPrice->getMonthlyPrice() ;
            }
        }
        return null ;
    }

    public function validate() {
        if (!is_a($this->type, 'Exposure\Model\SubscriptionType')) {
            throw new SubscriptionTypeAndDurationException(self::EXCEPTION_INVALID_TYPE);
        }
        $this->validateDurationInMonths($this->durationInMonths);
        
        if (!$this->existsDurationForSubscriptionType($this->durationInMonths)) {
            throw new SubscriptionTypeAndDurationException(
                    SubscriptionTypeAndDuration::EXCEPTION_TYPE_DURATION_MISMATCH);
        }
        
    }
}

