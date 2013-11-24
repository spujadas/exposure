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

use Exposure\Model\ProjectWantNotification,
    Exposure\Model\ProjectWant,
    Exposure\Model\Notification;
    
class ProjectWantNotificationTest extends \PHPUnit_Framework_TestCase {

    protected $notification;
    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = ProjectWantNotification::EVENT_WANTED_PROJECT;
    protected $want;

    public function setUp() {
        $this->notification = new ProjectWantNotification;
        $this->dateTime = new \DateTime;
        $this->want = new ProjectWant;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ProjectWantNotification', 
                $this->notification);
        $this->assertEquals(Notification::TYPE_PROJECT_WANT, 
                $this->notification->getType());
    }
    
    public function testSetEvent_invalidevent() {
        $this->setExpectedException('Exposure\Model\NotificationException', 
            Notification::EXCEPTION_INVALID_EVENT);
        $this->notification->setEvent(null);
    }
    
    public function testGetEvent_invalidevent() {
        try {
            $this->notification->setEvent(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->notification->getEvent());
    }
    
    public function testSetGetEvent() {
        $this->assertEquals(self::EVENT, $this->notification->setEvent(self::EVENT));
        $this->assertEquals(self::EVENT, $this->notification->getEvent());
    }
    
    public function testSetGetWant() {
        $this->assertEquals($this->want, $this->notification->setWant($this->want));
        $this->assertEquals($this->want, $this->notification->getWant());
    }

        
    public function testValidate() {
        $this->assertEquals(self::STATUS, $this->notification->setStatus(self::STATUS));
        $this->assertEquals(self::CONTENT, $this->notification->setContent(self::CONTENT));
        $this->assertEquals(self::EVENT, $this->notification->setEvent(self::EVENT));
        $this->assertEquals($this->dateTime, $this->notification->setDateTime($this->dateTime));
        $this->assertEquals($this->want, $this->notification->setWant($this->want));
        $this->notification->validate();
    }

    public function tearDown() {
        unset($this->notification);
    }

}

?>
