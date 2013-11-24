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

class SponsorContributionNotification extends Notification {
    const EVENT_PROPOSAL_SUBMITTED_BY_SPONSOR = 'proposal submitted by sponsor';
    const EVENT_PROPOSAL_APPROVED = 'proposal approved';
    const EVENT_CONTRIBUTION_SENT = 'contribution received';
    const EVENT_CONTRIBUTION_RECEIVED = 'contribution received';
    
    /** @var SponsorContribution */
    protected $contribution = null;
    const EXCEPTION_INVALID_CONTRIBUTION = 'invalid contribution';

    public function __construct() {
        $this->setType(parent::TYPE_SPONSOR_CONTRIBUTION);
    }
    
    protected function validateEvent($event) {
        if (!in_array($event, array(
            self::EVENT_CONTRIBUTION_RECEIVED,
            self::EVENT_CONTRIBUTION_SENT,
            self::EVENT_PROPOSAL_APPROVED,
            self::EVENT_PROPOSAL_SUBMITTED_BY_SPONSOR))) {
            throw new NotificationException(parent::EXCEPTION_INVALID_EVENT);
        }
    }
    
    public function getContribution() {
        return $this->contribution;
    }

    public function setContribution(SponsorContribution $contribution) {
        $this->contribution = $contribution;
        return $this->contribution;
    }

    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_SPONSOR_CONTRIBUTION) {
            throw new NotificationException(parent::EXCEPTION_TYPE_MISMATCH);
        }
        $this->validateEvent($this->event);
        if (!is_a($this->contribution, 'Exposure\Model\SponsorContribution')) {
            throw new SponsorContributionNotificationException(self::EXCEPTION_INVALID_CONTRIBUTION);
        }
    }

    public function isStatusEditableByUser(User $user) {
        switch ($user->getType()) {
        case User::TYPE_PROJECT_OWNER:
            $project = $this->contribution->getProject();
            return $project->getOwners()->contains($user)
                && $project->getNotifications()->contains($this);
        case User::TYPE_SPONSOR:
            $organisation = $this->contribution->getContributor();
            return $organisation->hasMember($user)
                && $organisation->getNotifications()->contains($this);
        }
        return false;
    }
}


