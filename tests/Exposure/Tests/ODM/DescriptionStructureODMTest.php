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

use Exposure\Model\DescriptionStructure,
    Exposure\Model\Theme,
    Sociable\ODM\ObjectDocumentMapper,
    Sociable\Model\MultiLanguageString;

class DescriptionStructureODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $descriptionStructure;
    protected static $id = null;
    protected $sectionTitle;
    
    protected $theme;
    const THEME_LABEL = 'ZZZZZ';
    const THEME_NAME_STRING = 'ZZZZZ';
    const THEME_NAME_LANGUAGE = 'fr';
    protected $themeName;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->theme = new Theme;
        $this->theme->setLabel(self::THEME_LABEL);
        $this->themeName = new MultiLanguageString(self::THEME_NAME_STRING, 
                self::THEME_NAME_LANGUAGE);
        $this->theme->setName($this->themeName);
        
        $this->descriptionStructure = new DescriptionStructure;
        
        $this->sectionTitle = new MultiLanguageString('sectionTitle', 'fr');
        $this->descriptionStructure->addSectionTitle($this->sectionTitle);
        $this->descriptionStructure->setTheme($this->theme);
        $this->descriptionStructure->validate();

        self::$dm->persist($this->descriptionStructure);
        self::$dm->persist($this->theme);
        self::$dm->flush();
        
        self::$id = $this->descriptionStructure->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->descriptionStructure = 
                ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\DescriptionStructure', self::$id);
        $this->assertNotNull($this->descriptionStructure);
        $this->assertInstanceOf('Exposure\Model\DescriptionStructure', 
            $this->descriptionStructure);
    }
    
    public function testIsValid() {
        $this->descriptionStructure = 
                ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\DescriptionStructure', self::$id);
        $this->descriptionStructure->validate();
    }
   
    public function testIsEqual() {
        $descriptionStructure = 
                ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\DescriptionStructure', self::$id);
        $sectionTitles = $this->descriptionStructure->getSectionTitles();
        $this->assertEquals(1, $sectionTitles->count());
        $this->assertTrue($sectionTitles->contains($this->sectionTitle));
        $this->assertEquals(self::THEME_LABEL, $descriptionStructure->getTheme()->getLabel());
        $this->assertEquals($this->theme->getName()
                ->getStringByLanguageCode(self::THEME_NAME_LANGUAGE), 
                $descriptionStructure->getTheme()->getName()
                ->getStringByLanguageCode(self::THEME_NAME_LANGUAGE)
                );
    }
   
    public function testRemove() {
        $this->descriptionStructure = 
                ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\DescriptionStructure', self::$id);
        self::$dm->remove($this->descriptionStructure);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 
            'Exposure\Model\DescriptionStructure', self::$id));
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
            $descriptionStructure = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\DescriptionStructure', self::$id);
            if(!is_null($descriptionStructure)) {
                self::$dm->remove($descriptionStructure);
            }
        }
        $theme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::THEME_LABEL);
        if(!is_null($theme)) {
            self::$dm->remove($theme);
        }
        self::$dm->flush();
    }
}

?>
