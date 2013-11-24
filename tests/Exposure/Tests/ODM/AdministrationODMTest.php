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

use Exposure\Model\Administration,
    Exposure\Model\ProjectThemeSuggestionNotification,
    Exposure\Model\ProfileModerationNotification,
    Exposure\Model\ProjectModerationNotification,
    Exposure\Model\Notification,
    Sociable\ODM\ObjectDocumentMapper;

class AdministrationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $administration;

    const LABEL = 'ZZZZZ';
    protected $notifications;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanup();
        
        $notification = new ProfileModerationNotification;
        $notification->setDateTime(new \DateTime('2011-01-01'));
        $this->notifications[] = $notification;
        
        $notification = new ProjectModerationNotification;
        $notification->setDateTime(new \DateTime('2013-01-01'));
        $this->notifications[] = $notification;
        
        $notification = new ProjectThemeSuggestionNotification;
        $notification->setDateTime(new \DateTime('2012-01-01'));
        $this->notifications[] = $notification;
        
        $this->administration = new Administration();
        $this->administration->setLabel(self::LABEL);
        $this->administration->addNotification($this->notifications[0]);
        $this->administration->addNotification($this->notifications[1]);
        $this->administration->addNotification($this->notifications[2]);
        $this->administration->validate();
        
        self::$dm->persist($this->administration);
        self::$dm->persist($this->notifications[0]);
        self::$dm->persist($this->notifications[1]);
        self::$dm->persist($this->notifications[2]);
        self::$dm->flush();

        self::$dm->clear();
    }

    public function testFound() {
        $this->administration = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Administration', self::LABEL);
        $this->assertNotNull($this->administration);
    }
    
    public function testIsValid() {
        $this->administration = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Administration', self::LABEL);
        $this->administration->validate();
    }
   
    public function testIsEqual() {
        $administration = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Administration', self::LABEL);
        $this->assertEquals($this->administration->getLabel(), $administration->getLabel());
        $notifications = $this->administration->getNotifications();
        $this->assertEquals(3, $notifications->count());
        $this->assertEquals($this->notifications[0], $notifications[0]);
        $this->assertEquals($this->notifications[1], $notifications[1]);
        $this->assertEquals($this->notifications[2], $notifications[2]);
        /*$this->assertEquals($this->notifications[1], $notifications[0]);
        $this->assertEquals($this->notifications[2], $notifications[1]);
        $this->assertEquals($this->notifications[0], $notifications[2]);*/
    }
   
    public function testRemove() {
        $this->administration = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Administration', self::LABEL);
        self::$dm->remove($this->administration);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Administration', self::LABEL));
    }
    
    public function testDuplicate() {
        $this->administration = new Administration();
        $this->administration->setLabel(self::LABEL);
        $this->administration->validate();

        self::$dm->persist($this->administration);
        
        $this->setExpectedException('MongoCursorException');
        self::$dm->flush();
    }

    public function tearDown() {
        self::cleanup();
    }
    
    public static function tearDownAfterClass() {
        self::cleanup();
    }
    
    public static function cleanUp() {
        self::$dm->clear();
        $administration = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Administration', self::LABEL);
        if(!is_null($administration)) {
            /* // removed as remove is now cascaded to notifications
            foreach ($administration->getNotifications() as $notification) {
                self::$dm->remove($notification);
            }
            */
            self::$dm->remove($administration);
            self::$dm->flush();
        }
    }

}

?>
