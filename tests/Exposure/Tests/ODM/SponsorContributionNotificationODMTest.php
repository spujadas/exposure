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

use Exposure\Model\SponsorContributionNotification,
    Exposure\Model\SponsorContribution,
    Exposure\Model\Notification,
    Sociable\ODM\ObjectDocumentMapper;

class SponsorContributionNotificationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $notification;
    protected static $id = null;

    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = SponsorContributionNotification::EVENT_CONTRIBUTION_RECEIVED;

    protected $contribution;
    protected static $contributionId = null;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->dateTime = new \DateTime();
        
        $this->contribution = new SponsorContribution;
        
        $this->notification = new SponsorContributionNotification;
        $this->notification->setStatus(self::STATUS);
        $this->notification->setContent(self::CONTENT);
        $this->notification->setEvent(self::EVENT);
        $this->notification->setDateTime($this->dateTime);
        $this->notification->setContribution($this->contribution);
        $this->notification->validate();
        
        self::$dm->persist($this->notification);
        self::$dm->persist($this->contribution);
        self::$dm->flush();
        
        self::$id = $this->notification->getId();
        self::$contributionId = $this->contribution->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContributionNotification', self::$id);
        $this->assertNotNull($this->notification);
        $this->assertInstanceOf('Exposure\Model\SponsorContributionNotification', $this->notification);
    }
    
    public function testIsValid() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContributionNotification', self::$id);
        $this->notification->validate();
    }
   
    public function testIsEqual() {
        $notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContributionNotification', self::$id);
        $this->assertEquals(self::STATUS, $notification->getStatus());
        $this->assertEquals(self::CONTENT, $notification->getContent());
        $this->assertEquals(self::EVENT, $notification->getEvent());
        $this->assertEquals($this->dateTime, $notification->getDateTime());
        $this->assertEquals($this->contribution->getId(), $notification->getContribution()->getId());

    }
   
    public function testRemove() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContributionNotification', self::$id);
        self::$dm->remove($this->notification);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContributionNotification', self::$id));
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
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContributionNotification', self::$id);
            if(!is_null($notification)) {
                self::$dm->remove($notification);
            }
        }
        if (!is_null(self::$contributionId)) {
            $contribution = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContribution', self::$contributionId);
            if(!is_null($contribution)) {
                self::$dm->remove($contribution);
            }
        }
        self::$dm->flush();
    }
}

?>
