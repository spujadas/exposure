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

use Exposure\Model\SponsorUserPreferences;

class SponsorUserPreferencesTest extends \PHPUnit_Framework_TestCase {

    protected $sponsorUserPreferences;

    public function setUp() {
        $this->sponsorUserPreferences = new SponsorUserPreferences();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SponsorUserPreferences', $this->sponsorUserPreferences);
    }

    public function testSetGetReceiveDailyDigestByEmail() {
        $this->assertTrue($this->sponsorUserPreferences->setReceiveDailyDigestByEmail(true));
        $this->assertTrue($this->sponsorUserPreferences->getReceiveDailyDigestByEmail());
        $this->assertFalse($this->sponsorUserPreferences->setReceiveDailyDigestByEmail(false));
        $this->assertFalse($this->sponsorUserPreferences->getReceiveDailyDigestByEmail());
        $this->assertFalse($this->sponsorUserPreferences->setReceiveDailyDigestByEmail(null));
        $this->assertFalse($this->sponsorUserPreferences->getReceiveDailyDigestByEmail());
    }
    
    public function testSetGetReceivePeriodicDigestByEmail() {
        $this->assertTrue($this->sponsorUserPreferences->setReceivePeriodicDigestByEmail(true));
        $this->assertTrue($this->sponsorUserPreferences->getReceivePeriodicDigestByEmail());
        $this->assertFalse($this->sponsorUserPreferences->setReceivePeriodicDigestByEmail(false));
        $this->assertFalse($this->sponsorUserPreferences->getReceivePeriodicDigestByEmail());
        $this->assertFalse($this->sponsorUserPreferences->setReceivePeriodicDigestByEmail(null));
        $this->assertFalse($this->sponsorUserPreferences->getReceivePeriodicDigestByEmail());
    }

    public function tearDown() {
        unset($this->sponsorUserPreferences);
    }
}

?>
