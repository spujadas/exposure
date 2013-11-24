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

use Exposure\Model\SponsorReturnNotification,
    Exposure\Model\SponsorReturn,
    Exposure\Model\Notification;
    
class SponsorReturnNotificationTest extends \PHPUnit_Framework_TestCase {

    protected $notification;
    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = SponsorReturnNotification::EVENT_APPROVED;
    protected $return;

    public function setUp() {
        $this->notification = new SponsorReturnNotification();
        $this->dateTime = new \DateTime();
        $this->return = new SponsorReturn();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SponsorReturnNotification', 
                $this->notification);
        $this->assertEquals(Notification::TYPE_SPONSOR_RETURN, 
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
    
    public function testSetGetReturn() {
        $this->assertEquals($this->return, $this->notification->setReturn($this->return));
        $this->assertEquals($this->return, $this->notification->getReturn());
    }

        
    public function testValidate() {
        $this->assertEquals(self::STATUS, $this->notification->setStatus(self::STATUS));
        $this->assertEquals(self::CONTENT, $this->notification->setContent(self::CONTENT));
        $this->assertEquals(self::EVENT, $this->notification->setEvent(self::EVENT));
        $this->assertEquals($this->dateTime, $this->notification->setDateTime($this->dateTime));
        $this->assertEquals($this->return, $this->notification->setReturn($this->return));
        $this->notification->validate();
    }

    public function tearDown() {
        unset($this->notification);
    }

}

?>
