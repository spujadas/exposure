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

use Exposure\Model\SponsorReturnNotification,
    Exposure\Model\SponsorReturn,
    Exposure\Model\Notification,
    Sociable\ODM\ObjectDocumentMapper;

class SponsorReturnNotificationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $notification;
    protected static $id = null;

    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = SponsorReturnNotification::EVENT_APPROVED;

    protected $return;
    protected static $returnId = null;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->dateTime = new \DateTime();
        
        $this->return = new SponsorReturn;
        
        $this->notification = new SponsorReturnNotification;
        $this->notification->setStatus(self::STATUS);
        $this->notification->setContent(self::CONTENT);
        $this->notification->setEvent(self::EVENT);
        $this->notification->setDateTime($this->dateTime);
        $this->notification->setReturn($this->return);
        $this->notification->validate();
        
        self::$dm->persist($this->notification);
        self::$dm->persist($this->return);
        self::$dm->flush();
        
        self::$id = $this->notification->getId();
        self::$returnId = $this->return->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnNotification', self::$id);
        $this->assertNotNull($this->notification);
        $this->assertInstanceOf('Exposure\Model\SponsorReturnNotification', $this->notification);
    }
    
    public function testIsValid() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnNotification', self::$id);
        $this->notification->validate();
    }
   
    public function testIsEqual() {
        $notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnNotification', self::$id);
        $this->assertEquals(self::STATUS, $notification->getStatus());
        $this->assertEquals(self::CONTENT, $notification->getContent());
        $this->assertEquals(self::EVENT, $notification->getEvent());
        $this->assertEquals($this->dateTime, $notification->getDateTime());
        $this->assertEquals($this->return->getId(), $notification->getReturn()->getId());

    }
   
    public function testRemove() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnNotification', self::$id);
        self::$dm->remove($this->notification);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnNotification', self::$id));
    }
    
    public function tearDown() {
        self::cleanUp();
    }
    
    public static function tearDownAfterClass() {
        self::cleanUp();
    }

    public static function cleanUp() {
        self::$dm->clear();
        if (!is_null(self::$id)) {
            $notification = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnNotification', self::$id);
            if(!is_null($notification)) {
                self::$dm->remove($notification);
            }
        }
        if (!is_null(self::$returnId)) {
            $return = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$returnId);
            if(!is_null($return)) {
                self::$dm->remove($return);
            }
        }
        self::$dm->flush();
    }
}

?>
