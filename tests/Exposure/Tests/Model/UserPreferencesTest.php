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

use Exposure\Model\UserPreferences;

class UserPreferencesTest extends \PHPUnit_Framework_TestCase {

    protected $userPreferences;

    public function setUp() {
        $this->userPreferences = $this->getMockForAbstractClass('Exposure\Model\UserPreferences');
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\UserPreferences', $this->userPreferences);
    }

    public function testSetGetReceiveNotificationsByEmail() {
        $this->assertTrue($this->userPreferences->setReceiveNotificationsByEmail(true));
        $this->assertTrue($this->userPreferences->getReceiveNotificationsByEmail());
        $this->assertFalse($this->userPreferences->setReceiveNotificationsByEmail(false));
        $this->assertFalse($this->userPreferences->getReceiveNotificationsByEmail());
        $this->assertFalse($this->userPreferences->setReceiveNotificationsByEmail(null));
        $this->assertFalse($this->userPreferences->getReceiveNotificationsByEmail());
    }

    public function tearDown() {
        unset($this->userPreferences);
    }
}

?>
