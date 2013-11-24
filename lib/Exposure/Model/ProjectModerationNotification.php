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
    Exposure\Model\Project;

class ProjectModerationNotification extends Notification {
    protected $project = null;

    const EVENT_SUBMITTED_PROJECT = 'submitted project';
    const EVENT_APPROVED_PROJECT = 'approved project';
    const EVENT_FLAGGED_PROJECT = 'flagged project';
    const EVENT_REFUSED_PROJECT = 'refused project';
    const EVENT_PROJECT_NEEDS_EDITING = 'project needs editing';

    public function __construct() {
        $this->setType(parent::TYPE_PROJECT_MODERATION);
    }
    
    protected function validateEvent($event) {
        if (!in_array($event, array(
            self::EVENT_APPROVED_PROJECT, 
            self::EVENT_PROJECT_NEEDS_EDITING, 
            self::EVENT_FLAGGED_PROJECT, 
            self::EVENT_REFUSED_PROJECT, 
            self::EVENT_SUBMITTED_PROJECT)
        )) {
            throw new NotificationException(parent::EXCEPTION_INVALID_EVENT);
        }
    }
    
    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_PROJECT_MODERATION) {
            throw new NotificationException(parent::EXCEPTION_TYPE_MISMATCH);
        }
        $this->validateEvent($this->event);
    }

    public function setProject(Project $project = null) {
        return $this->project = $project;
    }

    public function getProject() {
        return $this->project;
    }

    public function isStatusEditableByUser(User $user) {
        $project = $this->getProject();
        return $project->getOwners()->contains($user)
            && $project->getNotifications()->contains($this);
    }
}


