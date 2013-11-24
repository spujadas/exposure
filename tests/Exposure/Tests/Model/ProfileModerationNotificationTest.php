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

use Exposure\Model\ProfileModerationNotification,
    Exposure\Model\Notification;
    
class ProfileModerationNotificationTest extends \PHPUnit_Framework_TestCase {

    protected $notification;
    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = ProfileModerationNotification::EVENT_APPROVED_PROFILE;

    public function setUp() {
        $this->notification = new ProfileModerationNotification();
        $this->dateTime = new \DateTime();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ProfileModerationNotification', 
                $this->notification);
        $this->assertEquals(Notification::TYPE_PROFILE_MODERATION, 
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
    
    public function testValidate() {
        $this->assertEquals(self::STATUS, $this->notification->setStatus(self::STATUS));
        $this->assertEquals(self::CONTENT, $this->notification->setContent(self::CONTENT));
        $this->assertEquals(self::EVENT, $this->notification->setEvent(self::EVENT));
        $this->assertEquals($this->dateTime, $this->notification->setDateTime($this->dateTime));
        $this->notification->validate();
    }

    public function tearDown() {
        unset($this->notification);
    }

}

?>
