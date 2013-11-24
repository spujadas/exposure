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

use Exposure\Model\CommentOnSponsorOrganisation,
    Exposure\Model\Comment,
    Exposure\Model\SponsorOrganisation,
    Exposure\Model\User,
    Sociable\ODM\ObjectDocumentMapper;

class CommentOnSponsorOrganisationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $comment;
    protected static $id = null;
    const STATUS = Comment::STATUS_PUBLISHED;
    protected $dateTime;
    const CONTENT = 'content';
    const RATING = 4;

    protected $from;
    const FROM_EMAIL = 'zzzzzz@zzzzzz.com';
    
    protected $sponsorOrganisation;
    const SPONSOR_ORGANISATION_NAME = 'ZZZZZZ_org_name';
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();

        $this->comment = new CommentOnSponsorOrganisation;
        
        $this->dateTime = new \DateTime();
        $this->from = new User;
        $this->from->setEmail(self::FROM_EMAIL);
        
        $this->sponsorOrganisation = new SponsorOrganisation;
        $this->sponsorOrganisation->setName(self::SPONSOR_ORGANISATION_NAME);
        
        $this->comment = new CommentOnSponsorOrganisation;
        $this->comment->setStatus(self::STATUS);
        $this->comment->setDateTime($this->dateTime);
        $this->comment->setContent(self::CONTENT);
        $this->comment->setFrom($this->from);
        $this->comment->setSponsorOrganisation($this->sponsorOrganisation);
        $this->comment->setRating(self::RATING);
        $this->comment->validate();

        self::$dm->persist($this->comment);
        self::$dm->persist($this->sponsorOrganisation);
        self::$dm->persist($this->from);
        self::$dm->flush();
        
        self::$id = $this->comment->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorOrganisation', self::$id);
        $this->assertNotNull($this->comment);
        $this->assertInstanceOf('Exposure\Model\CommentOnSponsorOrganisation', $this->comment);
    }
    
    public function testIsValid() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorOrganisation', self::$id);
        $this->comment->validate();
    }
   
    public function testIsEqual() {
        $comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorOrganisation', self::$id);
        $this->assertEquals($this->comment->getStatus(), $comment->getStatus());
        $this->assertEquals($this->comment->getContent(), $comment->getContent());
        $this->assertEquals($this->comment->getDateTime(), $comment->getDateTime());
        $this->assertEquals($this->comment->getRating(), $comment->getRating());
        $this->assertEquals(self::SPONSOR_ORGANISATION_NAME, $comment->getSponsorOrganisation()->getName());
        $this->assertEquals(self::FROM_EMAIL, $comment->getFrom()->getEmail());
        $this->assertEquals(self::$id, $comment->getSponsorOrganisation()->getComments()[0]->getId());
    }
   
    public function testRemove() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorOrganisation', self::$id);
        self::$dm->remove($this->comment);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorOrganisation', self::$id));
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
            $comment = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorOrganisation', self::$id);
            if(!is_null($comment)) {
                self::$dm->remove($comment);
            }
        }
        $sponsorOrganisation = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::SPONSOR_ORGANISATION_NAME);
        if(!is_null($sponsorOrganisation)) {
            self::$dm->remove($sponsorOrganisation);
        }
        $from = ObjectDocumentMapper::getByEmail(self::$dm, 'Exposure\Model\User', self::FROM_EMAIL);
        if(!is_null($from)) {
            self::$dm->remove($from);
        }
        self::$dm->flush();
    }
}

?>
