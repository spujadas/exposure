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

use Exposure\Model\Notification,
    Sociable\Utility\StringValidator;

class NotificationTest extends \PHPUnit_Framework_TestCase {

    protected $notification;
    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';

    public function setUp() {
        $this->notification = $this->getMockForAbstractClass('Exposure\Model\Notification');
        
        $this->dateTime = new \DateTime;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\Notification', $this->notification);
    }
    
    public function testSetStatus_invalidstatus() {
        $this->setExpectedException('Exposure\Model\NotificationException', 
            Notification::EXCEPTION_INVALID_STATUS);
        $this->notification->setStatus(null);
    }
    
    public function testGetStatus_invalidstatus() {
        try {
            $this->notification->setStatus(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->notification->getStatus());
    }
    
    public function testSetGetStatus() {
        $this->assertEquals(self::STATUS, $this->notification->setStatus(self::STATUS));
        $this->assertEquals(self::STATUS, $this->notification->getStatus());
    }

    public function testSetContent_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->notification->setContent(array());
    }
    
    public function testGetContent_notastring() {
        try {
            $this->notification->setContent(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->notification->getContent());
    }
    
    public function testSetContent_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->notification->setContent('');
    }
    
    public function testGetContent_empty() {
        try {
            $this->notification->setContent('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->notification->getContent());
    }
    
    public function testSetContent_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->notification->setContent(str_repeat('a', Notification::CONTENT_MAX_LENGTH + 1));
    }
    
    public function testGetContent_toolong() {
        try {
            $this->notification->setContent(str_repeat('a', Notification::CONTENT_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->notification->getContent());
    }
    
    public function testSetGetContent() {
        $this->assertEquals(self::CONTENT, 
                $this->notification->setContent(self::CONTENT));
        $this->assertEquals(self::CONTENT, $this->notification->getContent());
    }
            
    public function testSetGetDateTime() {
        $this->assertEquals($this->dateTime, 
                $this->notification->setDateTime($this->dateTime));
        $this->assertEquals($this->dateTime, $this->notification->getDateTime());
    }
            
    public function testValidate_uninitialised() {
        $this->setExpectedException('Exposure\Model\NotificationException', 
            Notification::EXCEPTION_INVALID_STATUS);
        $this->notification->validate();
    }
    
    public function tearDown() {
        unset($this->notification);
    }

}

?>
