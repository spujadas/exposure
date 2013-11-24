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

use Exposure\Model\Theme,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator,
    Exposure\Model\ThemeException;

class ThemeTest extends \PHPUnit_Framework_TestCase {

    protected $theme;
    
    const LABEL = 'label';
    
    protected $name;
    protected $name_empty;
    protected $name_toolong;
    
    protected $parentTheme;
    
    public function setUp() {
        $this->theme = new Theme();

        $this->name = new MultiLanguageString('ZZZZZ', 'fr');
        $this->name_toolong = new MultiLanguageString(
                str_repeat('a', Theme::NAME_MAX_LENGTH + 1),
                'fr');
        $this->name_empty = new MultiLanguageString('', 'fr');
        
        $this->parentTheme = new Theme();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\Theme', $this->theme);
    }
    
    public function testSetLabel_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->theme->setLabel(array());
    }
    
    public function testGetLabel_notastring() {
        try {
            $this->theme->setLabel(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->theme->getLabel());
    }
    
    public function testSetLabel_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->theme->setLabel('');
    }
    
    public function testGetLabel_empty() {
        try {
            $this->theme->setLabel('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->theme->getLabel());
    }
    
    public function testSetLabel_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', StringValidator::EXCEPTION_TOO_LONG);
        $this->theme->setLabel(str_repeat('a', Theme::LABEL_MAX_LENGTH + 1));
    }
    
    public function testGetLabel_toolong() {
        try {
            $this->theme->setLabel(str_repeat('a', Theme::LABEL_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->theme->getLabel());
    }
    
    public function testSetGetLabel() {
        $this->assertEquals(self::LABEL, 
                $this->theme->setLabel(self::LABEL));
        $this->assertEquals(self::LABEL, $this->theme->getLabel());
    }

    
    public function testSetName_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->theme->setName($this->name_empty);
    }
    
    public function testGetName_empty() {
        try {
            $this->theme->setName($this->name_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->theme->getName());
    }
    
    public function testSetName_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->theme->setName($this->name_toolong);
    }
    
    public function testGetName_toolong() {
        try {
            $this->theme->setName($this->name_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->theme->getName());
    }
    
    public function testSetGetName() {
        $this->assertEquals($this->name, 
                $this->theme->setName($this->name));
        $this->assertEquals($this->name, $this->theme->getName());
    }
    
    public function testSetParentTheme_toodeep() {
        $grandParentTheme = new Theme();
        $this->parentTheme->setParentTheme($grandParentTheme);
        $this->setExpectedException('Exposure\Model\ThemeException', 
            Theme::EXCEPTION_TOO_DEEP);
        $this->theme->setParentTheme($this->parentTheme);
    }
    
    public function testSetGetParentTheme() {
        $this->theme->setParentTheme(null);
        $this->assertNull($this->theme->getParentTheme());
        $this->assertEquals($this->parentTheme, 
                $this->theme->setParentTheme($this->parentTheme));
        $this->assertEquals($this->parentTheme, 
                $this->theme->getParentTheme());
    }
    
    public function testValidate_uninitialised() {
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_NOT_A_STRING);
        $this->theme->validate();
    }

    public function testValidate() {
        $this->theme->setName($this->name);
        $this->theme->setLabel(self::LABEL);
        $this->theme->setParentTheme($this->parentTheme);
        $this->theme->validate();
    }

    public function tearDown() {
        unset($this->theme);
    }

}

?>
