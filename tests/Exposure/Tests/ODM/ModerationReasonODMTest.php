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

use Exposure\Model\ModerationReason,
    Sociable\Model\MultiLanguageString,
    Sociable\ODM\ObjectDocumentMapper;

class ModerationReasonODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $moderationReason;

    const CODE = 'ZZZZZ';
    protected $content;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanup();
        
        $this->content = new MultiLanguageString('ZZZZZ', 'fr');
        $this->moderationReason = new ModerationReason();
        $this->moderationReason->setCode(self::CODE);
        $this->moderationReason->setContent($this->content);
        $this->moderationReason->validate();
        
        self::$dm->persist($this->moderationReason);
        self::$dm->flush();

        self::$dm->clear();
    }

    public function testFound() {
        $this->moderationReason = ObjectDocumentMapper::getByCode(self::$dm, 
            'Exposure\Model\ModerationReason', self::CODE);
        $this->assertNotNull($this->moderationReason);
    }
    
    public function testIsValid() {
        $this->moderationReason = ObjectDocumentMapper::getByCode(self::$dm, 
            'Exposure\Model\ModerationReason', self::CODE);
        $this->moderationReason->validate();
    }
   
    public function testIsEqual() {
        $moderationReason = ObjectDocumentMapper::getByCode(self::$dm, 
            'Exposure\Model\ModerationReason', self::CODE);
        $this->assertEquals($this->moderationReason, $moderationReason);
    }
   
    public function testRemove() {
        $this->moderationReason = ObjectDocumentMapper::getByCode(self::$dm, 
            'Exposure\Model\ModerationReason', self::CODE);
        self::$dm->remove($this->moderationReason);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getByCode(self::$dm, 
            'Exposure\Model\ModerationReason', self::CODE));
    }
    
    public function testDuplicate() {
        $this->content = new MultiLanguageString('ZZZZZ', 'fr');
        $this->moderationReason = new ModerationReason();
        $this->moderationReason->setCode(self::CODE);
        $this->moderationReason->setContent($this->content);
        $this->moderationReason->validate();

        self::$dm->persist($this->moderationReason);
        
        $this->setExpectedException('MongoCursorException');
        self::$dm->flush();
    }

    public function tearDown() {
        self::cleanup();
    }
    
    public static function tearDownAfterClass() {
        self::cleanup();
    }
    
    public static function cleanUp() {
        self::$dm->clear();
        $moderationReason = ObjectDocumentMapper::getByCode(self::$dm, 
            'Exposure\Model\ModerationReason', self::CODE);
        if(!is_null($moderationReason)) {
            self::$dm->remove($moderationReason);
            self::$dm->flush();
        }
    }

}

?>
