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

use Exposure\Model\User,
    Exposure\Model\Project,
    Exposure\Model\ModerationStatus,
    Exposure\Model\Notification,
    Exposure\Model\ProjectThemeSuggestionNotification,
    Exposure\Model\ProjectModerationNotification,
    Exposure\Model\ProfileModerationNotification,
    Exposure\Model\ProjectWantNotification,
    Exposure\Model\SponsorContributionNotification;

class NotificationPostActions extends \Exposure\Controller\PostActions {
    protected $errors;
    
    protected function isNotificationRequestValid() {
        return $this->postHasIndices(array('notification_type', 'notification_id'));
    }

    protected function getNotification($notificationType, $notificationId) {
        switch ($_POST['notification_type']) {
        case Notification::TYPE_PROJECT_THEME_SUGGESTION:
            $object = 'Exposure\Model\ProjectThemeSuggestionNotification';
            break;
        case Notification::TYPE_PROJECT_MODERATION:
            $object = 'Exposure\Model\ProjectModerationNotification';
            break;
        case Notification::TYPE_PROFILE_MODERATION:
            $object = 'Exposure\Model\ProfileModerationNotification';
            break;
        case Notification::TYPE_PROJECT_WANT:
            $object = 'Exposure\Model\ProjectWantNotification';
            break;
        case Notification::TYPE_SPONSOR_CONTRIBUTION:
            $object = 'Exposure\Model\SponsorContributionNotification';
            break;
        case Notification::TYPE_SPONSOR_RETURN:
            $object = 'Exposure\Model\SponsorReturnNotification';
            break;
        default:
            return null;
            break;
        }

        return $this->getById($object, $_POST['notification_id']);
    }

    protected function getNotificationIfNotificationRequestValid() {
        // signed in?
        if (is_null($user = $this->getSignedInUser())) { return null; }

        // valid notification request?
        if (!$this->isNotificationRequestValid()) { return null; }
        
        // existing notification?
        if (is_null($notification = $this->getNotification($_POST['notification_type'], 
            $_POST['notification_id']))) {
            return null;
        }

        // check if notification status can be edited by the user
        if (!$notification->isStatusEditableByUser($user)) { return null; }

        return $notification;
    }

    protected function notificationUpdateStatus($status) {
        header('Content-Type: application/json');

        $notification = $this->getNotificationIfNotificationRequestValid();
        if (is_null($notification)) {
            echo json_encode(false);
            return;
        }
        $notification->setStatus($status);
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }

    public function notificationMarkRead() {
        $this->notificationUpdateStatus(Notification::STATUS_READ);
    }

    public function notificationMarkUnread() {
        $this->notificationUpdateStatus(Notification::STATUS_UNREAD);
    }

    public function notificationArchive() {
        $this->notificationUpdateStatus(Notification::STATUS_ARCHIVED);
    }

    protected function removeNotificationFromParentObject(Notification $notification) {
        switch($notification->getType()) {
        case Notification::TYPE_PROJECT_THEME_SUGGESTION:
        case Notification::TYPE_PROFILE_MODERATION:
            return $notification->getUser()->getNotifications()->removeElement($notification);
        case Notification::TYPE_PROJECT_MODERATION:
        case Notification::TYPE_PROJECT_WANT:
            return $notification->getProject()->getNotifications()->removeElement($notification);
        case Notification::TYPE_SPONSOR_CONTRIBUTION:
        case Notification::TYPE_SPONSOR_RETURN:
            if (is_null($user = $this->getSignedInUser())) {
                return false;
            }
            switch($user->getType()) {
            case User::TYPE_SPONSOR:
                return $notification->getContribution()->getContributor()
                    ->getNotifications()->removeElement($notification);
                break;
            case User::TYPE_PROJECT_OWNER:
                return $notification->getContribution()->getProject()
                    ->getNotifications()->removeElement($notification);
            }
        }
        return false;
    }

    public function notificationDelete() {
        header('Content-Type: application/json');
        // get notification to delete
        if (is_null($notification = $this->getNotificationIfNotificationRequestValid())
            || !$this->removeNotificationFromParentObject($notification)) {
            echo json_encode(false);
            return;
        }

        // delete notification itself
        $this->config->getDocumentManager()->remove($notification);
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }
}


