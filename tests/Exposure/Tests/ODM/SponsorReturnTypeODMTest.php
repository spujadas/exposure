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

use Exposure\Model\SponsorReturnType,
    Sociable\Model\MultiLanguageString,
    Sociable\ODM\ObjectDocumentMapper;

class SponsorReturnTypeODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $sponsorReturnType;
    protected static $id = null;    
    const TYPE_LABEL = 'ZZZZZZ_type_label';
    protected $description;
    const DESCRIPTION_STRING = 'description';
    const DESCRIPTION_LANGUAGE = 'fr';

    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();

        $this->description = new MultiLanguageString(self::DESCRIPTION_STRING,
                self::DESCRIPTION_LANGUAGE);
        
        $this->sponsorReturnType = new SponsorReturnType;
        $this->sponsorReturnType->setLabel(self::TYPE_LABEL);
        $this->sponsorReturnType->setDescription($this->description);
        $this->sponsorReturnType->validate();

        self::$dm->persist($this->sponsorReturnType);
        self::$dm->flush();
        
        self::$id = $this->sponsorReturnType->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->sponsorReturnType = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnType', self::$id);
        $this->assertNotNull($this->sponsorReturnType);
        $this->assertInstanceOf('Exposure\Model\SponsorReturnType', $this->sponsorReturnType);
    }
    
    public function testIsValid() {
        $this->sponsorReturnType = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnType', self::$id);
        $this->sponsorReturnType->validate();
    }
   
    public function testIsEqual() {
        $sponsorReturnType = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnType', self::$id);
        $this->assertEquals(self::DESCRIPTION_STRING, $sponsorReturnType
                ->getDescription()->getStringByLanguageCode(self::DESCRIPTION_LANGUAGE));
        $this->assertEquals(self::TYPE_LABEL, $sponsorReturnType->getLabel());
    }
   
    public function testRemove() {
        $this->sponsorReturnType = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnType', self::$id);
        self::$dm->remove($this->sponsorReturnType);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnType', self::$id));
    }
    
    public function tearDown() {
        self::cleanUp();
    }
    
    public static function tearDownAfterClass() {
        self::cleanUp();
    }

    public static function cleanUp() {
        self::$dm->clear();
        $sponsorReturnType = ObjectDocumentMapper::getByLabel(self::$dm, 'Exposure\Model\SponsorReturnType', self::TYPE_LABEL);
        if(!is_null($sponsorReturnType)) {
            self::$dm->remove($sponsorReturnType);
        }
        self::$dm->flush();
    }
}

?>
