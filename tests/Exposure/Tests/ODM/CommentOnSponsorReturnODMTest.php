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

use Exposure\Model\CommentOnSponsorReturn,
    Exposure\Model\Comment,
    Exposure\Model\SponsorReturn,
    Exposure\Model\User,
    Sociable\ODM\ObjectDocumentMapper;

class CommentOnSponsorReturnODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $comment;
    protected static $id = null;
    const STATUS = Comment::STATUS_PUBLISHED;
    protected $dateTime;
    const CONTENT = 'content';
    const RATING = 4;

    protected $from;
    const FROM_EMAIL = 'zzzzzz@zzzzzz.com';
    
    protected $sponsorReturn;
    protected static $sponsorReturnId = null;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();

        $this->comment = new CommentOnSponsorReturn;
        
        $this->dateTime = new \DateTime();
        $this->from = new User;
        $this->from->setEmail(self::FROM_EMAIL);
        
        $this->sponsorReturn = new SponsorReturn;
        
        $this->comment = new CommentOnSponsorReturn;
        $this->comment->setStatus(self::STATUS);
        $this->comment->setDateTime($this->dateTime);
        $this->comment->setContent(self::CONTENT);
        $this->comment->setFrom($this->from);
        $this->comment->setSponsorReturn($this->sponsorReturn);
        $this->comment->setRating(self::RATING);
        $this->comment->validate();

        self::$dm->persist($this->comment);
        self::$dm->persist($this->sponsorReturn);
        self::$dm->persist($this->from);
        self::$dm->flush();
        
        self::$id = $this->comment->getId();
        self::$sponsorReturnId = $this->sponsorReturn->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorReturn', self::$id);
        $this->assertNotNull($this->comment);
        $this->assertInstanceOf('Exposure\Model\CommentOnSponsorReturn', $this->comment);
    }
    
    public function testIsValid() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorReturn', self::$id);
        $this->comment->validate();
    }
   
    public function testIsEqual() {
        $comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorReturn', self::$id);
        $this->assertEquals($this->comment->getStatus(), $comment->getStatus());
        $this->assertEquals($this->comment->getContent(), $comment->getContent());
        $this->assertEquals($this->comment->getDateTime(), $comment->getDateTime());
        $this->assertEquals($this->comment->getRating(), $comment->getRating());
        $this->assertEquals(self::$sponsorReturnId, $comment->getSponsorReturn()->getId());
        $this->assertEquals(self::FROM_EMAIL, $comment->getFrom()->getEmail());
        $this->assertEquals(self::$id, $comment->getSponsorReturn()->getComments()[0]->getId());
    }
   
    public function testRemove() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorReturn', self::$id);
        self::$dm->remove($this->comment);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorReturn', self::$id));
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
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnSponsorReturn', self::$id);
            if(!is_null($comment)) {
                self::$dm->remove($comment);
            }
        }
        if (!is_null(self::$sponsorReturnId)) {
            $sponsorReturn = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$sponsorReturnId);
            if(!is_null($sponsorReturn)) {
                self::$dm->remove($sponsorReturn);
            }
        }
        $from = ObjectDocumentMapper::getByEmail(self::$dm, 'Exposure\Model\User', self::FROM_EMAIL);
        if(!is_null($from)) {
            self::$dm->remove($from);
        }
        self::$dm->flush();
    }
}

?>
