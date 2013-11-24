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

use Exposure\Model\SponsorReturnType,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator;

class SponsorReturnTypeTest extends \PHPUnit_Framework_TestCase {
    protected $sponsorReturnType;
    protected $description;
    protected $description_toolong;
    protected $description_empty;
    const LABEL = 'label';

    public function setUp() {
        $this->sponsorReturnType = new SponsorReturnType;
        
        $this->description = new MultiLanguageString('foo', 'fr');
        $this->description_toolong = new MultiLanguageString(
                str_repeat('a', SponsorReturnType::DESCRIPTION_MAX_LENGTH + 1),
                'fr');
        $this->description_empty = new MultiLanguageString('', 'fr');

    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SponsorReturnType', $this->sponsorReturnType);
    }
    
    public function testSetDescription_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->sponsorReturnType->setDescription($this->description_empty);
    }
    
    public function testGetDescription_empty() {
        try {
            $this->sponsorReturnType->setDescription($this->description_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorReturnType->getDescription());
    }
    
    public function testSetDescription_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->sponsorReturnType->setDescription($this->description_toolong);
    }
    
    public function testGetDescription_toolong() {
        try {
            $this->sponsorReturnType->setDescription($this->description_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorReturnType->getDescription());
    }
    
    public function testSetGetDescription() {
        $this->assertEquals($this->description, 
                $this->sponsorReturnType->setDescription($this->description));
        $this->assertEquals($this->description, $this->sponsorReturnType->getDescription());
    }
    
    public function testSetLabel_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->sponsorReturnType->setLabel(array());
    }
    
    public function testGetLabel_notastring() {
        try {
            $this->sponsorReturnType->setLabel(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorReturnType->getLabel());
    }
    
    public function testSetLabel_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->sponsorReturnType->setLabel('');
    }
    
    public function testGetLabel_empty() {
        try {
            $this->sponsorReturnType->setLabel('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorReturnType->getLabel());
    }
    
    public function testSetLabel_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', StringValidator::EXCEPTION_TOO_LONG);
        $this->sponsorReturnType->setLabel(str_repeat('a', SponsorReturnType::LABEL_MAX_LENGTH + 1));
    }
    
    public function testGetLabel_toolong() {
        try {
            $this->sponsorReturnType->setLabel(str_repeat('a', SponsorReturnType::LABEL_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorReturnType->getLabel());
    }
    
    public function testSetGetLabel() {
        $this->assertEquals(self::LABEL, 
                $this->sponsorReturnType->setLabel(self::LABEL));
        $this->assertEquals(self::LABEL, $this->sponsorReturnType->getLabel());
    }
            
    public function testValidate_uninitialised() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->sponsorReturnType->validate();
    }
    
    public function testValidate() {
        $this->sponsorReturnType->setDescription($this->description);
        $this->sponsorReturnType->setLabel(self::LABEL);
        $this->sponsorReturnType->validate();
    }
    
    public function tearDown() {
        unset($this->sponsorReturnType);
    }

}

?>
