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

use Exposure\Model\SponsorContribution,
    Exposure\Model\SponsorOrganisation;

class SponsorContributionTest extends \PHPUnit_Framework_TestCase {

    protected $sponsorContribution;
    const STATUS = SponsorContribution::STATUS_PROPOSAL_APPROVED;
    protected $sponsorOrganisation;

    public function setUp() {
        $this->sponsorContribution = new SponsorContribution();
        $this->sponsorOrganisation = new SponsorOrganisation();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SponsorContribution', $this->sponsorContribution);
    }
            
    public function testSetGetSponsorOrganisation() {
        $this->assertEquals($this->sponsorOrganisation, 
                $this->sponsorContribution->setContributor($this->sponsorOrganisation));
        $this->assertEquals($this->sponsorOrganisation, 
                $this->sponsorContribution->getContributor());
    }
            
    public function testSetStatus_invalidstatus() {
        $this->setExpectedException('Exposure\Model\SponsorContributionException', 
            SponsorContribution::EXCEPTION_INVALID_STATUS);
        $this->sponsorContribution->setStatus(null);
    }
    
    public function testGetStatus_invalidstatus() {
        try {
            $this->sponsorContribution->setStatus(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorContribution->getStatus());
    }
    
    public function testSetGetStatus() {
        $this->assertEquals(self::STATUS, $this->sponsorContribution->setStatus(self::STATUS));
        $this->assertEquals(self::STATUS, $this->sponsorContribution->getStatus());
    }
        
    public function testValidate() {
        $this->sponsorContribution->setStatus(self::STATUS);
        $this->sponsorContribution->setContributor($this->sponsorOrganisation);
        $this->sponsorContribution->validate();
    }

    public function tearDown() {
        unset($this->sponsorContribution);
    }

}

?>
