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

use Exposure\Model\CommentNotification,
    Exposure\Model\Comment,
    Exposure\Model\CommentOnProjectOwner,
    Exposure\Model\Notification,
    Exposure\Model\User,
    Exposure\Model\ModerationStatus,
    Sociable\Model\PasswordAuthenticator,
    Sociable\ODM\ObjectDocumentMapper;

class CommentNotificationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    protected static $id = null;
    protected static $commentId = null;
    
    protected $notification;
    protected $projectOwner;
    const PROJECT_OWNER_EMAIL = 'zzzzzz@zzzzzz.com';
    const PROJECT_OWNER_NAME = 'Foo';
    const PROJECT_OWNER_SURNAME = 'Bar';
    protected $authenticator;
    const PROJECT_OWNER_STATUS = User::STATUS_REGISTERED;
    protected $moderationStatus;
    protected $registrationDateTime;

    const STATUS = Notification::STATUS_ARCHIVED;
    protected $dateTime;
    const CONTENT = 'content';
    const EVENT = CommentNotification::EVENT_RECEIVED_COMMENT;
    protected $comment;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanup();
        
        $this->projectOwner = new User;
        $this->projectOwner->setEmail(self::PROJECT_OWNER_EMAIL);
        $this->projectOwner->setName(self::PROJECT_OWNER_NAME);
        $this->projectOwner->setSurname(self::PROJECT_OWNER_SURNAME);
        $this->authenticator = new PasswordAuthenticator();
        $this->authenticator->setParams(array('password' => '1234'));
        $this->projectOwner->setAuthenticator($this->authenticator);
        $this->projectOwner->setStatus(self::PROJECT_OWNER_STATUS);
        $this->moderationStatus = new ModerationStatus();
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);
        $this->moderationStatus->setReasonCode('s');
        $this->moderationStatus->setComment('comment');
        $this->registrationDateTime = new \DateTime();
        $this->projectOwner->setModerationStatus($this->moderationStatus);
        $this->projectOwner->setRegistrationDateTime($this->registrationDateTime);
        $this->projectOwner->setType(User::TYPE_PROJECT_OWNER);
        $this->projectOwner->validatePartial();
        
        $this->dateTime = new \DateTime();
        $this->comment = new CommentOnProjectOwner();
        $this->comment->setStatus(Comment::STATUS_PUBLISHED);
        $this->comment->setDateTime(new \DateTime);
        $this->comment->setFrom(new User);
        $this->comment->setContent('content');
        $this->comment->setProjectOwner($this->projectOwner);
        $this->comment->validate();
        
        $this->notification = new CommentNotification();
        $this->notification->setStatus(self::STATUS);
        $this->notification->setContent(self::CONTENT);
        $this->notification->setEvent(self::EVENT);
        $this->notification->setDateTime($this->dateTime);
        $this->notification->setComment($this->comment);
        $this->notification->validate();
        
        self::$dm->persist($this->notification);
        self::$dm->persist($this->comment);
        self::$dm->persist($this->projectOwner);
        self::$dm->flush();
        
        self::$id = $this->notification->getId();
        self::$commentId = $this->comment->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\CommentNotification', self::$id);
        $this->assertNotNull($this->notification);
        $this->assertInstanceOf('Exposure\Model\CommentNotification', $this->notification);
    }
    
    public function testIsValid() {
        $this->notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\CommentNotification', self::$id);
        $this->notification->validate();
    }
   
    public function testIsEqual() {
        $notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\CommentNotification', self::$id);
        $this->assertEquals(self::STATUS, $notification->getStatus());
        $this->assertEquals(self::CONTENT, $notification->getContent());
        $this->assertEquals(self::EVENT, $notification->getEvent());
        $this->assertEquals($this->dateTime, $notification->getDateTime());
        $this->assertEquals(Comment::TYPE_COMMENT_ON_PROJECT_OWNER, 
                $notification->getComment()->getType());
    }
   
    public function testRemove() {
        $this->notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\CommentNotification', self::$id);
        self::$dm->remove($this->notification);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\CommentNotification', self::$id));
    }
    
    public function tearDown() {
        self::cleanup();
    }
    
    public static function tearDownAfterClass() {
        self::cleanup();
    }
    
    public static function cleanUp() {
        self::$dm->clear();
        if (!is_null(self::$id)) {
            $notification = ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\CommentNotification', self::$id);
            if(!is_null($notification)) {
                self::$dm->remove($notification);
            }
        }
        if (!is_null(self::$commentId)) {
            $comment = ObjectDocumentMapper::getById(self::$dm, 
                'Exposure\Model\CommentOnProjectOwner', self::$commentId);
            if(!is_null($comment)) {
                self::$dm->remove($comment);
            }
        }
        $projectOwner = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\User', self::PROJECT_OWNER_EMAIL);
        if(!is_null($projectOwner)) {
            self::$dm->remove($projectOwner);
        }
        self::$dm->flush();
    }

}

?>
