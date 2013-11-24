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

use Exposure\Model\SponsorReturnOnFinancialContribution,
    Exposure\Model\SponsorReturnType,
    Exposure\Model\SponsorReturn,
    Exposure\Model\Theme,
    Sociable\Model\MultiCurrencyValue,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator;

class SponsorReturnOnFinancialContributionTest extends \PHPUnit_Framework_TestCase {
    protected $item;
    protected $theme;
    protected $amount;
    protected $description;
    protected $description_toolong;
    protected $description_empty;
    protected $type;

    public function setUp() {
        $this->item = new SponsorReturnOnFinancialContribution;
        
        $this->theme = new Theme;
        $this->amount = new MultiCurrencyValue(10, 'EUR');
        
        $this->description = new MultiLanguageString('foo', 'fr');
        $this->description_toolong = new MultiLanguageString(
                str_repeat('a', SponsorReturn::DESCRIPTION_MAX_LENGTH + 1),
                'fr');
        $this->description_empty = new MultiLanguageString('', 'fr');
        
        $this->type = new SponsorReturnType;

    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SponsorReturnOnFinancialContribution', $this->item);
    }
    
    public function testSetGetTheme() {
        $this->assertNull($this->item->setTheme(null));
        $this->assertNull($this->item->getTheme());
        $this->assertEquals($this->theme, $this->item->setTheme($this->theme));
        $this->assertEquals($this->theme, $this->item->getTheme());
    }
    
    public function testSetGetAmount() {
        $this->assertEquals($this->amount, 
                $this->item->setAmount($this->amount));
        $this->assertEquals($this->amount, $this->item->getAmount());
    }

    
    public function testSetDescription_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->item->setDescription($this->description_empty);
    }
    
    public function testGetDescription_empty() {
        try {
            $this->item->setDescription($this->description_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->item->getDescription());
    }
    
    public function testSetDescription_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->item->setDescription($this->description_toolong);
    }
    
    public function testGetDescription_toolong() {
        try {
            $this->item->setDescription($this->description_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->item->getDescription());
    }
    
    public function testSetGetDescription() {
        $this->assertEquals($this->description, 
                $this->item->setDescription($this->description));
        $this->assertEquals($this->description, $this->item->getDescription());
    }
    
    public function testSetGetType() {
        $this->assertEquals($this->type, 
                $this->item->setType($this->type));
        $this->assertEquals($this->type, $this->item->getType());
    }

    public function testValidate_missingamount() {
        $this->item->setDescription($this->description);
        $this->item->setType($this->type);
        $this->setExpectedException('Exposure\Model\SponsorReturnOnFinancialContributionException', 
                SponsorReturnOnFinancialContribution::EXCEPTION_INVALID_AMOUNT);
        $this->item->validate();
    }
    
    public function testValidate_missingreturndescription() {
        $this->item->setAmount($this->amount);
        $this->item->setType($this->type);
        $this->setExpectedException('Exposure\Model\SponsorReturnOnFinancialContributionException', 
                SponsorReturnOnFinancialContribution::EXCEPTION_INVALID_DESCRIPTION);
        $this->item->validate();
    }
    
    public function testValidate_missingtype() {
        $this->item->setAmount($this->amount);
        $this->item->setDescription($this->description);
        $this->setExpectedException('Exposure\Model\SponsorReturnOnFinancialContributionException', 
                SponsorReturnOnFinancialContribution::EXCEPTION_INVALID_TYPE);
        $this->item->validate();
    }
    
    public function testValidate() {
        $this->item->setAmount($this->amount);
        $this->item->setDescription($this->description);
        $this->item->setType($this->type);
        $this->item->validate();
        $this->item->setTheme($this->theme);
        $this->item->validate();
    }
    
    public function tearDown() {
        unset($this->item);
    }

}

?>
