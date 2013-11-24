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

use Exposure\Model\SubscriptionTypeAndDuration,
    Exposure\Model\SubscriptionType,
    Exposure\Model\SubscriptionPrice,
    Sociable\Utility\NumberValidator;

class SubscriptionTypeAndDurationTest extends \PHPUnit_Framework_TestCase {
    protected $subscriptionTypeAndDuration;
    protected $subscriptionType;
    const DURATION_IN_MONTHS = 3;
    
    public function setUp() {
        $this->subscriptionTypeAndDuration = new SubscriptionTypeAndDuration();
        $this->subscriptionType = new SubscriptionType;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SubscriptionTypeAndDuration', $this->subscriptionTypeAndDuration);
    }

    public function testSetGetType() {
        $this->assertEquals($this->subscriptionType, 
                $this->subscriptionTypeAndDuration->setType($this->subscriptionType));
        $this->assertEquals($this->subscriptionType, 
                $this->subscriptionTypeAndDuration->getType());
    }
    
    public function testSetDurationInMonths_notanumber() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_A_NUMBER);
        $this->subscriptionTypeAndDuration->setDurationInMonths(array());
    }
    
    public function testGetDurationInMonths_notanumber() {
        try {
            $this->subscriptionTypeAndDuration->setDurationInMonths(array());
        }
        catch (\Exception $e) {}
    }
    
    public function testSetDurationInMonths_notaninteger() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_AN_INTEGER);
        $this->subscriptionTypeAndDuration->setDurationInMonths(1.2);
    }
    
    public function testGetDurationInMonths_notaninteger() {
        try {
            $this->subscriptionTypeAndDuration->setDurationInMonths(1.2);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionTypeAndDuration->getDurationInMonths());
    }
    
    public function testSetDurationInMonths_toosmall() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_TOO_SMALL);
        $this->subscriptionTypeAndDuration->setDurationInMonths(SubscriptionTypeAndDuration::DURATION_MIN - 1);
    }
    
    public function testGetDurationInMonths_toosmall() {
        try {
            $this->subscriptionTypeAndDuration->setDurationInMonths(SubscriptionTypeAndDuration::DURATION_MIN - 1);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionTypeAndDuration->getDurationInMonths());
    }
    
    public function testSetGetDurationInMonths() {
        $this->assertEquals(self::DURATION_IN_MONTHS, 
                $this->subscriptionTypeAndDuration->setDurationInMonths(self::DURATION_IN_MONTHS));
        $this->assertEquals(self::DURATION_IN_MONTHS, 
                $this->subscriptionTypeAndDuration->getDurationInMonths());
    }
    
    public function testValidate_missingtype() {
        $this->subscriptionTypeAndDuration->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->setExpectedException('Exposure\Model\SubscriptionTypeAndDurationException', 
            SubscriptionTypeAndDuration::EXCEPTION_INVALID_TYPE);
        $this->subscriptionTypeAndDuration->validate();
    }
    
    public function testValidate_missingdurationinmonths() {
        $this->subscriptionTypeAndDuration->setType($this->subscriptionType);
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_A_NUMBER);
        $this->subscriptionTypeAndDuration->validate();
    }
    
    public function testValidate_typedurationmismatch() {
        $this->subscriptionTypeAndDuration->setType($this->subscriptionType);
        $this->subscriptionTypeAndDuration->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->setExpectedException('Exposure\Model\SubscriptionTypeAndDurationException', 
            SubscriptionTypeAndDuration::EXCEPTION_TYPE_DURATION_MISMATCH);
        $this->subscriptionTypeAndDuration->validate();
    }
    
    public function testValidate() {
        $subscriptionPrice = new SubscriptionPrice;
        $subscriptionPrice->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->subscriptionType->addSubscriptionPrice($subscriptionPrice);
        $this->subscriptionTypeAndDuration->setType($this->subscriptionType);
        $this->subscriptionTypeAndDuration->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->subscriptionTypeAndDuration->validate();
    }
    
    public function tearDown() {
        unset($this->subscriptionTypeAndDuration);
    }

}

?>
