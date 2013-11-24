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

class ProjectWantNotification extends Notification {
    const EVENT_WANTED_PROJECT = 'wanted project';
    
    /** @var SponsorOrganisation */
    protected $want = null;
    const EXCEPTION_INVALID_WANT = 'invalid want';

    public function __construct() {
        $this->setType(parent::TYPE_PROJECT_WANT);
    }
    
    protected function validateEvent($event) {
        if (($event != self::EVENT_WANTED_PROJECT)) {
            throw new NotificationException(parent::EXCEPTION_INVALID_EVENT);
        }
    }
    
    public function getWant() {
        return $this->want;
    }

    public function setWant(ProjectWant $want) {
        $this->want = $want;
        return $this->want;
    }

    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_PROJECT_WANT) {
            throw new NotificationException(parent::EXCEPTION_TYPE_MISMATCH);
        }
        $this->validateEvent($this->event);
        if (!is_a($this->want, 'Exposure\Model\ProjectWant')) {
            throw new ProjectWantNotificationException(self::EXCEPTION_INVALID_WANT);
        }
    }

    public function isStatusEditableByUser(User $user) {
        $project = $this->getWant()->getProject();
        return $project->getOwners()->contains($user)
            && $project->getNotifications()->contains($this);
    }
}


