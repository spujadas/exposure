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

class CommentNotification extends Notification {
    const EVENT_RECEIVED_COMMENT = 'received comment';
    
    /** @var SponsorReturn */
    protected $comment = null;
    const EXCEPTION_INVALID_COMMENT = 'invalid comment';

    public function __construct() {
        $this->setType(parent::TYPE_COMMENT);
    }
    
    protected function validateEvent($event) {
        if (($event != self::EVENT_RECEIVED_COMMENT)) {
            throw new NotificationException(parent::EXCEPTION_INVALID_EVENT);
        }
    }
    
    public function getComment() {
        return $this->comment;
    }

    public function setComment(Comment $comment) {
        $this->comment = $comment;
        return $this->comment;
    }

    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_COMMENT) {
            throw new NotificationException(parent::EXCEPTION_TYPE_MISMATCH);
        }
        $this->validateEvent($this->event);
        if (!is_a($this->comment, 'Exposure\Model\Comment')) {
            throw new CommentNotificationException(self::EXCEPTION_INVALID_COMMENT);
        }
    }

    public function isStatusEditableByUser(User $user) {
        if (is_a($this->comment, 'Exposure\Model\CommentOnProjectOwner')) {
            return $user->getNotifications()->contains($this);
        }

        if (is_a($this->comment, 'Exposure\Model\CommentOnSponsorReturn')
            && ($user->getType() == User::TYPE_PROJECT_OWNER)) {
            $project = $this->comment->getSponsorReturn()->getNeed()->getProject();
            return $project->getOwners()->contains($user)
                && $project->getNotifications()->contains($this);
        }

        if (is_a($this->comment, 'Exposure\Model\CommentOnSponsorOrganisation')
            && ($user->getType() == User::TYPE_SPONSOR)) {
            $organisation = $this->comment->getSponsorOrganisation();
            return $organisation->hasMember($user)
                && $organisation->getNotifications()->contains($this);
        }

        if (is_a($this->comment, 'Exposure\Model\CommentOnProject')
            && ($user->getType() == User::TYPE_PROJECT_OWNER)) {
            $project = $this->comment->getProject();
            return $project->getOwners()->contains($user)
                && $project->getNotifications()->contains($this);        
        }
        return false;
    }
}


