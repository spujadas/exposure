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

use Exposure\Model\ProjectThemeSuggestionNotification,
    Exposure\Model\User,
    Exposure\Model\Theme,
    Exposure\Model\Notification,
    Sociable\Utility\StringValidator;
    
class ProjectThemeSuggestionNotificationTest extends \PHPUnit_Framework_TestCase {

    protected $notification;
    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = ProjectThemeSuggestionNotification::EVENT_ACCEPTED_THEME;
    protected $from;
    protected $theme;
    const THEME_NAME = 'new theme';

    public function setUp() {
        $this->notification = new ProjectThemeSuggestionNotification();
        $this->dateTime = new \DateTime();
        $this->from = new User();
        $this->theme = new Theme();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ProjectThemeSuggestionNotification', 
                $this->notification);
        $this->assertEquals(Notification::TYPE_PROJECT_THEME_SUGGESTION, 
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
    
    public function testSetGetFrom() {
        $this->assertEquals($this->from, $this->notification->setFrom($this->from));
        $this->assertEquals($this->from, $this->notification->getFrom());
    }

    public function testSetGetParentTheme() {
        $this->assertNull($this->notification->setParentTheme(null));
        $this->assertNull($this->notification->getParentTheme());
        $this->assertEquals($this->theme, $this->notification->setParentTheme($this->theme));
        $this->assertEquals($this->theme, $this->notification->getParentTheme());
    }
    
    public function testSetThemeName_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->notification->setThemeName(array());
    }
    
    public function testGetThemeName_notastring() {
        try {
            $this->notification->setThemeName(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->notification->getThemeName());
    }
    
    public function testSetThemeName_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->notification->setThemeName('');
    }
    
    public function testGetThemeName_empty() {
        try {
            $this->notification->setThemeName('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->notification->getThemeName());
    }
    
    public function testSetThemeName_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->notification->setThemeName(str_repeat('a', Theme::NAME_MAX_LENGTH + 1));
    }
    
    public function testGetThemeName_toolong() {
        try {
            $this->notification->setThemeName(str_repeat('a', Theme::NAME_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->notification->getThemeName());
    }
    
    public function testSetGetThemeName() {
        $this->assertEquals(self::THEME_NAME, 
                $this->notification->setThemeName(self::THEME_NAME));
        $this->assertEquals(self::THEME_NAME, $this->notification->getThemeName());
    }
        
    public function testValidate() {
        $this->assertEquals(self::STATUS, $this->notification->setStatus(self::STATUS));
        $this->assertEquals(self::CONTENT, $this->notification->setContent(self::CONTENT));
        $this->assertEquals(self::EVENT, $this->notification->setEvent(self::EVENT));
        $this->assertEquals($this->dateTime, $this->notification->setDateTime($this->dateTime));
        $this->assertEquals($this->from, $this->notification->setFrom($this->from));
        $this->assertEquals(self::THEME_NAME, $this->notification->setThemeName(self::THEME_NAME));
        $this->notification->validate();
        $this->assertEquals($this->theme, $this->notification->setParentTheme($this->theme));
        $this->notification->validate();
    }

    public function tearDown() {
        unset($this->notification);
    }

}

?>
