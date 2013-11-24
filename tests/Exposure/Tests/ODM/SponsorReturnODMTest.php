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

use Exposure\Model\SponsorReturn,
    Exposure\Model\SponsorReturnType,
    Sociable\Model\MultiLanguageString,
    Sociable\ODM\ObjectDocumentMapper;

class SponsorReturnODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $sponsorReturn;
    protected static $id = null;
    
    protected $description;
    const DESCRIPTION_STRING = 'description';
    const DESCRIPTION_LANGUAGE = 'fr';
    
    protected $type;
    const TYPE_LABEL = 'ZZZZZZ_type_label';
    
    const STATUS = SponsorReturn::STATUS_APPROVED;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();

        $this->description = new MultiLanguageString(self::DESCRIPTION_STRING,
                self::DESCRIPTION_LANGUAGE);
        $this->type = new SponsorReturnType;
        $this->type->setLabel(self::TYPE_LABEL);
        
        $this->sponsorReturn = new SponsorReturn;
        $this->sponsorReturn->setDescription($this->description);
        $this->sponsorReturn->setType($this->type);
        $this->sponsorReturn->setStatus(self::STATUS);
        $this->sponsorReturn->validate();

        self::$dm->persist($this->sponsorReturn);
        self::$dm->persist($this->type);
        self::$dm->flush();
        
        self::$id = $this->sponsorReturn->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->sponsorReturn = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$id);
        $this->assertNotNull($this->sponsorReturn);
        $this->assertInstanceOf('Exposure\Model\SponsorReturn', $this->sponsorReturn);
    }
    
    public function testIsValid() {
        $this->sponsorReturn = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$id);
        $this->sponsorReturn->validate();
    }
   
    public function testIsEqual() {
        $sponsorReturn = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$id);
        $this->assertEquals(self::DESCRIPTION_STRING, $sponsorReturn
                ->getDescription()->getStringByLanguageCode(self::DESCRIPTION_LANGUAGE));
        $this->assertEquals(self::TYPE_LABEL, $sponsorReturn->getType()->getLabel());
        $this->assertEquals(self::STATUS, $sponsorReturn->getStatus());
    }
   
    public function testRemove() {
        $this->sponsorReturn = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$id);
        self::$dm->remove($this->sponsorReturn);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$id));
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
            $sponsorReturn = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$id);
            if(!is_null($sponsorReturn)) {
                self::$dm->remove($sponsorReturn);
            }
        }
        $type = ObjectDocumentMapper::getByLabel(self::$dm, 'Exposure\Model\SponsorReturnType', self::TYPE_LABEL);
        if(!is_null($type)) {
            self::$dm->remove($type);
        }
        self::$dm->flush();
    }
}

?>
