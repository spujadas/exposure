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

use Exposure\Model\CommentNotification,
    Exposure\Model\CommentOnProjectOwner,
    Exposure\Model\Notification;
    
class CommentNotificationTest extends \PHPUnit_Framework_TestCase {

    protected $notification;
    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = CommentNotification::EVENT_RECEIVED_COMMENT;
    protected $comment;

    public function setUp() {
        $this->notification = new CommentNotification();
        $this->dateTime = new \DateTime();
        $this->comment = new CommentOnProjectOwner();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\CommentNotification', 
                $this->notification);
        $this->assertEquals(Notification::TYPE_COMMENT, 
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
    
    public function testSetGetComment() {
        $this->assertEquals($this->comment, $this->notification->setComment($this->comment));
        $this->assertEquals($this->comment, $this->notification->getComment());
    }

        
    public function testValidate() {
        $this->notification->setStatus(self::STATUS);
        $this->notification->setContent(self::CONTENT);
        $this->notification->setEvent(self::EVENT);
        $this->notification->setDateTime($this->dateTime);
        $this->notification->setComment($this->comment);
        $this->notification->validate();
    }

    public function tearDown() {
        unset($this->notification);
    }

}

?>
