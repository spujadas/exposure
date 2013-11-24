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

use Exposure\Model\Administration,
    Exposure\Model\ProjectThemeSuggestionNotification,
    Sociable\Utility\StringValidator;

class AdministrationTest extends \PHPUnit_Framework_TestCase {
    protected $administration;
    const LABEL = 'ZZZZZ';
    protected $notification;

    public function setUp() {
        $this->administration = new Administration();
        
        $this->notification = new ProjectThemeSuggestionNotification();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\Administration', $this->administration);
        $this->assertEquals(0, $this->administration->getNotifications()->count());
    }

    public function testSetLabel_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->administration->setLabel(null);
    }
    
    public function testGetLabel_notastring() {
        try {
            $this->administration->setLabel(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->administration->getLabel());
    }
    
    public function testSetLabel_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->administration->setLabel('');
    }
    
    public function testGetLabel_empty() {
        try {
            $this->administration->setLabel('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->administration->getLabel());
    }
    
    public function testSetLabel_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->administration->setLabel(str_repeat('a', Administration::LABEL_MAX_LENGTH + 1));
    }
    
    public function testGetLabel_toolong() {
        try {
            $this->administration->setLabel(str_repeat('a', Administration::LABEL_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->administration->getLabel());
    }

    public function testSetGetLabel() {
        $this->assertEquals(self::LABEL, $this->administration->setLabel(self::LABEL));
        $this->assertEquals(self::LABEL, $this->administration->getLabel());
    }
    
    public function testAddNotification_invalid() {
        $notification = $this->getMockForAbstractClass('Exposure\Model\Notification');
        $this->setExpectedException('Exposure\Model\AdministrationException', 
                Administration::EXCEPTION_INVALID_NOTIFICATION_TYPE);
        $this->administration->addNotification($notification);
    }
    
    public function testAddNotification() {
        $this->administration->addNotification($this->notification);
        $this->assertEquals(1, $this->administration->getNotifications()->count());
    }
    
    public function testRemoveNotification() {
        $this->assertEquals(0, $this->administration->getNotifications()->count());
        $this->administration->addNotification($this->notification);
        $this->assertEquals(1, $this->administration->getNotifications()->count());
        $dummyNotification = new ProjectThemeSuggestionNotification();
        $this->assertFalse($this->administration->removeNotification($dummyNotification));
        $this->assertEquals(1, $this->administration->getNotifications()->count());
        $this->assertTrue($this->administration->removeNotification($this->notification));
        $this->assertEquals(0, $this->administration->getNotifications()->count());
    }
    
    public function testValidate_missinglabel() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->administration->validate();
    }

    public function testValidate() {
        $this->administration->setLabel(self::LABEL);
        $this->administration->validate();
        $this->administration->addNotification($this->notification);
        $this->administration->validate();
    }

    public function tearDown() {
        unset($this->administration);
    }

}

?>
