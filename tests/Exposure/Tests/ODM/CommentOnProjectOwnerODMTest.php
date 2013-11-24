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

use Exposure\Model\CommentOnProjectOwner,
    Exposure\Model\Comment,
    Exposure\Model\ProjectOwner,
    Exposure\Model\User,
    Sociable\ODM\ObjectDocumentMapper;

class CommentOnProjectOwnerODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $comment;
    protected static $id = null;
    const STATUS = Comment::STATUS_PUBLISHED;
    protected $dateTime;
    const CONTENT = 'content';
    const RATING = 4;

    protected $from;
    const FROM_EMAIL_ADDRESS = 'zzzzzzz@zzzzzz.com';
    
    protected $projectOwner;
    const PROJECT_OWNER_EMAIL_ADDRESS = 'yyyyyyy@yyyyyy.com';
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();

        $this->comment = new CommentOnProjectOwner;
        
        $this->dateTime = new \DateTime();
        $this->from = new User;
        $this->from->setEmail(self::FROM_EMAIL_ADDRESS);
        $this->projectOwner = new User;
        $this->projectOwner->setType(User::TYPE_PROJECT_OWNER);
        $this->projectOwner->setEmail(self::PROJECT_OWNER_EMAIL_ADDRESS);
        
        $this->comment = new CommentOnProjectOwner;
        $this->comment->setStatus(self::STATUS);
        $this->comment->setDateTime($this->dateTime);
        $this->comment->setContent(self::CONTENT);
        $this->comment->setFrom($this->from);
        $this->comment->setProjectOwner($this->projectOwner);
        $this->comment->setRating(self::RATING);
        $this->comment->validate();

        self::$dm->persist($this->comment);
        self::$dm->persist($this->projectOwner);
        self::$dm->persist($this->from);
        self::$dm->flush();
        
        self::$id = $this->comment->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnProjectOwner', self::$id);
        $this->assertNotNull($this->comment);
        $this->assertInstanceOf('Exposure\Model\CommentOnProjectOwner', $this->comment);
    }
    
    public function testIsValid() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnProjectOwner', self::$id);
        $this->comment->validate();
    }
   
    public function testIsEqual() {
        $comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnProjectOwner', self::$id);
        $this->assertEquals($this->comment->getStatus(), $comment->getStatus());
        $this->assertEquals($this->comment->getContent(), $comment->getContent());
        $this->assertEquals($this->comment->getDateTime(), $comment->getDateTime());
        $this->assertEquals($this->comment->getRating(), $comment->getRating());
        $this->assertEquals(self::PROJECT_OWNER_EMAIL_ADDRESS, $comment->getProjectOwner()->getEmail());
        $this->assertEquals(self::FROM_EMAIL_ADDRESS, $comment->getFrom()->getEmail());
        $this->assertEquals(self::$id, $comment->getProjectOwner()->getCommentsOnProjectOwner()[0]->getId());
    }
   
    public function testRemove() {
        $this->comment = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnProjectOwner', self::$id);
        self::$dm->remove($this->comment);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnProjectOwner', self::$id));
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
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\CommentOnProjectOwner', self::$id);
            if(!is_null($comment)) {
                self::$dm->remove($comment);
            }
        }
        $projectOwner = ObjectDocumentMapper::getByEmail(self::$dm, 'Exposure\Model\User', self::PROJECT_OWNER_EMAIL_ADDRESS);
        if(!is_null($projectOwner)) {
                self::$dm->remove($projectOwner);
        }
        $from = ObjectDocumentMapper::getByEmail(self::$dm, 'Exposure\Model\User', self::FROM_EMAIL_ADDRESS);
        if(!is_null($from)) {
            self::$dm->remove($from);
        }
        self::$dm->flush();
    }
}

?>
