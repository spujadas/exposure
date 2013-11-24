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

use Sociable\Utility\StringValidator;

class SponsorReturnNotification extends Notification {
    const EVENT_STARTED = 'started';
    const EVENT_COMPLETED_BY_PROJECT = 'completed by project';
    const EVENT_APPROVED = 'approved';
    
    /** @var SponsorReturn */
    protected $return = null;
    const EXCEPTION_INVALID_RETURN = 'invalid return';

    public function __construct() {
        $this->setType(parent::TYPE_SPONSOR_RETURN);
    }
    
    protected function validateEvent($event) {
        if (!in_array($event, array(self::EVENT_STARTED, 
                self::EVENT_COMPLETED_BY_PROJECT, self::EVENT_APPROVED))) {
            throw new NotificationException(parent::EXCEPTION_INVALID_EVENT);
        }
    }
    
    public function getReturn() {
        return $this->return;
    }

    public function setReturn(SponsorReturn $return) {
        $this->return = $return;
        return $this->return;
    }

    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_SPONSOR_RETURN) {
            throw new NotificationException(parent::EXCEPTION_TYPE_MISMATCH);
        }
        $this->validateEvent($this->event);
        if (!is_a($this->return, 'Exposure\Model\SponsorReturn')) {
            throw new SponsorReturnNotificationException(self::EXCEPTION_INVALID_RETURN);
        }
    }

    public function isStatusEditableByUser(User $user) {
        switch ($user->getType()) {
        case User::TYPE_PROJECT_OWNER:
            $project = $this->return->getNeed()->getProject();
            return $project->getOwners()->contains($user)
                && $project->getNotifications()->contains($this);
        case User::TYPE_SPONSOR:
            $organisation = $this->return->getNeed()->getContribution()->getContributor();
            return $organisation->hasMember($user)
                && $organisation->getNotifications()->contains($this);
        }
        return false;
    }
}


