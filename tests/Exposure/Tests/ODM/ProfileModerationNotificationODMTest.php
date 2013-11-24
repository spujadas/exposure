<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Tests\ODM;

use Exposure\Model\ProfileModerationNotification,
    Exposure\Model\Notification,
    Sociable\ODM\ObjectDocumentMapper;

class ProfileModerationNotificationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    protected static $id = null;
    
    protected $notification;

    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = ProfileModerationNotification::EVENT_APPROVED_PROFILE;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanup();
        
        $this->dateTime = new \DateTime();
        
        $this->notification = new ProfileModerationNotification();
        $this->notification->setStatus(self::STATUS);
        $this->notification->setContent(self::CONTENT);
        $this->notification->setEvent(self::EVENT);
        $this->notification->setDateTime($this->dateTime);
        $this->notification->validate();
        
        self::$dm->persist($this->notification);
        self::$dm->flush();
        
        self::$id = $this->notification->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\ProfileModerationNotification', self::$id);
        $this->assertNotNull($this->notification);
        $this->assertInstanceOf('Exposure\Model\ProfileModerationNotification', $this->notification);
    }
    
    public function testIsValid() {
        $this->notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\ProfileModerationNotification', self::$id);
        $this->notification->validate();
    }
   
    public function testIsEqual() {
        $notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\ProfileModerationNotification', self::$id);
        $this->assertEquals(self::STATUS, $notification->getStatus());
        $this->assertEquals(self::CONTENT, $notification->getContent());
        $this->assertEquals(self::EVENT, $notification->getEvent());
        $this->assertEquals($this->dateTime, $notification->getDateTime());
    }
   
    public function testRemove() {
        $this->notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\ProfileModerationNotification', self::$id);
        self::$dm->remove($this->notification);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\ProfileModerationNotification', self::$id));
    }
    
    public function tearDown() {
        self::cleanup();
    }
    
    public static function tearDownAfterClass() {
        self::cleanup();
    }
    
    public static function cleanUp() {
        self::$dm->clear();
        if (!is_null(self::$id)) {
            $notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\ProfileModerationNotification', self::$id);
            if(!is_null($notification)) {
                self::$dm->remove($notification);
            }
        }
        self::$dm->flush();
    }

}

?>
