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

use Exposure\Model\SponsorContributionTypes;

class SponsorContributionTypesTest extends \PHPUnit_Framework_TestCase {

    protected $sponsorContributionTypes;

    public function setUp() {
        $this->sponsorContributionTypes = new SponsorContributionTypes();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SponsorContributionTypes', $this->sponsorContributionTypes);
    }
    
    public function testSetGetFinancial() {
        $this->assertTrue($this->sponsorContributionTypes->setFinancial(true));
        $this->assertTrue($this->sponsorContributionTypes->getFinancial());
        $this->assertFalse($this->sponsorContributionTypes->setFinancial(false));
        $this->assertFalse($this->sponsorContributionTypes->getFinancial());
        $this->assertFalse($this->sponsorContributionTypes->setFinancial(null));
        $this->assertFalse($this->sponsorContributionTypes->getFinancial());
    }
    
    public function testSetGetEquipment() {
        $this->assertTrue($this->sponsorContributionTypes->setEquipment(true));
        $this->assertTrue($this->sponsorContributionTypes->getEquipment());
        $this->assertFalse($this->sponsorContributionTypes->setEquipment(false));
        $this->assertFalse($this->sponsorContributionTypes->getEquipment());
        $this->assertFalse($this->sponsorContributionTypes->setEquipment(null));
        $this->assertFalse($this->sponsorContributionTypes->getEquipment());
    }
    
    public function testSetGetService() {
        $this->assertTrue($this->sponsorContributionTypes->setService(true));
        $this->assertTrue($this->sponsorContributionTypes->getService());
        $this->assertFalse($this->sponsorContributionTypes->setService(false));
        $this->assertFalse($this->sponsorContributionTypes->getService());
        $this->assertFalse($this->sponsorContributionTypes->setService(null));
        $this->assertFalse($this->sponsorContributionTypes->getService());
    }
    
    public function tearDown() {
        unset($this->sponsorContributionTypes);
    }

}

?>
