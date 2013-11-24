<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Tests\Model;

use Exposure\Model\Subscription,
    Sociable\Model\ConfirmationCode,
    Exposure\Model\SubscriptionTypeAndDuration,
    Exposure\Model\User;

class SubscriptionTest extends \PHPUnit_Framework_TestCase {
    protected $subscription;
    
    const STATUS = Subscription::STATUS_ACTIVE;

    protected $paymentConfirmationCode;
    protected $startDateTime;
    protected $endDateTime;
    protected $typeAndDuration;
    
    public function setUp() {
        $this->subscription = new Subscription;
        $this->paymentConfirmationCode = new ConfirmationCode;
        $this->startDateTime = new \DateTime;
        $this->endDateTime = new \DateTime;
        $this->typeAndDuration = new SubscriptionTypeAndDuration;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\Subscription', $this->subscription);
    }
    
    public function testSetStatus_invalidstatus() {
        $this->setExpectedException('Exposure\Model\SubscriptionException', 
            Subscription::EXCEPTION_INVALID_STATUS);
        $this->subscription->setStatus(null);
    }
    
    public function testGetStatus_invalidstatus() {
        try {
            $this->subscription->setStatus(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscription->getStatus());
    }
    
    public function testSetGetStatus() {
        $this->assertEquals(self::STATUS, $this->subscription->setStatus(self::STATUS));
        $this->assertEquals(self::STATUS, $this->subscription->getStatus());
    }

    public function testSetGetPaymentConfirmationCode() {
        $this->assertEquals($this->paymentConfirmationCode, 
                $this->subscription->setPaymentConfirmationCode($this->paymentConfirmationCode));
        $this->assertEquals($this->paymentConfirmationCode, 
                $this->subscription->getPaymentConfirmationCode());
    }
    
    public function testSetGetStartDateTime() {
        $this->assertEquals($this->startDateTime, 
                $this->subscription->setStartDateTime($this->startDateTime));
        $this->assertEquals($this->startDateTime, $this->subscription->getStartDateTime());
    }

    public function testSetGetEndDateTime() {
        $this->assertEquals($this->endDateTime, 
                $this->subscription->setEndDateTime($this->endDateTime));
        $this->assertEquals($this->endDateTime, $this->subscription->getEndDateTime());
    }

    public function testSetGetTypeAndDuration() {
        $this->assertEquals($this->typeAndDuration, 
                $this->subscription->setTypeAndDuration($this->typeAndDuration));
        $this->assertEquals($this->typeAndDuration, $this->subscription->getTypeAndDuration());
    }

    public function testValidate_missingstatus() {
        $this->subscription->setStartDateTime($this->startDateTime);
        $this->subscription->setEndDateTime($this->endDateTime);
        $this->subscription->setTypeAndDuration($this->typeAndDuration);
        $this->setExpectedException('Exposure\Model\SubscriptionException', 
            Subscription::EXCEPTION_INVALID_STATUS);
        $this->subscription->validate();
    }
    
    public function testValidate_missingstartdatetime() {
        $this->subscription->setStatus(self::STATUS);
        $this->subscription->setEndDateTime($this->endDateTime);
        $this->subscription->setTypeAndDuration($this->typeAndDuration);
        $this->setExpectedException('Exposure\Model\SubscriptionException', 
            Subscription::EXCEPTION_INVALID_START_DATE_TIME);
        $this->subscription->validate();
    }
    
    public function testValidate_missingenddatetime() {
        $this->subscription->setStatus(self::STATUS);
        $this->subscription->setStartDateTime($this->startDateTime);
        $this->subscription->setTypeAndDuration($this->typeAndDuration);
        $this->setExpectedException('Exposure\Model\SubscriptionException', 
            Subscription::EXCEPTION_INVALID_END_DATE_TIME);
        $this->subscription->validate();
    }
    
    public function testValidate_missingsubscriptiontypeandduration() {
        $this->subscription->setStatus(self::STATUS);
        $this->subscription->setStartDateTime($this->startDateTime);
        $this->subscription->setEndDateTime($this->endDateTime);
        $this->setExpectedException('Exposure\Model\SubscriptionException', 
            Subscription::EXCEPTION_INVALID_TYPE_AND_DURATION);
        $this->subscription->validate();
    }
    
    public function testValidate() {
        $this->subscription->setStatus(self::STATUS);
        $this->subscription->setStartDateTime($this->startDateTime);
        $this->subscription->setEndDateTime($this->endDateTime);
        $this->subscription->setTypeAndDuration($this->typeAndDuration);
        $this->subscription->validate();
        $this->subscription->setPaymentConfirmationCode($this->paymentConfirmationCode);
        $this->subscription->validate();
    }
    
    public function tearDown() {
        unset($this->subscription);
    }

}

?>
