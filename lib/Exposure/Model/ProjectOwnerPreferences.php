<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Model;

class ProjectOwnerPreferences extends UserPreferences {
    protected $receiveNotificationsByEmailWhenWanted = true;
    protected $receivePeriodicDigestByEmail = false;
    protected $receiveNewsletter = true;
    protected $receiveNotificationsWhenCommented = true;
    protected $receiveNotificationsWhenSubscriptionWillExpire = true;
    
    public function getReceiveNotificationsByEmailWhenWanted() {
        return $this->receiveNotificationsByEmailWhenWanted;
    }

    public function setReceiveNotificationsByEmailWhenWanted($receiveNotificationsByEmailWhenWanted) {
        $this->receiveNotificationsByEmailWhenWanted = (bool) $receiveNotificationsByEmailWhenWanted;
        return $this->receiveNotificationsByEmailWhenWanted;
    }

    public function getReceivePeriodicDigestByEmail() {
        return $this->receivePeriodicDigestByEmail;
    }

    public function setReceivePeriodicDigestByEmail($receivePeriodicDigestByEmail) {
        $this->receivePeriodicDigestByEmail = (bool) $receivePeriodicDigestByEmail;
        return $this->receivePeriodicDigestByEmail;
    }
    
    public function getReceiveNewsletter() {
        return $this->receiveNewsletter;
    }

    public function setReceiveNewsletter($receiveNewsletter) {
        $this->receiveNewsletter = (bool) $receiveNewsletter;
        return $this->receiveNewsletter;
    }

    public function getReceiveNotificationsWhenCommented() {
        return $this->receiveNotificationsWhenCommented;
    }

    public function setReceiveNotificationsWhenCommented($receiveNotificationsWhenCommented) {
        $this->receiveNotificationsWhenCommented = (bool) $receiveNotificationsWhenCommented;
        return $this->receiveNotificationsWhenCommented;
    }

    public function getReceiveNotificationsWhenSubscriptionWillExpire() {
        return $this->receiveNotificationsWhenSubscriptionWillExpire;
    }

    public function setReceiveNotificationsWhenSubscriptionWillExpire($receiveNotificationsWhenSubscriptionWillExpire) {
        $this->receiveNotificationsWhenSubscriptionWillExpire = (bool) $receiveNotificationsWhenSubscriptionWillExpire;
        return $this->receiveNotificationsWhenSubscriptionWillExpire;
    }
}


