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

use Exposure\Model\SponsorReturn,
    Exposure\Model\SponsorReturnType,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator;

class SponsorReturnTest extends \PHPUnit_Framework_TestCase {
    protected $sponsorReturn;
    protected $description;
    protected $description_toolong;
    protected $description_empty;
    protected $type;
    const STATUS = SponsorReturn::STATUS_APPROVED;

    public function setUp() {
        $this->sponsorReturn = new SponsorReturn;
        
        $this->description = new MultiLanguageString('foo', 'fr');
        $this->description_toolong = new MultiLanguageString(
                str_repeat('a', SponsorReturn::DESCRIPTION_MAX_LENGTH + 1),
                'fr');
        $this->description_empty = new MultiLanguageString('', 'fr');

        $this->type = new SponsorReturnType;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SponsorReturn', $this->sponsorReturn);
    }
    
    public function testSetDescription_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->sponsorReturn->setDescription($this->description_empty);
    }
    
    public function testGetDescription_empty() {
        try {
            $this->sponsorReturn->setDescription($this->description_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorReturn->getDescription());
    }
    
    public function testSetDescription_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->sponsorReturn->setDescription($this->description_toolong);
    }
    
    public function testGetDescription_toolong() {
        try {
            $this->sponsorReturn->setDescription($this->description_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorReturn->getDescription());
    }
    
    public function testSetGetDescription() {
        $this->assertEquals($this->description, 
                $this->sponsorReturn->setDescription($this->description));
        $this->assertEquals($this->description, $this->sponsorReturn->getDescription());
    }
            
    public function testSetGetType() {
        $this->assertEquals($this->type, 
                $this->sponsorReturn->setType($this->type));
        $this->assertEquals($this->type, $this->sponsorReturn->getType());
    }
    
    public function testSetStatus_invalidstatus() {
        $this->setExpectedException('Exposure\Model\SponsorReturnException', 
            SponsorReturn::EXCEPTION_INVALID_STATUS);
        $this->sponsorReturn->setStatus(null);
    }
    
    public function testGetStatus_invalidstatus() {
        try {
            $this->sponsorReturn->setStatus(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorReturn->getStatus());
    }
    
    public function testSetGetStatus() {
        $this->assertEquals(self::STATUS, $this->sponsorReturn->setStatus(self::STATUS));
        $this->assertEquals(self::STATUS, $this->sponsorReturn->getStatus());
    }
            
    public function testValidate_missingstatus() {
        $this->sponsorReturn->setDescription($this->description);
        $this->sponsorReturn->setType($this->type);
        $this->setExpectedException('Exposure\Model\SponsorReturnException', 
            SponsorReturn::EXCEPTION_INVALID_STATUS);
        $this->sponsorReturn->validate();
    }
    
    public function testValidate_missingtype() {
        $this->sponsorReturn->setDescription($this->description);
        $this->sponsorReturn->setStatus(self::STATUS);
        $this->setExpectedException('Exposure\Model\SponsorReturnException', 
            SponsorReturn::EXCEPTION_INVALID_TYPE);
        $this->sponsorReturn->validate();
    }
    
    public function testValidate_missingdescription() {
        $this->sponsorReturn->setType($this->type);
        $this->sponsorReturn->setStatus(self::STATUS);
        $this->setExpectedException('Exposure\Model\SponsorReturnException', 
            SponsorReturn::EXCEPTION_INVALID_DESCRIPTION);
        $this->sponsorReturn->validate();
    }
    
    public function testValidate() {
        $this->sponsorReturn->setDescription($this->description);
        $this->sponsorReturn->setType($this->type);
        $this->sponsorReturn->setStatus(self::STATUS);
        $this->sponsorReturn->validate();
    }
    
    public function tearDown() {
        unset($this->sponsorReturn);
    }

}

?>
