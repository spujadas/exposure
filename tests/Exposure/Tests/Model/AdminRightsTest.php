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

use Exposure\Model\AdminRights;

class AdminRightsTest extends \PHPUnit_Framework_TestCase {

    protected $adminRights;

    public function setUp() {
        $this->adminRights = new AdminRights;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\AdminRights', $this->adminRights);
    }
    
    public function testSetGetCrud() {
        $this->assertTrue($this->adminRights->setCrud(true));
        $this->assertTrue($this->adminRights->getCrud());
        $this->assertFalse($this->adminRights->setCrud(false));
        $this->assertFalse($this->adminRights->getCrud());
        $this->assertFalse($this->adminRights->setCrud(null));
        $this->assertFalse($this->adminRights->getCrud());
    }
    
    public function testSetGetApprove() {
        $this->assertTrue($this->adminRights->setApprove(true));
        $this->assertTrue($this->adminRights->getApprove());
        $this->assertFalse($this->adminRights->setApprove(false));
        $this->assertFalse($this->adminRights->getApprove());
        $this->assertFalse($this->adminRights->setApprove(null));
        $this->assertFalse($this->adminRights->getApprove());
    }
    
    public function testSetGetAdmin() {
        $this->assertTrue($this->adminRights->setAdmin(true));
        $this->assertTrue($this->adminRights->getAdmin());
        $this->assertFalse($this->adminRights->setAdmin(false));
        $this->assertFalse($this->adminRights->getAdmin());
        $this->assertFalse($this->adminRights->setAdmin(null));
        $this->assertFalse($this->adminRights->getAdmin());
    }
    
    public function tearDown() {
        unset($this->adminRights);
    }

}

?>
