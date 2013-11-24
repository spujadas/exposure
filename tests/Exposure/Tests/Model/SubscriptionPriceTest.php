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

use Exposure\Model\SubscriptionPrice,
    Sociable\Utility\NumberValidator,
    Sociable\Model\MultiCurrencyValue;

class SubscriptionPriceTest extends \PHPUnit_Framework_TestCase {
    protected $subscriptionPrice;
    
    const DURATION_IN_MONTHS = 3;
    protected $monthlyPrice;
    
    public function setUp() {
        $this->subscriptionPrice = new SubscriptionPrice();
        $this->monthlyPrice = new MultiCurrencyValue(10, 'EUR');
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SubscriptionPrice', $this->subscriptionPrice);
    }

    public function testSetDurationInMonths_notanumber() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_A_NUMBER);
        $this->subscriptionPrice->setDurationInMonths(array());
    }
    
    public function testGetDurationInMonths_notanumber() {
        try {
            $this->subscriptionPrice->setDurationInMonths(array());
        }
        catch (\Exception $e) {}
    }
    
    public function testSetDurationInMonths_notaninteger() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_AN_INTEGER);
        $this->subscriptionPrice->setDurationInMonths(1.2);
    }
    
    public function testGetDurationInMonths_notaninteger() {
        try {
            $this->subscriptionPrice->setDurationInMonths(1.2);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionPrice->getDurationInMonths());
    }
    
    public function testSetDurationInMonths_toosmall() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_TOO_SMALL);
        $this->subscriptionPrice->setDurationInMonths(SubscriptionPrice::DURATION_MIN - 1);
    }
    
    public function testGetDurationInMonths_toosmall() {
        try {
            $this->subscriptionPrice->setDurationInMonths(SubscriptionPrice::DURATION_MIN - 1);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionPrice->getDurationInMonths());
    }
    
    public function testSetGetDurationInMonths() {
        $this->assertEquals(self::DURATION_IN_MONTHS, 
                $this->subscriptionPrice->setDurationInMonths(self::DURATION_IN_MONTHS));
        $this->assertEquals(self::DURATION_IN_MONTHS, 
                $this->subscriptionPrice->getDurationInMonths());
    }
    
    public function testSetGetMonthlyPrice() {
        $this->assertEquals($this->monthlyPrice, 
                $this->subscriptionPrice->setMonthlyPrice($this->monthlyPrice));
        $this->assertEquals($this->monthlyPrice, $this->subscriptionPrice->getMonthlyPrice());
    }

    public function testValidate_missingdurationinmonths() {
        $this->subscriptionPrice->setMonthlyPrice($this->monthlyPrice);
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_A_NUMBER);
        $this->subscriptionPrice->validate();
    }

    public function testValidate_missingmonthlyprice() {
        $this->subscriptionPrice->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->setExpectedException('Exposure\Model\SubscriptionPriceException', 
            SubscriptionPrice::EXCEPTION_INVALID_MONTHLY_PRICE);
        $this->subscriptionPrice->validate();
    }

    public function testValidate() {
        $this->subscriptionPrice->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->subscriptionPrice->setMonthlyPrice($this->monthlyPrice);
        $this->subscriptionPrice->validate();
    }
    
    public function tearDown() {
        unset($this->subscriptionPrice);
    }

}

?>
