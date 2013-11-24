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

use Exposure\Model\CommentOnSponsorReturn,
    Exposure\Model\Comment,
    Exposure\Model\User,
    Exposure\Model\SponsorReturn;

class CommentOnSponsorReturnTest extends \PHPUnit_Framework_TestCase {

    protected $comment;
    const STATUS = Comment::STATUS_PUBLISHED;
    protected $dateTime;
    const CONTENT = 'content';
    const RATING = 4;
    protected $from;
    protected $sponsorReturn;

    public function setUp() {
        $this->comment = new CommentOnSponsorReturn();
        
        $this->dateTime = new \DateTime();
        $this->from = new User();
        $this->sponsorReturn = new SponsorReturn();    
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\CommentOnSponsorReturn', 
                $this->comment);
        $this->assertEquals(Comment::TYPE_COMMENT_ON_SPONSOR_RETURN, 
                $this->comment->getType());
    }
    
    public function testValidate() {
        $this->assertEquals(self::STATUS, $this->comment->setStatus(self::STATUS));
        $this->assertEquals($this->dateTime, $this->comment->setDateTime($this->dateTime));
        $this->assertEquals(self::CONTENT, $this->comment->setContent(self::CONTENT));
        $this->assertEquals($this->from, $this->comment->setFrom($this->from));
        $this->assertEquals($this->sponsorReturn, $this->comment->setSponsorReturn($this->sponsorReturn));
        $this->comment->validate();
        $this->assertEquals(self::RATING, $this->comment->setRating(self::RATING));
        $this->comment->validate();
    }

    public function tearDown() {
        unset($this->comment);
    }

}

?>
