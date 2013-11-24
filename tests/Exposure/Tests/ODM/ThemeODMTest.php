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

use Exposure\Model\Theme,
    Sociable\Model\MultiLanguageString,
    Exposure\ODM\ObjectDocumentMapper;

class ThemeODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $parentTheme;
    const PARENT_LABEL = 'ZZZZZ_PARENT';
    protected $parentName;
    
    protected $theme;
    const LABEL = 'ZZZZZ_CHILD';
    protected $name;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanup();
        
        $this->parentTheme = new Theme();
        $this->parentName = new MultiLanguageString('ZZZZZ_parent', 'fr');
        $this->parentTheme->setName($this->parentName);
        $this->parentTheme->setLabel(self::PARENT_LABEL);
        $this->parentTheme->validate();
        
        $this->theme = new Theme();
        $this->name = new MultiLanguageString('ZZZZZ_name', 'fr');
        $this->theme->setName($this->name);
        $this->theme->setLabel(self::LABEL);
        $this->theme->setParentTheme($this->parentTheme);
        $this->theme->validate();
        
        self::$dm->persist($this->parentTheme);
        self::$dm->persist($this->theme);
        self::$dm->flush();

        self::$dm->clear();
    }

    public function testExists() {
        $parentThemes = ObjectDocumentMapper::getRootThemes(self::$dm);
        $this->assertTrue($parentThemes->count() > 0);
    }
    
    public function testIsValid() {
        $parentThemes = ObjectDocumentMapper::getRootThemes(self::$dm);
        foreach ($parentThemes as $parentTheme) {
            $parentTheme->validate();
        }
    }
   
    public function testIsEqual() {
        $parentThemes = ObjectDocumentMapper::getRootThemes(self::$dm);
        foreach ($parentThemes as $parentTheme) {
            if ($parentTheme->getLabel() == self::PARENT_LABEL) {break;}
        }
        
        $this->assertEquals($this->parentTheme->getLabel(), $parentTheme->getLabel());
        $this->assertEquals($this->parentTheme->getName()->getStringByLanguageCode('fr'), 
                $parentTheme->getName()->getStringByLanguageCode('fr'));
        $this->assertEquals(1, $parentTheme->getChildrenThemes()->count());
        $this->assertEquals($this->parentTheme->getParentTheme(), $parentTheme->getParentTheme());
    }
   
    public function testRemove_haschildren() {
        $parentTheme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::PARENT_LABEL);
        $this->setExpectedException('Exposure\Model\ThemeException', 
                Theme::EXCEPTION_HAS_CHILDREN);
        self::$dm->remove($parentTheme);
    }

    public function testRemove() {
        $parentTheme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::PARENT_LABEL);
        foreach ($parentTheme->getChildrenThemes() as $theme) {
            self::$dm->remove($theme);
        }
        self::$dm->flush();
        self::$dm->clear();

        $count = ObjectDocumentMapper::getRootThemes(self::$dm)->count();
        $parentTheme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::PARENT_LABEL);
        $this->assertEquals(0, $parentTheme->getChildrenThemes()->count());
        self::$dm->remove($parentTheme);
        self::$dm->flush();
    
        $parentThemes = ObjectDocumentMapper::getRootThemes(self::$dm);
        $this->assertEquals($count - 1, $parentThemes->count());
    }
    
    public function testDuplicate_parent() {
        $this->parentTheme = new Theme();
        $this->parentName = new MultiLanguageString('ZZZZZ_parent', 'fr');
        $this->parentTheme->setName($this->parentName);
        $this->parentTheme->setLabel(self::PARENT_LABEL);
        
        self::$dm->persist($this->parentTheme);
        
        $this->setExpectedException('MongoCursorException');
        self::$dm->flush();
    }

    public function testDuplicate_child() {
        $parentTheme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::PARENT_LABEL);
        
        $this->theme = new Theme();
        $this->name = new MultiLanguageString('ZZZZZ_name', 'fr');
        $this->theme->setName($this->name);
        $this->theme->setLabel(self::LABEL);
        $this->theme->setParentTheme($parentTheme);

        self::$dm->persist($this->theme);
        
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
        $theme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::LABEL);
        if (!is_null($theme)) {
            self::$dm->remove($theme);
        }
        self::$dm->flush();
        $parentTheme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::PARENT_LABEL);
        if (!is_null($parentTheme)) {
            self::$dm->remove($parentTheme);
        }
        self::$dm->flush();
    }

}

?>
