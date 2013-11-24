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

use Exposure\Model\AdminUser,
    Exposure\Model\User,
    Exposure\Model\Project,
    Exposure\Model\ModerationStatus,
    Exposure\Model\Notification,
    Exposure\Model\ProjectModerationNotification,
    Exposure\Model\ProfileModerationNotification,
    Sociable\Model\PasswordAuthenticator,
    Sociable\Utility\StringValidator,
    Sociable\Utility\StringException;

class AdminPostActions extends \Exposure\Controller\PostActions {
    const ALREADY_SIGNED_IN = 'you are already signed in';

    protected $errors;

    protected function signinIsValidPost() {
        return $this->postHasIndices(array('password', 'email'));
    }

    protected function signinIsAuthenticated() {
        $this->errors = array();
        
        // retrieve user by email
        $adminUser = $this->getByEmail('Exposure\Model\AdminUser', $_POST['email']);
        
        // no user for entered email
        if(is_null($adminUser)) {
            $this->errors['email'] = 'no user is registered to this email address';
                // $this->translate->_('adminSignin.error.nonexistentUser');
            return self::INVALID_DATA;
        }
        
        // validate user
        $adminUser->validate();

        // test for password authenticator
        $authenticator = $adminUser->getAuthenticator();
        
        // test password
        try {
            $authenticates = $authenticator->authenticate(array('password' => $_POST['password']));
        }
        catch (\Exception $e) {
            $this->errors['password'] = 'password is invalid';
                // $this->translate->_('adminSignin.error.invalidPassword');
            return self::INVALID_DATA;
        }
        
        if (!$authenticates) {
            $this->errors['password'] = 'password is incorrect';
                // $this->translate->_('adminSignin.error.incorrectPassword');
            return self::INVALID_DATA;
        }
        
        SessionUtils::setAdminSessionParams($adminUser, 
            $this->config->getParam('defaultLanguageCode'),
            $this->config->getParam('defaultCurrencyCode'));
        return self::SUCCESS;
    }

    public function signin() {
        if (isset($_SESSION['adminuser']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are already signed in',
                // 'content' => $this->translate->_('adminSignup.warning.alreadySignedIn'),
                'type' => 'warning');
            return self::ALREADY_SIGNED_IN;
        }
        
        // check $_POST
        if (!$this->signinIsValidPost()) {
            return self::INVALID_POST;
        }

        // authenticate
        $result = $this->signinIsAuthenticated();

        if ($result == self::SUCCESS) {
            $_SESSION['message'] = array (
                'content' => 'you are now signed in',
                // 'content' => $this->translate->_('adminSignin.success.connected'),
                'type' => 'success');
            if (isset($_SESSION['last-operation'])) {
                header('Location: ' . $_SESSION['last-operation']);
                unset($_SESSION['last-operation']);
                return;
            }
            return $result;
        }
        
        if (!empty($this->errors)) {
            $_SESSION['errors'] = $this->errors;
            $_SESSION['message'] = array (
                'content' => 'some fields are incorrectly filled in',
                // 'content' => $this->translate->_('adminSignin.errors'),
                'type' => 'error');

            // fill in autofill
            $_SESSION['autofill'] = array (
                'email' => $_POST['email'],
            );
        }
        
        return $result;
    }

    public function signout() {
        // sign out
        $_SESSION = array();

        // message
        $_SESSION['message'] = array (
                'content' => 'you are now signed out',
                // 'content' => $this->translate->_('signout.success.disconnected'),
                'type' => 'success');
        
        return self::SUCCESS;
    }

    protected function profileModerateIsValidPost() {
        if (!$this->postHasIndices(array('user_id', 'comment'))) {
            return false;
        }

        // make sure that one action exists
        foreach (array('request_editing', 'reject', 'approve') as $index) {
            if (isset($_POST[$index])) {
                return true;
            }
        }

        return false;
    }

