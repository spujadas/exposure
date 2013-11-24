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

use Exposure\Model\ProjectNeed,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator;

class ProjectNeedTest extends \PHPUnit_Framework_TestCase {
    protected $projectNeed;
    protected $description;
    protected $description_toolong;
    protected $description_empty;

    public function setUp() {
        $this->projectNeed = $this->getMockForAbstractClass('Exposure\Model\ProjectNeed');
        
        $this->description = new MultiLanguageString('foo', 'fr');
        $this->description_toolong = new MultiLanguageString(
                str_repeat('a', ProjectNeed::DESCRIPTION_MAX_LENGTH + 1),
                'fr');
        $this->description_empty = new MultiLanguageString('', 'fr');

    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ProjectNeed', $this->projectNeed);
    }
    
    public function testSetDescription_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->projectNeed->setDescription($this->description_empty);
    }
    
    public function testGetDescription_empty() {
        try {
            $this->projectNeed->setDescription($this->description_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->projectNeed->getDescription());
    }
    
    public function testSetDescription_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->projectNeed->setDescription($this->description_toolong);
    }
    
    public function testGetDescription_toolong() {
        try {
            $this->projectNeed->setDescription($this->description_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->projectNeed->getDescription());
    }
    
    public function testSetGetDescription() {
        $this->assertEquals($this->description, 
                $this->projectNeed->setDescription($this->description));
        $this->assertEquals($this->description, $this->projectNeed->getDescription());
    }
        
    public function tearDown() {
        unset($this->projectNeed);
    }

}

?>
