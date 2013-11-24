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

use Sociable\Model\ConfirmationCode;

class Subscription {
    protected $id = null;
    
    protected $status = null;
    const STATUS_PENDING_PAYMENT = 'pending payment';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const EXCEPTION_INVALID_STATUS = 'invalid status';

    /** @var ConfirmationCode */
    protected $paymentConfirmationCode = null;
    const EXCEPTION_INVALID_PAYMENT_CONFIRMATION_CODE = 'invalid payment confirmation code';
    
    /** @var \DateTime */
    protected $startDateTime = null;
    const EXCEPTION_INVALID_START_DATE_TIME = 'invalid start date time';
    
    /** @var \DateTime */
    protected $endDateTime = null;
    const EXCEPTION_INVALID_END_DATE_TIME = 'invalid end date time';

    /** @var SubscriptionTypeAndDuration */
    protected $typeAndDuration = null;
    const EXCEPTION_INVALID_TYPE_AND_DURATION = 'invalid type and duration';
    
    public function getId() {
        return $this->id;
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

    public function startSubscription() {
        if (is_null($this->typeAndDuration)) {
            return false;
        }
        $this->startDateTime = new \DateTime;
        $this->endDateTime = (new \DateTime)
            ->modify('+'. $this->typeAndDuration->getDurationInMonths() .' month');
        return true;
    }

    public function getStatus() {
        return $this->status;
    }
    
    protected function validateStatus($status) {
        if (!in_array($status, array(self::STATUS_ACTIVE,
                self::STATUS_INACTIVE,
                self::STATUS_PENDING_PAYMENT))) {
            throw new SubscriptionException(self::EXCEPTION_INVALID_STATUS);
        }
    }
    
    public function getPaymentConfirmationCode() {
        return $this->paymentConfirmationCode;
    }

    public function setPaymentConfirmationCode(ConfirmationCode $paymentConfirmationCode = null) {
        $this->paymentConfirmationCode = $paymentConfirmationCode;
        return $this->paymentConfirmationCode;
    }
    
    public function getStartDateTime() {
        return $this->startDateTime;
    }
            
    public function setStartDateTime(\DateTime $datetime) {
        $this->startDateTime = $datetime;
        return $this->startDateTime;
    }
    
    public function getEndDateTime() {
        return $this->endDateTime;
    }
            
    public function setEndDateTime(\DateTime $datetime) {
        $this->endDateTime = $datetime;
        return $this->endDateTime;
    }
    
    public function getTypeAndDuration() {
        return $this->typeAndDuration;
    }

    public function setTypeAndDuration(SubscriptionTypeAndDuration $typeAndDuration) {
        $this->typeAndDuration = $typeAndDuration;
        return $this->typeAndDuration;
    }

    public function validate() {
        $this->validateStatus($this->status);
        if (!is_null($this->paymentConfirmationCode) 
                && !is_a($this->paymentConfirmationCode, 'Sociable\Model\ConfirmationCode')) {
            throw new SubscriptionException(self::EXCEPTION_INVALID_PAYMENT_CONFIRMATION_CODE);
        }
        if (!is_a($this->startDateTime, 'DateTime')) {
            throw new SubscriptionException(self::EXCEPTION_INVALID_START_DATE_TIME);
        }
        if (!is_a($this->endDateTime, 'DateTime')) {
            throw new SubscriptionException(self::EXCEPTION_INVALID_END_DATE_TIME);
        }
        if (!is_a($this->typeAndDuration, 'Exposure\Model\SubscriptionTypeAndDuration')) {
            throw new SubscriptionException(self::EXCEPTION_INVALID_TYPE_AND_DURATION);
        }
    }
    
}


