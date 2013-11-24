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

use Exposure\Model\ApprovableLabelledImage,
    Exposure\Model\ApprovableContent,
    Exposure\Model\ModerationStatus,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\LabelledImage,
    Sociable\ODM\ObjectDocumentMapper;

class ApprovableLabelledImageTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;

    protected $approvableLabelledImage;
    protected static $id;

    protected $moderationStatus;
    
    protected $current;
    const CURRENT_PHOTO_FILE_NAME = 'current.png';
    protected static $currentId;
    
    protected $previous;
    const PREVIOUS_PHOTO_FILE_NAME = 'previous.png';
    protected static $previousId;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }

    public function setUp() {
        self::cleanUp();

        $this->approvableLabelledImage = new ApprovableLabelledImage;
        
        $this->moderationStatus = new ModerationStatus();
        $this->moderationStatus->setReasonCode('code');
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);

        $this->current = new LabelledImage();
        $this->current->setImageFile(__DIR__ . '/' . self::CURRENT_PHOTO_FILE_NAME);
        $this->current->setMime(LabelledImage::MIME_PNG);
        $this->current->setDescription(new MultiLanguageString('current', 'en'));
        
        $this->previous = new LabelledImage();
        $this->previous->setImageFile(__DIR__ . '/' . self::PREVIOUS_PHOTO_FILE_NAME);
        $this->previous->setMime(LabelledImage::MIME_PNG);
        $this->previous->setDescription(new MultiLanguageString('previous', 'en'));
        
        $this->approvableLabelledImage->setModerationStatus($this->moderationStatus);
        $this->approvableLabelledImage->setCurrent($this->current);
        $this->approvableLabelledImage->setPrevious($this->previous);
        
        $this->approvableLabelledImage->validate();
        
        self::$dm->persist($this->approvableLabelledImage);
        self::$dm->persist($this->current);
        self::$dm->persist($this->previous);
        
        self::$dm->flush();
        
        self::$id = $this->approvableLabelledImage->getId();
        self::$currentId = $this->current->getId();
        self::$previousId = $this->previous->getId();
        
        self::$dm->clear();
    }

    public function testFound() {
        $this->approvableLabelledImage = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ApprovableLabelledImage', self::$id);
        $this->assertNotNull($this->approvableLabelledImage);
    }
    
    public function testIsValid() {
        $this->approvableLabelledImage = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ApprovableLabelledImage', self::$id);
        $this->approvableLabelledImage->validate();
    }
   
    public function testIsEqual() {
        $this->approvableLabelledImage = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ApprovableLabelledImage', self::$id);
        
        $this->assertEquals($this->moderationStatus, $this->approvableLabelledImage->getModerationStatus());
        $this->assertEquals(sha1_file(__DIR__ . '/' . self::CURRENT_PHOTO_FILE_NAME), 
            sha1($this->approvableLabelledImage->getCurrent()->getImageFile()->getBytes()));
        $this->assertEquals(sha1_file(__DIR__ . '/' . self::PREVIOUS_PHOTO_FILE_NAME), 
            sha1($this->approvableLabelledImage->getPrevious()->getImageFile()->getBytes()));
        $this->assertEquals(self::$currentId, $this->approvableLabelledImage->getCurrent()->getId());
        $this->assertEquals(self::$previousId, $this->approvableLabelledImage->getPrevious()->getId());
    }
   
    public function testRemove() {
        $this->approvableLabelledImage = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ApprovableLabelledImage', self::$id);
        self::$dm->remove($this->approvableLabelledImage);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ApprovableLabelledImage', self::$id));
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
            $approvableLabelledImage = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ApprovableLabelledImage', self::$id);
            if(!is_null($approvableLabelledImage)) {
                self::$dm->remove($approvableLabelledImage);
            }
        }
        
        if (!is_null(self::$currentId)) {
            $current = ObjectDocumentMapper::getById(self::$dm, 'Sociable\Model\LabelledImage', self::$currentId);
            if(!is_null($current)) {
                self::$dm->remove($current);
            }
        }
        
        if (!is_null(self::$previousId)) {
            $previous = ObjectDocumentMapper::getById(self::$dm, 'Sociable\Model\LabelledImage', self::$previousId);
            if(!is_null($previous)) {
                self::$dm->remove($previous);
            }
        }
        
        self::$dm->flush();
    }

}

?>
