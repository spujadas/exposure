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

use Exposure\Model\Comment,
    Exposure\Model\User,
    Sociable\Utility\NumberValidator,
    Sociable\Utility\StringValidator;

class CommentTest extends \PHPUnit_Framework_TestCase {

    protected $comment;
    const STATUS = Comment::STATUS_PUBLISHED;
    protected $dateTime;
    const CONTENT = 'content';
    const RATING = 4;
    protected $from;

    public function setUp() {
        $this->comment = $this->getMockForAbstractClass('Exposure\Model\Comment');
        
        $this->dateTime = new \DateTime();
        $this->from = new User();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\Comment', $this->comment);
    }
    
    public function testSetStatus_invalidstatus() {
        $this->setExpectedException('Exposure\Model\CommentException', 
            Comment::EXCEPTION_INVALID_STATUS);
        $this->comment->setStatus(null);
    }
    
    public function testGetStatus_invalidstatus() {
        try {
            $this->comment->setStatus(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getStatus());
    }
    
    public function testSetGetStatus() {
        $this->assertEquals(self::STATUS, $this->comment->setStatus(self::STATUS));
        $this->assertEquals(self::STATUS, $this->comment->getStatus());
    }
    
    public function testSetGetDateTime() {
        $this->assertEquals($this->dateTime, 
                $this->comment->setDateTime($this->dateTime));
        $this->assertEquals($this->dateTime, $this->comment->getDateTime());
    }
    
    public function testSetContent_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->comment->setContent(array());
    }
    
    public function testGetContent_notastring() {
        try {
            $this->comment->setContent(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getContent());
    }
    
    public function testSetContent_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->comment->setContent('');
    }
    
    public function testGetContent_empty() {
        try {
            $this->comment->setContent('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getContent());
    }
    
    public function testSetContent_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->comment->setContent(str_repeat('a', Comment::CONTENT_MAX_LENGTH + 1));
    }
    
    public function testGetContent_toolong() {
        try {
            $this->comment->setContent(str_repeat('a', Comment::CONTENT_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getContent());
    }
    
    public function testSetGetContent() {
        $this->assertEquals(self::CONTENT, 
                $this->comment->setContent(self::CONTENT));
        $this->assertEquals(self::CONTENT, $this->comment->getContent());
    }
    
    public function testSetRating_notanumber() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_A_NUMBER);
        $this->comment->setRating(array());
    }
    
    public function testGetRating_notanumber() {
        try {
            $this->comment->setRating(array());
        }
        catch (\Exception $e) {}
    }
    
    public function testSetRating_notaninteger() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_AN_INTEGER);
        $this->comment->setRating(1.2);
    }
    
    public function testGetRating_notaninteger() {
        try {
            $this->comment->setRating(1.2);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getRating());
    }
    
    public function testSetRating_toosmall() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_TOO_SMALL);
        $this->comment->setRating(Comment::RATING_MIN - 1);
    }
    
    public function testGetRating_toosmall() {
        try {
            $this->comment->setRating(Comment::RATING_MIN - 1);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getRating());
    }
    
    public function testSetRating_toolarge() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_TOO_LARGE);
        $this->comment->setRating(Comment::RATING_MAX + 1);
    }
    
    public function testGetRating_toolarge() {
        try {
            $this->comment->setRating(Comment::RATING_MAX + 1);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getRating());
    }
    
    public function testSetGetRating() {
        $this->assertNull($this->comment->setRating(null));
        $this->assertNull($this->comment->getRating());
        $this->assertEquals(Comment::RATING_MAX, $this->comment->setRating(Comment::RATING_MAX));
        $this->assertEquals(Comment::RATING_MAX, $this->comment->getRating());
    }

    public function testSetGetFrom() {
        $this->assertEquals($this->from, $this->comment->setFrom($this->from));
        $this->assertEquals($this->from, $this->comment->getFrom());
    }
        
    public function testValidate_uninitialised() {
        $this->setExpectedException('Exposure\Model\CommentException', 
            Comment::EXCEPTION_INVALID_STATUS);
        $this->comment->validate();
    }
    
    public function tearDown() {
        unset($this->comment);
    }

}

?>