    protected function sendProfileModerationEmail(User $user, $content) {
        switch ($user->getModerationStatus()->getStatus()) {
        case ModerationStatus::STATUS_USER_EDIT:
            $emailTemplate = $this->config->getTwig()->loadTemplate('profile-moderation-needs-editing-email.tpl.html');
            break;
        case ModerationStatus::STATUS_REJECTED:
            $emailTemplate = $this->config->getTwig()->loadTemplate('profile-moderation-rejected-email.tpl.html');
            break;
        case ModerationStatus::STATUS_APPROVED:
        default:
            $emailTemplate = $this->config->getTwig()->loadTemplate('profile-moderation-approved-email.tpl.html');
            break;
        }

        $profileEditUrl = 'http://' . $this->config->getParam('hostname') 
                . '/profile-edit/' . $user->getId();
        
        $parameters = array (
            'profileEditUrl' => $profileEditUrl,
        );

        if (!empty($content)) {
            $parameters['content'] = $content;
        }

        $subject  = $emailTemplate->renderBlock('subject',   $parameters);
        $bodyHtml = $emailTemplate->renderBlock('body_html', $parameters);
        $bodyText = $emailTemplate->renderBlock('body_text', $parameters);
        
        $message = \Swift_Message::newInstance()
            ->setFrom(array(
                $this->config->getParam('emailFromAddress') 
                    => $this->config->getParam('emailFromName'))
                )
            ->setSubject($subject)
            ->setBody($bodyText, 'text/plain')
            ->addPart($bodyHtml, 'text/html');
        
        $message->setTo($user->getEmail());
        return $this->config->getSwiftMailer()->send($message);
    }


    protected function updateUserModerationStatusAndNotify(User $user) {
        // init notification
        $notification = new ProfileModerationNotification;
        $notification->setStatus(ProfileModerationNotification::STATUS_UNREAD);
        if (!empty($_POST['comment'])) {
            $notification->setContent($_POST['comment']);
        }
        $notification->setUser($user);
        $notification->setDateTime(new \DateTime);

        // update status
        if (array_key_exists('request_editing', $_POST)) {
            $user->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
            $notification->setEvent(ProfileModerationNotification::EVENT_PROFILE_NEEDS_EDITING);
            $messageContent = 'needs editing'; 
                // $this->translate->_('profileModeration.status.needsEditing');
        }
        elseif (array_key_exists('reject', $_POST)) {
            $user->getModerationStatus()->setStatus(ModerationStatus::STATUS_REJECT);
            $notification->setEvent(ProfileModerationNotification::EVENT_REJECTED_PROFILE);
            $messageContent = 'rejected'; 
                // $this->translate->_('profileModeration.status.rejected');
        }
        else {
            $user->getModerationStatus()->setStatus(ModerationStatus::STATUS_APPROVED);
            $notification->setEvent(ProfileModerationNotification::EVENT_APPROVED_PROFILE);
            $messageContent = 'approved'; 
                // $this->translate->_('profileModeration.status.approved');
        }

        // attach notification to user
        $this->config->getDocumentManager()->persist($notification);
        $user->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail if req'd
        if ($user->getReceiveNotificationsByEmail()) {
            $this->sendProfileModerationEmail($user, $_POST['comment']);
        }

        $_SESSION['message'] = array (
            'content' => 'profile has been moderated – ' . $messageContent,
            // 'content' => $this->translate->_('profileModeration.success.moderatedProfile'),
            'type' => 'success');
    }

