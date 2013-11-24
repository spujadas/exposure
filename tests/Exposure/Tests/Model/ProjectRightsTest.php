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

use Exposure\Model\ProjectRights,
    Sociable\Utility\NumberValidator;

class ProjectRightsTest extends \PHPUnit_Framework_TestCase {

    protected $projectRights;
    const NUMBER_DISPLAYED_PHOTOS = 3;

    public function setUp() {
        $this->projectRights = new ProjectRights();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ProjectRights', $this->projectRights);
    }
    
    public function testSetGetDisplayDescription() {
        $this->assertTrue($this->projectRights->setDisplayDescription(true));
        $this->assertTrue($this->projectRights->getDisplayDescription());
        $this->assertFalse($this->projectRights->setDisplayDescription(false));
        $this->assertFalse($this->projectRights->getDisplayDescription());
        $this->assertFalse($this->projectRights->setDisplayDescription(null));
        $this->assertFalse($this->projectRights->getDisplayDescription());
    }
    
    public function testSetNumberDisplayedPhotos_notanumber() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_A_NUMBER);
        $this->projectRights->setNumberDisplayedPhotos(array());
    }
    
    public function testGetNumberDisplayedPhotos_notanumber() {
        try {
            $this->projectRights->setNumberDisplayedPhotos(array());
        }
        catch (\Exception $e) {}
        $this->assertEquals(1, $this->projectRights->getNumberDisplayedPhotos());
    }
    
    public function testSetNumberDisplayedPhotos_notaninteger() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_AN_INTEGER);
        $this->projectRights->setNumberDisplayedPhotos(1.2);
    }
    
    public function testGetNumberDisplayedPhotos_notaninteger() {
        try {
            $this->projectRights->setNumberDisplayedPhotos(1.2);
        }
        catch (\Exception $e) {}
        $this->assertEquals(1, $this->projectRights->getNumberDisplayedPhotos());
    }
    
    public function testSetNumberDisplayedPhotos_notpositive() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_POSITIVE);
        $this->projectRights->setNumberDisplayedPhotos(-1);
    }
    
    public function testGetNumberDisplayedPhotos_notpositive() {
        try {
            $this->projectRights->setNumberDisplayedPhotos(-1);
        }
        catch (\Exception $e) {}
        $this->assertEquals(1, $this->projectRights->getNumberDisplayedPhotos());
    }
    
    public function testSetNumberDisplayedPhotos_toolarge() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_TOO_LARGE);
        $this->projectRights->setNumberDisplayedPhotos(ProjectRights::NUMBER_DISPLAYED_PHOTOS_MAX + 1);
    }
    
    public function testGetNumberDisplayedPhotos_toolarge() {
        try {
            $this->projectRights->setNumberDisplayedPhotos(ProjectRights::NUMBER_DISPLAYED_PHOTOS_MAX + 1);
        }
        catch (\Exception $e) {}
        $this->assertEquals(1, $this->projectRights->getNumberDisplayedPhotos());
    }
    
    public function testSetGetNumberDisplayedPhotos() {
        $this->assertEquals(self::NUMBER_DISPLAYED_PHOTOS,
                $this->projectRights->setNumberDisplayedPhotos(self::NUMBER_DISPLAYED_PHOTOS));
        $this->assertEquals(self::NUMBER_DISPLAYED_PHOTOS,
                $this->projectRights->getNumberDisplayedPhotos());
    }
    
    public function testSetGetDisplayWebPresence() {
        $this->assertTrue($this->projectRights->setDisplayWebPresence(true));
        $this->assertTrue($this->projectRights->getDisplayWebPresence());
        $this->assertFalse($this->projectRights->setDisplayWebPresence(false));
        $this->assertFalse($this->projectRights->getDisplayWebPresence());
        $this->assertFalse($this->projectRights->setDisplayWebPresence(null));
        $this->assertFalse($this->projectRights->getDisplayWebPresence());
    }
    
    public function tearDown() {
        unset($this->projectRights);
    }

}

?>
