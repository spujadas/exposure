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

use Exposure\Model\SubscriptionType,
    Exposure\Model\SubscriptionPrice,
    Exposure\Model\ProjectRights,
    Exposure\Model\ViewRights,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator;

class SubscriptionTypeTest extends \PHPUnit_Framework_TestCase {
    protected $subscriptionType;
    
    const LABEL = 'label';
    
    protected $name;
    protected $name_toolong;
    protected $name_empty;
   
    protected $subscriptionPrice;
    protected $projectRights;
    protected $viewRights;
    
    public function setUp() {
        $this->subscriptionType = new SubscriptionType;
        $this->subscriptionPrice = new SubscriptionPrice;
        $this->projectRights = new ProjectRights;
        $this->viewRights = new ViewRights;
        $this->name = new MultiLanguageString('foo','fr');
        $this->name_toolong = new MultiLanguageString(
                str_repeat('a', SubscriptionType::NAME_MAX_LENGTH + 1), 'fr');
        $this->name_empty = new MultiLanguageString('', 'fr');
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SubscriptionType', $this->subscriptionType);
    }

    public function testSetLabel_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->subscriptionType->setLabel(array());
    }
    
    public function testGetLabel_notastring() {
        try {
            $this->subscriptionType->setLabel(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionType->getLabel());
    }
    
    public function testSetLabel_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->subscriptionType->setLabel('');
    }
    
    public function testGetLabel_empty() {
        try {
            $this->subscriptionType->setLabel('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionType->getLabel());
    }
    
    public function testSetLabel_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', StringValidator::EXCEPTION_TOO_LONG);
        $this->subscriptionType->setLabel(str_repeat('a', SubscriptionType::LABEL_MAX_LENGTH + 1));
    }
    
    public function testGetLabel_toolong() {
        try {
            $this->subscriptionType->setLabel(str_repeat('a', SubscriptionType::LABEL_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionType->getLabel());
    }
    
    public function testSetGetLabel() {
        $this->assertEquals(self::LABEL, 
                $this->subscriptionType->setLabel(self::LABEL));
        $this->assertEquals(self::LABEL, $this->subscriptionType->getLabel());
    }
    
    public function testSetName_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->subscriptionType->setName($this->name_empty);
    }
    
    public function testGetName_empty() {
        try {
            $this->subscriptionType->setName($this->name_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionType->getName());
    }
    
    public function testSetName_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->subscriptionType->setName($this->name_toolong);
    }
    
    public function testGetName_toolong() {
        try {
            $this->subscriptionType->setName($this->name_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->subscriptionType->getName());
    }
    
    public function testSetGetName() {
        $this->assertEquals($this->name, 
                $this->subscriptionType->setName($this->name));
        $this->assertEquals($this->name, $this->subscriptionType->getName());
    }
    
    public function testAddRemoveSubscriptionPrice() {
        $this->subscriptionType->addSubscriptionPrice($this->subscriptionPrice);
        $this->assertEquals(1, $this->subscriptionType->getSubscriptionPrices()->count());
        $this->assertTrue($this->subscriptionType->getSubscriptionPrices()->contains($this->subscriptionPrice));
        $this->assertTrue($this->subscriptionType->removeSubscriptionPrice($this->subscriptionPrice));
        $this->assertEquals(0, $this->subscriptionType->getSubscriptionPrices()->count());
        $this->assertFalse($this->subscriptionType->getSubscriptionPrices()->contains($this->subscriptionPrice));
        $this->assertFalse($this->subscriptionType->removeSubscriptionPrice($this->subscriptionPrice));
    }

    public function testSetGetProjectRights() {
        $this->assertEquals($this->projectRights, 
                $this->subscriptionType->setProjectRights($this->projectRights));
        $this->assertEquals($this->projectRights, 
                $this->subscriptionType->getProjectRights());
    }
    
    public function testSetGetViewRights() {
        $this->assertEquals($this->viewRights, 
                $this->subscriptionType->setViewRights($this->viewRights));
        $this->assertEquals($this->viewRights, 
                $this->subscriptionType->getViewRights());
    }
    
    public function testValidate() {
        $this->subscriptionType->setLabel(self::LABEL);
        $this->subscriptionType->setName($this->name);
        $this->subscriptionType->addSubscriptionPrice($this->subscriptionPrice);
        $this->subscriptionType->setProjectRights($this->projectRights);
        $this->subscriptionType->setViewRights($this->viewRights);
        $this->subscriptionType->validate();
    }
    
    public function tearDown() {
        unset($this->subscriptionType);
    }

}

?>
