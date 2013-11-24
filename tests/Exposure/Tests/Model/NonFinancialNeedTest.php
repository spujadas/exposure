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

use Exposure\Model\NonFinancialNeed,
    Exposure\Model\SponsorReturn,
    Exposure\Model\ProjectNeed,
    Exposure\Model\SponsorContribution,
    Exposure\Model\Project,
    Sociable\Model\MultiLanguageString;

class NonFinancialNeedTest extends \PHPUnit_Framework_TestCase {
    protected $nonFinancialNeed;
    const TYPE = ProjectNeed::TYPE_SERVICE;
    const TYPE_INVALID = 'rubbish';
    protected $description;
    protected $return;
    protected $contribution;
    protected $project;
    
    public function setUp() {
        $this->nonFinancialNeed = new NonFinancialNeed;
        $this->description = new MultiLanguageString('description', 'fr');
        $this->return = new SponsorReturn;
        $this->contribution = new SponsorContribution;
        $this->project = new Project;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\NonFinancialNeed', $this->nonFinancialNeed);
    }
    
    public function testSetType_null() {
        $this->setExpectedException('Exposure\Model\ProjectNeedException', 
            ProjectNeed::EXCEPTION_INVALID_TYPE);
        $this->nonFinancialNeed->setType(null);
    }
    
    public function testGetType_null() {
        try {
            $this->nonFinancialNeed->setType(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->nonFinancialNeed->getType());
    }
    
    public function testSetType_invalidtype() {
        $this->setExpectedException('Exposure\Model\ProjectNeedException', 
            ProjectNeed::EXCEPTION_INVALID_TYPE);
        $this->nonFinancialNeed->setType(self::TYPE_INVALID);
    }
    
    public function testGetType_invalidtype() {
        try {
            $this->nonFinancialNeed->setType(self::TYPE_INVALID);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->nonFinancialNeed->getType());
    }
    
    public function testSetGetType() {
        $this->assertEquals(self::TYPE, $this->nonFinancialNeed->setType(self::TYPE));
        $this->assertEquals(self::TYPE, $this->nonFinancialNeed->getType());
    }
    
    public function testSetGetProject() {
        $this->assertEquals($this->project, 
                $this->nonFinancialNeed->setProject($this->project));
        $this->assertEquals($this->project, 
                $this->nonFinancialNeed->getProject());
    }
    
    public function testSetGetReturn() {
        $this->assertEquals($this->return, 
                $this->nonFinancialNeed->setReturn($this->return));
        $this->assertEquals($this->return, $this->nonFinancialNeed->getReturn());
    }
    
    public function testSetGetContribution() {
        $this->assertEquals($this->contribution, 
                $this->nonFinancialNeed->setContribution($this->contribution));
        $this->assertEquals($this->contribution, $this->nonFinancialNeed->getContribution());
        $this->assertNull($this->nonFinancialNeed->setContribution());
        $this->assertNull($this->nonFinancialNeed->getContribution());
    }
    
    public function testValidate_missingtype() {
        $this->nonFinancialNeed->setDescription($this->description);
        $this->nonFinancialNeed->setProject($this->project);
        $this->nonFinancialNeed->setReturn($this->return);
        $this->setExpectedException('Exposure\Model\ProjectNeedException', 
            ProjectNeed::EXCEPTION_INVALID_TYPE);
        $this->nonFinancialNeed->validate();
    }
    
    public function testValidate_missingreturn() {
        $this->nonFinancialNeed->setType(self::TYPE);
        $this->nonFinancialNeed->setDescription($this->description);
        $this->nonFinancialNeed->setProject($this->project);
        $this->setExpectedException('Exposure\Model\NonFinancialNeedException', 
            NonFinancialNeed::EXCEPTION_INVALID_SPONSOR_RETURN);
        $this->nonFinancialNeed->validate();
    }
    
    public function testValidate_missingdescription() {
        $this->nonFinancialNeed->setType(self::TYPE);
        $this->nonFinancialNeed->setReturn($this->return);
        $this->nonFinancialNeed->setProject($this->project);
        $this->setExpectedException('Exposure\Model\ProjectNeedException', 
            NonFinancialNeed::EXCEPTION_MISSING_DESCRIPTION);
        $this->nonFinancialNeed->validate();
    }

    public function testValidate_missingproject() {
        $this->nonFinancialNeed->setType(self::TYPE);
        $this->nonFinancialNeed->setReturn($this->return);
        $this->nonFinancialNeed->setDescription($this->description);
        $this->setExpectedException('Exposure\Model\NonFinancialNeedException', 
            NonFinancialNeed::EXCEPTION_INVALID_PROJECT);
        $this->nonFinancialNeed->validate();
    }
    
    public function testValidate() {
        $this->nonFinancialNeed->setType(self::TYPE);
        $this->nonFinancialNeed->setDescription($this->description);
        $this->nonFinancialNeed->setReturn($this->return);
        $this->nonFinancialNeed->setProject($this->project);
        $this->nonFinancialNeed->validate();
        $this->nonFinancialNeed->setContribution($this->contribution);
        $this->nonFinancialNeed->validate();
    }

    public function testIsFulfilled() {
        $this->assertFalse($this->nonFinancialNeed->isFulfilled());
        $this->nonFinancialNeed->setContribution($this->contribution);
        $this->assertTrue($this->nonFinancialNeed->isFulfilled());
    }
    
    public function tearDown() {
        unset($this->nonFinancialNeed);
    }

}

?>