    public function profileModerate() {
        if (!isset($_SESSION['adminuser']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->profileModerateIsValidPost()) { return self::INVALID_POST; }
        $user = $this->getById('Exposure\Model\User', $_POST['user_id']);
        if (!is_a($user, 'Exposure\Model\User')) {
            return self::INVALID_POST;
        }
        $this->updateUserModerationStatusAndNotify($user);
        return self::SUCCESS;
    }

    protected function isNotificationRequestValid() {
        // post request valid?
        foreach (array('notification_type', 'notification_id') as $index) {
            if (!isset($_POST[$index])) {
                return false;
            }
        }

        return true;
    }

    protected function getNotification($notificationType, $notificationId) {
        switch ($_POST['notification_type']) {
        case Notification::TYPE_PROJECT_THEME_SUGGESTION:
            $repository = 'Exposure\Model\ProjectThemeSuggestionNotification';
            break;
        case Notification::TYPE_PROJECT_MODERATION:
            $repository = 'Exposure\Model\ProjectModerationNotification';
            break;
        case Notification::TYPE_PROFILE_MODERATION:
            $repository = 'Exposure\Model\ProfileModerationNotification';
            break;
        default:
            return null;
            break;
        }

        return $this->getById($repository, $_POST['notification_id']);
    }

    protected function getNotificationIfNotificationRequestValid($admin) {
        // signed in as admin?
        if (!isset($_SESSION['adminuser']['id'])) { return null; }
        
        // valid notification request?
        if (!$this->isNotificationRequestValid()) { return null; }

        // existing notification?
        if (is_null($notification = $this->getNotification($_POST['notification_type'], $_POST['notification_id']))) { return null; }

        // check if notification belongs to the admin
        if (!$admin->getNotifications()->contains($notification)) { return null; }

        return $notification;
    }

    protected function notificationUpdateStatus($status) {
        header('Content-Type: application/json');

        $admin = $this->getByLabel('Exposure\Model\Administration', $this->config->getParam('adminLabel'));

        $notification = $this->getNotificationIfNotificationRequestValid($admin);
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

    public function notificationDelete() {
        header('Content-Type: application/json');

        $admin = $this->getByLabel('Exposure\Model\Administration', $this->config->getParam('adminLabel'));

        $notification = $this->getNotificationIfNotificationRequestValid($admin);
        if (is_null($notification)) {
            echo json_encode(false);
            return;
        }

        $admin->getNotifications()->removeElement($notification);
        $this->config->getDocumentManager()->remove($notification);
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }

    protected function isProjectModerateStringRequestValid() {
        // post request valid?
        foreach (array('project_id', 'string', 'command') as $index) {
            if (!isset($_POST[$index])) {
                return false;
            }
        }
        
        // make sure that one command exists
        if (!in_array($_POST['command'], array('request_editing', 'approve'))) {
            return false;
        }

        // check if target string valid
        if (!in_array($_POST['string'], array('summary', 'audience_description', 'description'))) {
            return false;
        }

        return true;
    }

    protected function getProjectIfProjectModerateStringRequestValid() {
        // signed in?
        if (!isset($_SESSION['adminuser']['id'])) { return null; }
        
        if (!$this->isProjectModerateStringRequestValid()) { return null; }

        return $this->getById('Exposure\Model\Project', $_POST['project_id']);
    }

    public function projectModerateString() {
        header('Content-Type: application/json');
        if (is_null($project = $this->getProjectIfProjectModerateStringRequestValid())) {
            echo json_encode(false);
            return;
        }

        switch($_POST['command']) {
        case 'request_editing':
            $newStatus = ModerationStatus::STATUS_USER_EDIT;
            break;
        case 'approve':
            $newStatus = ModerationStatus::STATUS_APPROVED;
            break;
        }

        switch($_POST['string']) {
        case 'summary':
            $string = $project->getSummary();
            break;
        case 'description':
            $string = $project->getDescription();
            break;
        case 'audience_description':
            $string = $project->getAudienceDescription();
            break;
        }

        $string->getModerationStatus()->setStatus($newStatus);
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }

    protected function projectModerateIsValidPost() {
        if (!$this->postHasIndices(array('project_id', 'comment'))) {
            return false;
        }

        // make sure that one action exists
        foreach (array('request_editing', 'reject', 'approve') as $index) {
            if (isset($_POST[$index])) {
                return true;
            }
        }

        return false;
    }

    protected function sendProjectModerationEmail(Project $project, User $user, $content) {
        switch ($project->getModerationStatus()->getStatus()) {
        case ModerationStatus::STATUS_USER_EDIT:
        case ModerationStatus::STATUS_FIRST_USER_EDIT:
            $emailTemplate = $this->config->getTwig()->loadTemplate('project-moderation-needs-editing-email.tpl.html');
            break;
        case ModerationStatus::STATUS_REJECTED:
            $emailTemplate = $this->config->getTwig()->loadTemplate('project-moderation-rejected-email.tpl.html');
            break;
        case ModerationStatus::STATUS_APPROVED:
        default:
            $emailTemplate = $this->config->getTwig()->loadTemplate('project-moderation-approved-email.tpl.html');
            break;
        }

        $projectEditUrl = 'http://' . $this->config->getParam('hostname') 
                . '/project-edit/' . $project->getUrlSlug();
        
        $parameters = array (
            'projectEditUrl' => $projectEditUrl,
            'project' => $project,
        );

        if (!empty($content)) {
            $parameters['content'] = $content;
        }

        $subject  = $emailTemplate->renderBlock('subject',   $parameters);
        $bodyHtml = $emailTemplate->renderBlock('body_html', $parameters);
        $bodyText = $emailTemplate->renderBlock('body_text', $parameters);
        
        $message = \Swift_Message::newInstance()
            ->setFrom(array(
                $this->config->getParam('emailFromAddress') 
                    => $this->config->getParam('emailFromName'))
                )
            ->setSubject($subject)
            ->setBody($bodyText, 'text/plain')
            ->addPart($bodyHtml, 'text/html');
        
        $message->setTo($user->getEmail());
        return $this->config->getSwiftMailer()->send($message);
    }


    protected function updateProjectModerationStatusAndNotify(Project $project) {
        // init notification
        $notification = new ProjectModerationNotification;
        $notification->setStatus(ProjectModerationNotification::STATUS_UNREAD);
        if (!empty($_POST['comment'])) {
            $notification->setContent($_POST['comment']);
        }
        $notification->setProject($project);
        $notification->setDateTime(new \DateTime);

        // update status
        if (array_key_exists('request_editing', $_POST)) {
            switch ($project->getModerationStatus()->getStatus()) {
                case ModerationStatus::STATUS_SUBMITTED_FIRST_TIME:
                    $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_FIRST_USER_EDIT);
                    break;
                default:
                    $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
                    break;
            }
            $notification->setEvent(ProjectModerationNotification::EVENT_PROJECT_NEEDS_EDITING);
            $messageContent = 'needs editing'; 
                // $this->translate->_('projectModeration.status.needsEditing');
        }
        elseif (array_key_exists('reject', $_POST)) {
            $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_REJECT);
            $notification->setEvent(ProjectModerationNotification::EVENT_REJECTED_PROJECT);
            $messageContent = 'rejected'; 
                // $this->translate->_('projectModeration.status.rejected');
        }
        else {
            // if first time submission
            if ($project->getModerationStatus()->getStatus() == ModerationStatus::STATUS_SUBMITTED_FIRST_TIME) {
                // if any field still needs editing then fail
                // (not published and no previously published value to fall back on)
                if ($project->getSummary()->getModerationStatus()->getStatus() == ModerationStatus::STATUS_USER_EDIT) { return false; }
                if ($project->getDescription()->getModerationStatus()->getStatus() == ModerationStatus::STATUS_USER_EDIT) { return false; }
                if ($project->getAudienceDescription()->getModerationStatus()->getStatus() == ModerationStatus::STATUS_USER_EDIT) { return false; }
                foreach ($project->getPhotos() as $photo) {
                    if ($photo->getModerationStatus()->getStatus() == ModerationStatus::STATUS_USER_EDIT) { return false; }
                }
            }

            // auto-approve submitted fields
            if ($project->getSummary()->getModerationStatus()->getStatus() == ModerationStatus::STATUS_SUBMITTED) {
                $project->getSummary()->getModerationStatus()->setStatus(ModerationStatus::STATUS_APPROVED);
            }
            if ($project->getDescription()->getModerationStatus()->getStatus() == ModerationStatus::STATUS_SUBMITTED) {
                $project->getDescription()->getModerationStatus()->setStatus(ModerationStatus::STATUS_APPROVED);
            }
            if ($project->getAudienceDescription()->getModerationStatus()->getStatus() == ModerationStatus::STATUS_SUBMITTED) {
                $project->getAudienceDescription()->getModerationStatus()->setStatus(ModerationStatus::STATUS_APPROVED);
            }
            foreach ($project->getPhotos() as $photo) {
                if ($photo->getModerationStatus()->getStatus() == ModerationStatus::STATUS_SUBMITTED) { 
                    $photo->getModerationStatus()->setStatus(ModerationStatus::STATUS_APPROVED);
                }
            }

            $project->getSummary()->getModerationStatus()
                ->setStatus(ModerationStatus::STATUS_APPROVED);
            $project->getDescription()->getModerationStatus()
                ->setStatus(ModerationStatus::STATUS_APPROVED);
            $project->getAudienceDescription()->getModerationStatus()
                ->setStatus(ModerationStatus::STATUS_APPROVED);

            $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_APPROVED);
            $notification->setEvent(ProjectModerationNotification::EVENT_APPROVED_PROJECT);
            $messageContent = 'approved'; 
                // $this->translate->_('projectModeration.status.approved');
        }

