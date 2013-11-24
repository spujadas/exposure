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

use Exposure\Model\ModerationReason,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator;

class ModerationReasonTest extends \PHPUnit_Framework_TestCase {

    protected $moderationReason;
    const CODE = 's';
    
    protected $content;
    protected $content_toolong;
    
    public function setUp() {
        $this->moderationReason = new ModerationReason();

        $this->content = new MultiLanguageString('code', 'fr');
        
        $this->content_toolong = new MultiLanguageString(str_repeat('a', ModerationReason::CONTENT_MAX_LENGTH + 1), 'fr');
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ModerationReason', $this->moderationReason);
    }

    public function testSetCode_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->moderationReason->setCode(null);
    }
    
    public function testGetCode_notastring() {
        try {
            $this->moderationReason->setCode(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationReason->getCode());
    }
    
    public function testSetCode_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->moderationReason->setCode('');
    }
    
    public function testGetCode_empty() {
        try {
            $this->moderationReason->setCode('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationReason->getCode());
    }
    
    public function testSetCode_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->moderationReason->setCode(str_repeat('a', ModerationReason::CODE_MAX_LENGTH + 1));
    }
    
    public function testGetCode_toolong() {
        try {
            $this->moderationReason->setCode(str_repeat('a', ModerationReason::CODE_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationReason->getCode());
    }

    public function testSetGetCode() {
        $this->assertEquals(self::CODE, $this->moderationReason->setCode(self::CODE));
        $this->assertEquals(self::CODE, $this->moderationReason->getCode());
    }
    
    public function testSetContent_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->moderationReason->setContent($this->content_toolong);
    }
    
    public function testGetContent_toolong() {
        try {
            $this->moderationReason->setContent($this->content_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationReason->getContent());
    }
    
    public function testSetGetContent() {
        $this->assertEquals($this->content, 
                $this->moderationReason->setContent($this->content));
        $this->assertEquals($this->content, $this->moderationReason->getContent());
    }
    
    public function testValidate_uninitialised() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->moderationReason->validate();
    }
    
    public function testValidate() {
        $this->assertEquals(self::CODE, $this->moderationReason->setCode(self::CODE));
        $this->assertEquals($this->content, 
                $this->moderationReason->setContent($this->content));
        $this->moderationReason->validate();
    }

    public function tearDown() {
        unset($this->moderationReason);
    }

}

?>
