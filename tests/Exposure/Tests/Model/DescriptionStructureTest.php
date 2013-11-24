<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Tests\Model;

use Exposure\Model\DescriptionStructure,
    Exposure\Model\Theme,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator;

class DescriptionStructureTest extends \PHPUnit_Framework_TestCase {
    protected $descriptionStructure;

    protected $sectionTitle;

    public function setUp() {
        $this->descriptionStructure = new DescriptionStructure();
        
        $this->sectionTitle = new MultiLanguageString('test', 'fr');
        $this->theme = new Theme();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\DescriptionStructure', $this->descriptionStructure);
        $this->assertEquals(0, $this->descriptionStructure->getSectionTitles()->count());
    }
    
    public function testAddSectionTitle_empty() {
        $sectionTitle = new MultiLanguageString('', 'fr');
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_EMPTY);
        $this->descriptionStructure->addSectionTitle($sectionTitle);
    }
    
    public function testAddSectionTitle_toolong() {
        $sectionTitle = new MultiLanguageString(
                str_repeat('a', DescriptionStructure::SECTION_TITLE_MAX_LENGTH + 1), 'fr');
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_TOO_LONG);
        $this->descriptionStructure->addSectionTitle($sectionTitle);
    }
    
    public function testAddSectionTitle() {
        $this->descriptionStructure->addSectionTitle($this->sectionTitle);
        $this->assertEquals(1, $this->descriptionStructure->getSectionTitles()->count());
    }
    
    public function testRemoveSectionTitle() {
        $this->assertEquals(0, $this->descriptionStructure->getSectionTitles()->count());
        $this->descriptionStructure->addSectionTitle($this->sectionTitle);
        $this->assertEquals(1, $this->descriptionStructure->getSectionTitles()->count());
        $dummySectionTitle = new MultiLanguageString('rubbish', 'fr');
        $this->assertFalse($this->descriptionStructure->removeSectionTitle($dummySectionTitle));
        $this->assertEquals(1, $this->descriptionStructure->getSectionTitles()->count());
        $this->assertTrue($this->descriptionStructure->removeSectionTitle($this->sectionTitle));
        $this->assertEquals(0, $this->descriptionStructure->getSectionTitles()->count());
    }
    
    public function testSetGetTheme() {
        $this->assertEquals($this->theme, $this->descriptionStructure->setTheme($this->theme));
        $this->assertEquals($this->theme, $this->descriptionStructure->getTheme());
        $this->assertNull($this->descriptionStructure->setTheme(null));
        $this->assertNull($this->descriptionStructure->getTheme());
    }
    
    public function testValidate() {
        $this->descriptionStructure->validate();
        $this->descriptionStructure->addSectionTitle($this->sectionTitle);
        $this->descriptionStructure->validate();
    }

    public function tearDown() {
        unset($this->descriptionStructure);
    }

}

?>
