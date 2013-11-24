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

use Exposure\Model\FinancialNeedByAmount,
    Exposure\Model\ProjectNeed,
    Exposure\Model\SponsorReturn,
    Exposure\Model\SponsorContribution,
    Sociable\Model\MultiCurrencyValue,
    Sociable\Model\MultiLanguageString;

class FinancialNeedByAmountTest extends \PHPUnit_Framework_TestCase {
    protected $financialNeedByAmount;
    
    const TYPE = ProjectNeed::TYPE_FINANCIAL;
    const TYPE_INVALID = 'rubbish';

    protected $description;
    protected $return;
    protected $amount;
    protected $contribution;
    
    public function setUp() {
        $this->financialNeedByAmount = new FinancialNeedByAmount;

        $this->description = new MultiLanguageString('foo', 'fr');
        $this->return = new SponsorReturn;
        $this->contribution = new SponsorContribution;
        $this->amount = new MultiCurrencyValue(10, 'EUR');
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\FinancialNeedByAmount', $this->financialNeedByAmount);
        $this->assertEquals(ProjectNeed::TYPE_FINANCIAL, $this->financialNeedByAmount->getType());
    }

    public function testSetType_null() {
        $this->setExpectedException('Exposure\Model\ProjectNeedException', 
            ProjectNeed::EXCEPTION_INVALID_TYPE);
        $this->financialNeedByAmount->setType(null);
    }
    
    public function testGetType_null() {
        try {
            $this->financialNeedByAmount->setType(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->financialNeedByAmount->getType());
    }
    
    public function testSetType_invalidtype() {
        $this->setExpectedException('Exposure\Model\ProjectNeedException', 
            ProjectNeed::EXCEPTION_INVALID_TYPE);
        $this->financialNeedByAmount->setType(self::TYPE_INVALID);
    }
    
    public function testGetType_invalidtype() {
        try {
            $this->financialNeedByAmount->setType(self::TYPE_INVALID);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->financialNeedByAmount->getType());
    }
    
    public function testSetGetType() {
        $this->assertEquals(self::TYPE, $this->financialNeedByAmount->setType(self::TYPE));
        $this->assertEquals(self::TYPE, $this->financialNeedByAmount->getType());
    }

    public function testSetGetReturn() {
        $this->assertEquals($this->return, 
                $this->financialNeedByAmount->setReturn($this->return));
        $this->assertEquals($this->return, $this->financialNeedByAmount->getReturn());
    }
    
    public function testSetGetAmount() {
        $this->assertEquals($this->amount, 
                $this->financialNeedByAmount->setAmount($this->amount));
        $this->assertEquals($this->amount, $this->financialNeedByAmount->getAmount());
    }

    public function testSetGetContribution() {
        $this->assertEquals($this->contribution, 
                $this->financialNeedByAmount->setContribution($this->contribution));
        $this->assertEquals($this->contribution, $this->financialNeedByAmount->getContribution());
        $this->assertNull($this->financialNeedByAmount->setContribution());
        $this->assertNull($this->financialNeedByAmount->getContribution());
    }
    
    public function testValidate_missingdescription() {
        $this->financialNeedByAmount->setAmount($this->amount);
        $this->financialNeedByAmount->setReturn($this->return);
        $this->setExpectedException('Exposure\Model\ProjectNeedException', 
            FinancialNeedByAmount::EXCEPTION_MISSING_DESCRIPTION);
        $this->financialNeedByAmount->validate();
    }
    
    public function testValidate_missingreturn() {
        $this->financialNeedByAmount->setDescription($this->description);
        $this->financialNeedByAmount->setAmount($this->amount);
        $this->setExpectedException('Exposure\Model\FinancialNeedByAmountException', 
            FinancialNeedByAmount::EXCEPTION_INVALID_SPONSOR_RETURN);
        $this->financialNeedByAmount->validate();
    }
    
    public function testValidate_missingamount() {
        $this->financialNeedByAmount->setDescription($this->description);
        $this->financialNeedByAmount->setReturn($this->return);
        $this->setExpectedException('Exposure\Model\FinancialNeedByAmountException', 
            FinancialNeedByAmount::EXCEPTION_INVALID_AMOUNT);
        $this->financialNeedByAmount->validate();
    }
    
    public function testValidate() {
        $this->financialNeedByAmount->setDescription($this->description);
        $this->financialNeedByAmount->setAmount($this->amount);
        $this->financialNeedByAmount->setReturn($this->return);
        $this->financialNeedByAmount->validate();
        $this->financialNeedByAmount->setContribution($this->contribution);
        $this->financialNeedByAmount->validate();
    }
    
    public function testIsFulfilled() {
        $this->assertFalse($this->financialNeedByAmount->isFulfilled());
        $this->financialNeedByAmount->setContribution($this->contribution);
        $this->assertTrue($this->financialNeedByAmount->isFulfilled());
    }

    public function tearDown() {
        unset($this->financialNeedByAmount);
    }

}

?>
