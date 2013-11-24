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

use Exposure\Model\CommentOnSponsorOrganisation,
    Exposure\Model\Comment,
    Exposure\Model\User,
    Exposure\Model\SponsorOrganisation;

class CommentOnSponsorOrganisationTest extends \PHPUnit_Framework_TestCase {

    protected $comment;
    const STATUS = Comment::STATUS_PUBLISHED;
    protected $dateTime;
    const CONTENT = 'content';
    const RATING = 4;
    protected $from;
    protected $sponsorOrganisation;

    public function setUp() {
        $this->comment = new CommentOnSponsorOrganisation();
        
        $this->dateTime = new \DateTime();
        $this->from = new User();
        $this->sponsorOrganisation = new SponsorOrganisation();
        
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\CommentOnSponsorOrganisation', $this->comment);
        $this->assertEquals(Comment::TYPE_COMMENT_ON_SPONSOR_ORGANISATION, $this->comment->getType());
    }
            
    public function testSetGetSponsorOrganisation() {
        $this->assertEquals($this->sponsorOrganisation, $this->comment->setSponsorOrganisation($this->sponsorOrganisation));
        $this->assertEquals($this->sponsorOrganisation, $this->comment->getSponsorOrganisation());
    }

    public function testValidate() {
        $this->assertEquals(self::STATUS, $this->comment->setStatus(self::STATUS));
        $this->assertEquals($this->dateTime, $this->comment->setDateTime($this->dateTime));
        $this->assertEquals(self::CONTENT, $this->comment->setContent(self::CONTENT));
        $this->assertEquals($this->from, $this->comment->setFrom($this->from));
        $this->assertEquals($this->sponsorOrganisation, $this->comment->setSponsorOrganisation($this->sponsorOrganisation));
        $this->comment->validate();
        $this->assertEquals(self::RATING, $this->comment->setRating(self::RATING));
        $this->comment->validate();
    }

    public function tearDown() {
        unset($this->comment);
    }

}

?>
