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

use Exposure\Model\ViewRights;

class ViewRightsTest extends \PHPUnit_Framework_TestCase {

    protected $viewRights;

    public function setUp() {
        $this->viewRights = new ViewRights();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ViewRights', $this->viewRights);
    }
    
    public function testSetGetCanSeeSponsors() {
        $this->assertTrue($this->viewRights->setCanSeeSponsors(true));
        $this->assertTrue($this->viewRights->getCanSeeSponsors());
        $this->assertFalse($this->viewRights->setCanSeeSponsors(false));
        $this->assertFalse($this->viewRights->getCanSeeSponsors());
        $this->assertFalse($this->viewRights->setCanSeeSponsors(null));
        $this->assertFalse($this->viewRights->getCanSeeSponsors());
    }
    
    public function tearDown() {
        unset($this->viewRights);
    }

}

?>
