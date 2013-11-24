<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Controller;

use Exposure\Model\SponsorContribution,
    Exposure\Model\SponsorContributionNotification,
    Exposure\Model\User,
    Exposure\Model\Project;

class ContributionPostActions extends \Exposure\Controller\PostActions {
    /************
        Common
    */

    protected function contributionStatusUpdateRequestIsValidPost() {
        return $this->postHasIndices(array('contribution_id'));
    }

    protected function getContributionIfStatusUpdateRequestValid() {
        // valid request?
        if (!$this->contributionStatusUpdateRequestIsValidPost()) { return null; }

        return $this->getById('Exposure\Model\SponsorContribution',
            $_POST['contribution_id']);
    }

    protected function getContributionIfStatusUpdateRequestFromProjectOwnerValid() {
        if (is_null($contribution = $this->getContributionIfStatusUpdateRequestValid())) {
            return null;
        }

        // check if user owns the project
        if (is_null($user = $this->getSignedInUser())
            || ($user->getType() != User::TYPE_PROJECT_OWNER)
            || !$user->ownsProject($contribution->getProject())) {
            return null; 
        }

        return $contribution;
    }

    protected function getContributionIfStatusUpdateRequestFromSponsorValid() {
        if (is_null($contribution = $this->getContributionIfStatusUpdateRequestValid())) {
            return null;
        }

        // check if the user belongs to the organisation that is sponsoring the project
        if (is_null($user = $this->getSignedInUser())
            || ($user->getType() != User::TYPE_SPONSOR)
            || !$user->belongsToOrganisation($contribution->getContributor())) { 
            return null; 
        }

        return $contribution;
    }

    protected function sendNotificationEmail($emailTemplate, 
        SponsorContribution $contribution, User $user, Project $project) {
        $parameters = array (
            'contribution' => $contribution,
            'need' => $contribution->getContributedNeed(),
            'project' => $project,
            'language' => $user->getLanguageCode(),
            'currency' => $_SESSION['currency'],
        );

        return $this->sendEmail($emailTemplate, $parameters, $user);
    }


    /**********************
        Proposal approve
    */

    protected function notifyProposalApproved(SponsorContribution $contribution) {
        // init notification
        $notification = new SponsorContributionNotification;
        $notification->setStatus(SponsorContributionNotification::STATUS_UNREAD);
        $notification->setContribution($contribution);
        $notification->setDateTime(new \DateTime);
        $notification->setEvent(SponsorContributionNotification::EVENT_PROPOSAL_APPROVED);

        // attach notification to organisation
        $this->config->getDocumentManager()->persist($notification);
        $contribution->getContributor()->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to sponsor users if req'd
        $project = $contribution->getProject();
        foreach ($contribution->getContributor()->getSponsorUsers() as $user) {
            if ($user->getReceiveNotificationsByEmail()) {
                $this->sendContributionProposalApprovedEmail($contribution, $user, $project);
            }
        }
    }

    protected function sendContributionProposalApprovedEmail(SponsorContribution $contribution, 
        User $user, Project $project) {
        $emailTemplate = $this->config->getTwig()->loadTemplate('contribution-proposal-approved-email.tpl.html');

        return $this->sendNotificationEmail($emailTemplate, $contribution, 
            $user, $project);
    }

    public function proposalApprove() {
        $this->updateStatusAndNotify(
            'getContributionIfStatusUpdateRequestFromProjectOwnerValid',
            SponsorContribution::STATUS_PROPOSAL_SUBMITTED_BY_SPONSOR,
            SponsorContribution::STATUS_PROPOSAL_APPROVED,
            'notifyProposalApproved');
    }


    /***********************
        Contribution sent
    */

    protected function notifySent(SponsorContribution $contribution) {
        // init notification
        $notification = new SponsorContributionNotification;
        $notification->setStatus(SponsorContributionNotification::STATUS_UNREAD);
        $notification->setContribution($contribution);
        $notification->setDateTime(new \DateTime);
        $notification->setEvent(SponsorContributionNotification::EVENT_CONTRIBUTION_SENT);

        // attach notification to project
        $this->config->getDocumentManager()->persist($notification);
        $contribution->getProject()->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to project owners if req'd
        $project = $contribution->getProject();
        foreach ($project->getOwners() as $user) {
            if ($user->getReceiveNotificationsByEmail()) {
                $this->sendContributionSentEmail($contribution, $user, $project);
            }
        }
    }

    protected function sendContributionSentEmail(SponsorContribution $contribution, 
        User $user, Project $project) {
        $emailTemplate = $this->config->getTwig()->loadTemplate('contribution-sent-email.tpl.html');

        return $this->sendNotificationEmail($emailTemplate, $contribution, 
            $user, $project);
    }

    public function markSent() {
        $this->updateStatusAndNotify(
            'getContributionIfStatusUpdateRequestFromSponsorValid',
            SponsorContribution::STATUS_PROPOSAL_APPROVED,
            SponsorContribution::STATUS_SENT,
            'notifySent');
    }


    /***************************
        Contribution received
    */

    protected function notifyReceived(SponsorContribution $contribution) {
        // init notification
        $notification = new SponsorContributionNotification;
        $notification->setStatus(SponsorContributionNotification::STATUS_UNREAD);
        $notification->setContribution($contribution);
        $notification->setDateTime(new \DateTime);
        $notification->setEvent(SponsorContributionNotification::EVENT_CONTRIBUTION_RECEIVED);

        // attach notification to organisation
        $this->config->getDocumentManager()->persist($notification);
        $contribution->getContributor()->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to sponsor users if req'd
        $project = $contribution->getProject();
        foreach ($contribution->getContributor()->getSponsorUsers() as $user) {
            if ($user->getReceiveNotificationsByEmail()) {
                $this->sendContributionReceivedEmail($contribution, $user, $project);
            }
        }
    }

    protected function sendContributionReceivedEmail(SponsorContribution $contribution, 
        User $user, Project $project) {
        $emailTemplate = $this->config->getTwig()->loadTemplate('contribution-received-email.tpl.html');

        return $this->sendNotificationEmail($emailTemplate, $contribution, 
            $user, $project);
    }

    public function markReceived() {
        $this->updateStatusAndNotify(
            'getContributionIfStatusUpdateRequestFromProjectOwnerValid',
            SponsorContribution::STATUS_SENT,
            SponsorContribution::STATUS_RECEIVED,
            'notifyReceived');
    }
}


