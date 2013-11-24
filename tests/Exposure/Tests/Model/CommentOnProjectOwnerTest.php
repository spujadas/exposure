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

use Exposure\Model\CommentOnProjectOwner,
    Exposure\Model\Comment,
    Exposure\Model\User,
    Sociable\Utility\StringValidator;
    
class CommentOnProjectOwnerTest extends \PHPUnit_Framework_TestCase {

    protected $comment;
    const STATUS = Comment::STATUS_PUBLISHED;
    protected $dateTime;
    const CONTENT = 'content';
    const RATING = 4;
    protected $from;
    protected $projectOwner;

    public function setUp() {
        $this->comment = new CommentOnProjectOwner();
        
        $this->dateTime = new \DateTime();
        $this->from = new User();
        $this->projectOwner = new User();
        $this->projectOwner->setType(User::TYPE_PROJECT_OWNER);
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\CommentOnProjectOwner', $this->comment);
        $this->assertEquals(Comment::TYPE_COMMENT_ON_PROJECT_OWNER, $this->comment->getType());
    }
    
    public function testSetProjectOwner_invalid() {
        $this->setExpectedException('Exposure\Model\CommentOnProjectOwnerException', 
            CommentOnProjectOwner::EXCEPTION_OBJECT_USER_NOT_A_PROJECT_OWNER);
        $this->comment->setProjectOwner(new User());
    }
    
    public function testGetProjectOwner_invalid() {
        try {
            $this->comment->setProjectOwner(new User());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getProjectOwner());
    }
    
    public function testSetProjectOwner_incorrecttype() {
        $this->projectOwner->setType(User::TYPE_SPONSOR);
        $this->setExpectedException('Exception');
        $this->comment->setProjectOwner($this->projectOwner);
    }
    
    public function testGetProjectOwner_incorrecttype() {
        $this->projectOwner->setType(User::TYPE_SPONSOR);
        try {
            $this->comment->setProjectOwner($this->projectOwner);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->comment->getProjectOwner());
    }
    
    public function testSetGetProjectOwner() {
        $this->assertEquals($this->projectOwner, 
                $this->comment->setProjectOwner($this->projectOwner));
        $this->assertEquals($this->projectOwner, 
                $this->comment->getProjectOwner());
    }
        
    public function testValidate_missingpstatus() {
        $this->assertEquals(self::CONTENT, $this->comment->setContent(self::CONTENT));
        $this->assertEquals($this->dateTime, $this->comment->setDateTime($this->dateTime));
        $this->assertEquals($this->from, $this->comment->setFrom($this->from));
        $this->assertEquals($this->projectOwner, $this->comment->setProjectOwner($this->projectOwner));
        $this->setExpectedException('Exposure\Model\CommentException', 
                CommentOnProjectOwner::EXCEPTION_INVALID_STATUS);
        $this->comment->validate();
    }
        
    public function testValidate_missingcontent() {
        $this->assertEquals(self::STATUS, $this->comment->setStatus(self::STATUS));
        $this->assertEquals($this->dateTime, $this->comment->setDateTime($this->dateTime));
        $this->assertEquals($this->from, $this->comment->setFrom($this->from));
        $this->assertEquals($this->projectOwner, $this->comment->setProjectOwner($this->projectOwner));
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_NOT_A_STRING);
        $this->comment->validate();
    }
        
    public function testValidate_missingdatetime() {
        $this->assertEquals(self::STATUS, $this->comment->setStatus(self::STATUS));
        $this->assertEquals(self::CONTENT, $this->comment->setContent(self::CONTENT));
        $this->assertEquals($this->from, $this->comment->setFrom($this->from));
        $this->assertEquals($this->projectOwner, $this->comment->setProjectOwner($this->projectOwner));
        $this->setExpectedException('Exposure\Model\CommentException', 
                CommentOnProjectOwner::EXCEPTION_INVALID_DATE_TIME);
        $this->comment->validate();
    }
        
    public function testValidate_missingfrom() {
        $this->assertEquals(self::STATUS, $this->comment->setStatus(self::STATUS));
        $this->assertEquals(self::CONTENT, $this->comment->setContent(self::CONTENT));
        $this->assertEquals($this->dateTime, $this->comment->setDateTime($this->dateTime));
        $this->assertEquals($this->projectOwner, $this->comment->setProjectOwner($this->projectOwner));
        $this->setExpectedException('Exposure\Model\CommentException', 
                CommentOnProjectOwner::EXCEPTION_INVALID_FROM);
        $this->comment->validate();
    }
        
    public function testValidate_missingprojectowner() {
        $this->assertEquals(self::STATUS, $this->comment->setStatus(self::STATUS));
        $this->assertEquals(self::CONTENT, $this->comment->setContent(self::CONTENT));
        $this->assertEquals($this->dateTime, $this->comment->setDateTime($this->dateTime));
        $this->assertEquals($this->from, $this->comment->setFrom($this->from));
        $this->setExpectedException('Exposure\Model\CommentOnProjectOwnerException', 
                CommentOnProjectOwner::EXCEPTION_INVALID_PROJECT_OWNER);
        $this->comment->validate();
    }
        
    public function testValidate() {
        $this->assertEquals(self::STATUS, $this->comment->setStatus(self::STATUS));
        $this->assertEquals(self::CONTENT, $this->comment->setContent(self::CONTENT));
        $this->assertEquals($this->dateTime, $this->comment->setDateTime($this->dateTime));
        $this->assertEquals($this->from, $this->comment->setFrom($this->from));
        $this->assertEquals($this->projectOwner, $this->comment->setProjectOwner($this->projectOwner));
        $this->comment->validate();
        $this->assertEquals(self::RATING, $this->comment->setRating(self::RATING));
        $this->comment->validate();
    }

    public function tearDown() {
        unset($this->comment);
    }

}

?>
