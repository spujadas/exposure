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

use Sociable\Utility\StringValidator,
    Exposure\Model\User;

class ProfileModerationNotification extends Notification {
    protected $user = null;
    
    const EVENT_SUBMITTED_PROFILE = 'submitted profile';
    const EVENT_APPROVED_PROFILE = 'approved profile';
    const EVENT_REFUSED_PROFILE = 'refused profile';
    const EVENT_PROFILE_NEEDS_EDITING = 'profile needs editing';

    public function __construct() {
        $this->setType(parent::TYPE_PROFILE_MODERATION);
    }
    
    protected function validateEvent($event) {
        if (!in_array($event, array(
                self::EVENT_APPROVED_PROFILE,
                self::EVENT_PROFILE_NEEDS_EDITING,
                self::EVENT_REFUSED_PROFILE,
                self::EVENT_SUBMITTED_PROFILE))) {
            throw new NotificationException(parent::EXCEPTION_INVALID_EVENT);
        }
    }
    
    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_PROFILE_MODERATION) {
            throw new NotificationException(parent::EXCEPTION_TYPE_MISMATCH);
        }
        $this->validateEvent($this->event);
    }

    public function setUser(User $user = null) {
        return $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }

    public function isStatusEditableByUser(User $user) {
        return $user->getNotifications()->contains($this);
    }
}


