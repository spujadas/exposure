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

use Exposure\Model\ProjectThemeSuggestionNotification,
    Exposure\Model\User,
    Exposure\Model\Theme,
    Exposure\Model\Notification,
    Sociable\ODM\ObjectDocumentMapper,
    Sociable\Model\MultiLanguageString;

class ProjectThemeSuggestionNotificationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $notification;
    protected static $id = null;

    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = ProjectThemeSuggestionNotification::EVENT_ACCEPTED_THEME;

    protected $from;
    protected static $fromId = null;

    protected $theme;
    const THEME_LABEL = 'ZZZZZ';
    const THEME_NAME_STRING = 'ZZZZZ';
    const THEME_NAME_LANGUAGE = 'fr';
    protected $themeName;
    
    const NAME = 'ZZZZZ_suggestion';
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->dateTime = new \DateTime();
        
        $this->from = new User;
        
        $this->theme = new Theme;
        $this->theme->setLabel(self::THEME_LABEL);
        $this->themeName = new MultiLanguageString(self::THEME_NAME_STRING, 
                self::THEME_NAME_LANGUAGE);
        $this->theme->setName($this->themeName);

        $this->notification = new ProjectThemeSuggestionNotification;
        $this->notification->setStatus(self::STATUS);
        $this->notification->setContent(self::CONTENT);
        $this->notification->setEvent(self::EVENT);
        $this->notification->setDateTime($this->dateTime);
        $this->notification->setFrom($this->from);
        $this->notification->setParentTheme($this->theme);
        $this->notification->setThemeName(self::NAME);
        $this->notification->validate();
        
        self::$dm->persist($this->notification);
        self::$dm->persist($this->from);
        self::$dm->persist($this->theme);
        self::$dm->flush();
        
        self::$id = $this->notification->getId();
        self::$fromId = $this->from->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectThemeSuggestionNotification', self::$id);
        $this->assertNotNull($this->notification);
        $this->assertInstanceOf('Exposure\Model\ProjectThemeSuggestionNotification', $this->notification);
    }
    
    public function testIsValid() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectThemeSuggestionNotification', self::$id);
        $this->notification->validate();
    }
   
    public function testIsEqual() {
        $notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectThemeSuggestionNotification', self::$id);
        $this->assertEquals(self::STATUS, $notification->getStatus());
        $this->assertEquals(self::CONTENT, $notification->getContent());
        $this->assertEquals(self::EVENT, $notification->getEvent());
        $this->assertEquals($this->dateTime, $notification->getDateTime());
        $this->assertEquals($this->from->getId(), $notification->getFrom()->getId());
        $this->assertEquals(self::THEME_LABEL, $notification->getParentTheme()->getLabel());
        $this->assertEquals($this->theme->getName()
                ->getStringByLanguageCode(self::THEME_NAME_LANGUAGE), 
                $notification->getParentTheme()->getName()
                ->getStringByLanguageCode(self::THEME_NAME_LANGUAGE)
                );
        $this->assertEquals(self::NAME, $notification->getThemeName());
    }
   
    public function testRemove() {
        $this->notification = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectThemeSuggestionNotification', self::$id);
        self::$dm->remove($this->notification);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectThemeSuggestionNotification', self::$id));
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
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectThemeSuggestionNotification', self::$id);
            if(!is_null($notification)) {
                self::$dm->remove($notification);
            }
        }
        if (!is_null(self::$fromId)) {
            $from = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\User', self::$fromId);
            if(!is_null($from)) {
                self::$dm->remove($from);
            }
        }
        $theme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::THEME_LABEL);
        if(!is_null($theme)) {
            self::$dm->remove($theme);
        }
        self::$dm->flush();
    }
}

?>
