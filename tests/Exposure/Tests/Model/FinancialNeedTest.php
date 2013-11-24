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

use Exposure\Model\FinancialNeed,
    Exposure\Model\ProjectNeed,
    Exposure\Model\FinancialNeedByAmount,
    Exposure\Model\SponsorContribution,
    Exposure\Model\Project,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\MultiCurrencyValue;

class FinancialNeedTest extends \PHPUnit_Framework_TestCase {
    protected $financialNeed;
    protected $financialNeedByAmount;
    protected $contribution;
    protected $project;
    protected $totalAmount;
    
    public function setUp() {
        $this->financialNeed = new FinancialNeed();
        $this->description = new MultiLanguageString('description', 'fr');
        $this->totalAmount = new MultiCurrencyValue(10, 'EUR');
        $this->financialNeedByAmount = new FinancialNeedByAmount();

        $this->contribution = new SponsorContribution;
        $this->project = new Project;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\FinancialNeed', $this->financialNeed);
        $this->assertEquals(0, $this->financialNeed->getNeedsByAmount()->count());
    }
    
    
    public function testSetGetTotalAmount() {
        $this->assertEquals($this->totalAmount, 
                $this->financialNeed->setTotalAmount($this->totalAmount));
        $this->assertEquals($this->totalAmount, 
                $this->financialNeed->getTotalAmount());
    }

    public function testSetGetProject() {
        $this->assertEquals($this->project, 
                $this->financialNeed->setProject($this->project));
        $this->assertEquals($this->project, 
                $this->financialNeed->getProject());
    }

    public function testAddRemoveFinancialNeedByAmount() {
        $this->financialNeed->addFinancialNeedByAmount($this->financialNeedByAmount);
        $this->assertEquals(1, $this->financialNeed->getNeedsByAmount()->count());
        $this->assertTrue($this->financialNeed->getNeedsByAmount()->contains($this->financialNeedByAmount));
        $this->assertTrue($this->financialNeed->removeFinancialNeedByAmount($this->financialNeedByAmount));
        $this->assertEquals(0, $this->financialNeed->getNeedsByAmount()->count());
        $this->assertFalse($this->financialNeed->getNeedsByAmount()->contains($this->financialNeedByAmount));
        $this->assertFalse($this->financialNeed->removeFinancialNeedByAmount($this->financialNeedByAmount));
    }
    
    public function testIsFulfilled() {
        $this->assertFalse($this->financialNeed->isFulfilled());
        $this->financialNeed->addFinancialNeedByAmount($this->financialNeedByAmount);
        $this->assertFalse($this->financialNeed->isFulfilled());
        $this->financialNeedByAmount->setContribution($this->contribution);
        $this->assertTrue($this->financialNeed->isFulfilled());
        $this->financialNeed->addFinancialNeedByAmount(new FinancialNeedByAmount());
        $this->assertFalse($this->financialNeed->isFulfilled());
    }
    
    public function testCompareTotalWithSumOfParts() {
        $this->financialNeed->setTotalAmount(new MultiCurrencyValue('10', 'EUR'));
        $this->assertEquals(MultiCurrencyValue::COMPARE_DIFFERENT_NUMBER_OF_CURRENCIES,
                $this->financialNeed->compareTotalWithSumOfParts());
        
        $financialNeedByAmount1 = new FinancialNeedByAmount();
        $financialNeedByAmount1->setAmount(new MultiCurrencyValue('10', 'EUR'));
        $this->financialNeed->addFinancialNeedByAmount($financialNeedByAmount1);
        $this->assertEquals(MultiCurrencyValue::COMPARE_EQUALS,
                $this->financialNeed->compareTotalWithSumOfParts());
        
        $financialNeedByAmount1->setAmount(new MultiCurrencyValue('5', 'EUR'));
        $this->assertEquals(MultiCurrencyValue::COMPARE_DIFFERENT_VALUE,
                $this->financialNeed->compareTotalWithSumOfParts());
        
        $financialNeedByAmount2 = new FinancialNeedByAmount();
        $financialNeedByAmount2->setAmount(new MultiCurrencyValue('5', 'EUR'));
        $this->financialNeed->addFinancialNeedByAmount($financialNeedByAmount2);
        $this->assertEquals(MultiCurrencyValue::COMPARE_EQUALS,
                $this->financialNeed->compareTotalWithSumOfParts());
        $financialNeedByAmount1->getAmount()->addValueByCurrencyCode('5', 'GBP');
        $financialNeedByAmount1->getAmount()->setDefaultCurrencyCode('GBP');
        $financialNeedByAmount1->getAmount()->removeValueByCurrencyCode('EUR');
        $financialNeedByAmount2->getAmount()->addValueByCurrencyCode('5', 'GBP');
        $financialNeedByAmount2->getAmount()->setDefaultCurrencyCode('GBP');
        $financialNeedByAmount2->getAmount()->removeValueByCurrencyCode('EUR');
        $this->assertEquals(MultiCurrencyValue::COMPARE_DIFFERENT_LIST_OF_CURRENCIES,
                $this->financialNeed->compareTotalWithSumOfParts());
    }
    
    public function testValidate_missingtotalamount() {
        $this->financialNeed->setProject($this->project);
        $this->setExpectedException('Exposure\Model\FinancialNeedException', 
            FinancialNeed::EXCEPTION_MISSING_TOTAL_AMOUNT);
        $this->financialNeed->validate();
    }
    
    public function testValidate_missingproject() {
        $this->financialNeed->setTotalAmount($this->totalAmount);
        $this->setExpectedException('Exposure\Model\FinancialNeedException', 
            FinancialNeed::EXCEPTION_MISSING_PROJECT);
        $this->financialNeed->validate();
    }
    
    public function testValidate() {
        $this->financialNeed->setTotalAmount($this->totalAmount);
        $this->financialNeed->setProject($this->project);
        $this->financialNeed->validate();
        $this->financialNeed->addFinancialNeedByAmount($this->financialNeedByAmount);
        $this->financialNeed->validate();
    }

    public function tearDown() {
        unset($this->financialNeed);
    }

}

?>
