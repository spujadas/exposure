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

use Exposure\Model\ProjectOwnerPreferences;

class ProjectOwnerPreferencesTest extends \PHPUnit_Framework_TestCase {

    protected $projectOwnerPreferences;

    public function setUp() {
        $this->projectOwnerPreferences = new ProjectOwnerPreferences();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ProjectOwnerPreferences', $this->projectOwnerPreferences);
    }
    
    public function testSetGetReceiveNotificationsByEmailWhenWanted() {
        $this->assertTrue($this->projectOwnerPreferences->setReceiveNotificationsByEmailWhenWanted(true));
        $this->assertTrue($this->projectOwnerPreferences->getReceiveNotificationsByEmailWhenWanted());
        $this->assertFalse($this->projectOwnerPreferences->setReceiveNotificationsByEmailWhenWanted(false));
        $this->assertFalse($this->projectOwnerPreferences->getReceiveNotificationsByEmailWhenWanted());
        $this->assertFalse($this->projectOwnerPreferences->setReceiveNotificationsByEmailWhenWanted(null));
        $this->assertFalse($this->projectOwnerPreferences->getReceiveNotificationsByEmailWhenWanted());
    }
    
    public function testSetGetReceivePeriodicDigestByEmail() {
        $this->assertTrue($this->projectOwnerPreferences->setReceivePeriodicDigestByEmail(true));
        $this->assertTrue($this->projectOwnerPreferences->getReceivePeriodicDigestByEmail());
        $this->assertFalse($this->projectOwnerPreferences->setReceivePeriodicDigestByEmail(false));
        $this->assertFalse($this->projectOwnerPreferences->getReceivePeriodicDigestByEmail());
        $this->assertFalse($this->projectOwnerPreferences->setReceivePeriodicDigestByEmail(null));
        $this->assertFalse($this->projectOwnerPreferences->getReceivePeriodicDigestByEmail());
    }
    
    public function testSetGetReceiveNewsletter() {
        $this->assertTrue($this->projectOwnerPreferences->setReceiveNewsletter(true));
        $this->assertTrue($this->projectOwnerPreferences->getReceiveNewsletter());
        $this->assertFalse($this->projectOwnerPreferences->setReceiveNewsletter(false));
        $this->assertFalse($this->projectOwnerPreferences->getReceiveNewsletter());
        $this->assertFalse($this->projectOwnerPreferences->setReceiveNewsletter(null));
        $this->assertFalse($this->projectOwnerPreferences->getReceiveNewsletter());
    }
    
    public function testSetGetReceiveNotificationsWhenCommented() {
        $this->assertTrue($this->projectOwnerPreferences->setReceiveNotificationsWhenCommented(true));
        $this->assertTrue($this->projectOwnerPreferences->getReceiveNotificationsWhenCommented());
        $this->assertFalse($this->projectOwnerPreferences->setReceiveNotificationsWhenCommented(false));
        $this->assertFalse($this->projectOwnerPreferences->getReceiveNotificationsWhenCommented());
        $this->assertFalse($this->projectOwnerPreferences->setReceiveNotificationsWhenCommented(null));
        $this->assertFalse($this->projectOwnerPreferences->getReceiveNotificationsWhenCommented());
    }
    
    public function testSetGetReceiveNotificationsWhenSubscriptionWillExpire() {
        $this->assertTrue($this->projectOwnerPreferences->setReceiveNotificationsWhenSubscriptionWillExpire(true));
        $this->assertTrue($this->projectOwnerPreferences->getReceiveNotificationsWhenSubscriptionWillExpire());
        $this->assertFalse($this->projectOwnerPreferences->setReceiveNotificationsWhenSubscriptionWillExpire(false));
        $this->assertFalse($this->projectOwnerPreferences->getReceiveNotificationsWhenSubscriptionWillExpire());
        $this->assertFalse($this->projectOwnerPreferences->setReceiveNotificationsWhenSubscriptionWillExpire(null));
        $this->assertFalse($this->projectOwnerPreferences->getReceiveNotificationsWhenSubscriptionWillExpire());
    }
    
    public function tearDown() {
        unset($this->projectOwnerPreferences);
    }

}

?>
