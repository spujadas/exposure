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

use Exposure\Model\ProjectWant,
    Exposure\Model\Project,
    Exposure\Model\User,
    Exposure\Model\SponsorOrganisation;

class ProjectWantTest extends \PHPUnit_Framework_TestCase {

    protected $projectWant;
    protected $dateTime;
    protected $sponsorOrganisation;
    protected $project;

    public function setUp() {
        $this->projectWant = new ProjectWant();
        
        $this->dateTime = new \DateTime();
        $this->sponsorOrganisation = new SponsorOrganisation();
        $this->project = new Project();
        
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ProjectWant', $this->projectWant);
    }
    
    public function testSetGetDateTime() {
        $this->assertEquals($this->dateTime, 
                $this->projectWant->setDateTime($this->dateTime));
        $this->assertEquals($this->dateTime, $this->projectWant->getDateTime());
    }
            
    public function testSetGetSponsorOrganisation() {
        $this->assertEquals($this->sponsorOrganisation, 
                $this->projectWant->setSponsorOrganisation($this->sponsorOrganisation));
        $this->assertEquals($this->sponsorOrganisation, 
                $this->projectWant->getSponsorOrganisation());
    }

    public function testSetGetProject() {
        $this->assertEquals($this->project, $this->projectWant->setProject($this->project));
        $this->assertEquals($this->project, $this->projectWant->getProject());
    }
        
    public function testValidate_missingdatetime() {
        $this->assertEquals($this->sponsorOrganisation, 
                $this->projectWant->setSponsorOrganisation($this->sponsorOrganisation));
        $this->assertEquals($this->project, $this->projectWant->setProject($this->project));
        $this->setExpectedException('Exposure\Model\ProjectWantException', 
            ProjectWant::EXCEPTION_INVALID_DATE_TIME);
        $this->projectWant->validate();
    }
        
    public function testValidate_missingsponsororganisation() {
        $this->assertEquals($this->dateTime, $this->projectWant->setDateTime($this->dateTime));
        $this->assertEquals($this->project, $this->projectWant->setProject($this->project));
        $this->setExpectedException('Exposure\Model\ProjectWantException', 
            ProjectWant::EXCEPTION_INVALID_SPONSOR_ORGANISATION);
        $this->projectWant->validate();
    }
        
    public function testValidate_missingproject() {
        $this->assertEquals($this->dateTime, $this->projectWant->setDateTime($this->dateTime));
        $this->assertEquals($this->sponsorOrganisation, $this->projectWant->setSponsorOrganisation($this->sponsorOrganisation));
        $this->setExpectedException('Exposure\Model\ProjectWantException', 
            ProjectWant::EXCEPTION_INVALID_PROJECT);
        $this->projectWant->validate();
    }
        
    public function testValidate() {
        $this->assertEquals($this->dateTime, $this->projectWant->setDateTime($this->dateTime));
        $this->assertEquals($this->sponsorOrganisation, $this->projectWant->setSponsorOrganisation($this->sponsorOrganisation));
        $this->assertEquals($this->project, $this->projectWant->setProject($this->project));
        $this->projectWant->validate();
    }

    public function tearDown() {
        unset($this->projectWant);
    }

}

?>