        // attach notification to project
        $this->config->getDocumentManager()->persist($notification);
        $project->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to project owners if req'd
        foreach ($project->getOwners() as $owner) {
            if ($owner->getReceiveNotificationsByEmail()) {
                $this->sendProjectModerationEmail($project, $owner, $_POST['comment']);
            }
        }

        $_SESSION['message'] = array (
                'content' => 'project has been moderated – ' . $messageContent,
                // 'content' => $this->translate->_('projectModeration.success.moderatedProject'),
                'type' => 'success');

        return true;
    }

    public function projectModerate() {
        if (!isset($_SESSION['adminuser']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->projectModerateIsValidPost()) { return self::INVALID_POST; }
        if (is_null($project = $this->getById('Exposure\Model\Project', $_POST['project_id']))) {
            return self::INVALID_POST;
        }
        if ($this->updateProjectModerationStatusAndNotify($project)) {
            return self::SUCCESS;
        }

        $_SESSION['message'] = array (
            'content' => 'project cannot be moderated as it stands',
            // 'content' => $this->translate->_('projectModeration.error.notUpdatable'),
            'type' => 'error');
        $_SESSION['request'] = $project->getUrlSlug();
        return self::INVALID_DATA;
    }

    protected function isProjectModeratePhotoRequestValid() {
        // post request valid?
        foreach (array('project_id', 'photo_id', 'command') as $index) {
            if (!isset($_POST[$index])) {
                return false;
            }
        }
        
        // make sure that one command exists
        if (!in_array($_POST['command'], array('request_editing', 'approve'))) {
            return false;
        }

        return true;
    }

    protected function getPhotoIfProjectModeratePhotoRequestValid() {
        // signed in?
        if (!isset($_SESSION['adminuser']['id'])) { return null; }

        if (!$this->isProjectModeratePhotoRequestValid()) { return null; }

        // does project exist?
        if (is_null($project = $this->getById('Exposure\Model\Project', $_POST['project_id']))) {
            return null;
        }

        // return photo if photo (by id) found in project
        foreach ($project->getPhotos() as $photo) {
            if ($photo->getId() == $_POST['photo_id']) {
                return $photo;
            }
        }

        return null;
    }

    public function projectModeratePhoto() {
        header('Content-Type: application/json');
        if (is_null($photo = $this->getPhotoIfProjectModeratePhotoRequestValid())) {
            echo json_encode(false);
            return;
        }

        switch($_POST['command']) {
        case 'request_editing':
            $newStatus = ModerationStatus::STATUS_USER_EDIT;
            break;
        case 'approve':
            $newStatus = ModerationStatus::STATUS_APPROVED;
            break;
        }

        $photo->getModerationStatus()->setStatus($newStatus);
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }

}


