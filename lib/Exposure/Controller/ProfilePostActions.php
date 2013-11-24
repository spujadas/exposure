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
    Sociable\Utility\StringValidator,
    Sociable\Utility\StringException,
    Exposure\Model\ProfileModerationNotification,
    Exposure\Model\ModerationStatus;

class ProfilePostActions extends \Exposure\Controller\PostActions {
    protected $errors;
    protected $autofill;

    protected function firstTimeSaveIsValidPost() {
        return $this->postHasIndices(array('name', 'surname', 'country', 
            'presentation'));
    }

    protected function saveIsValidPost() {
        return $this->postHasIndices(array('name', 'surname', 'email', 
            'email_check', 'country', 'presentation'));
    }

    protected function passwordChangeIsValidPost() {
        return $this->postHasIndices(array('old_password', 'password', 
            'password_check'));
    }

    protected function updateReceiveNotificationsByEmailProjectOwner(User $user, $enabled) {
        $user->getProjectOwnerPreferences()
            ->setReceiveNotificationsByEmail($enabled);
    }

    protected function updateReceiveNotificationsByEmailWhenWanted(User $user, $enabled) {
        $user->getProjectOwnerPreferences()
            ->setReceiveNotificationsByEmailWhenWanted($enabled);
    }

    protected function updateReceivePeriodicDigestByEmailProjectOwner(User $user, $enabled) {
        $user->getProjectOwnerPreferences()
            ->setReceivePeriodicDigestByEmail($enabled);
    }
    
    protected function updateReceiveNewsletter(User $user, $enabled) {
        $user->getProjectOwnerPreferences()
            ->setReceiveNewsletter($enabled);
    }

    protected function updateReceiveNotificationsWhenCommented(User $user, $enabled) {
        $user->getProjectOwnerPreferences()
            ->setReceiveNotificationsWhenCommented($enabled);
    }

    protected function updateReceiveNotificationsWhenSubscriptionWillExpire(User $user, $enabled) {
        $user->getProjectOwnerPreferences()
            ->setReceiveNotificationsWhenSubscriptionWillExpire($enabled);
    }

    protected function updateReceiveNotificationsByEmailSponsor(User $user, $enabled) {
        $user->getSponsorUserPreferences()
            ->setReceiveNotificationsByEmail($enabled);
    }

    protected function updateReceivePeriodicDigestByEmailSponsor(User $user, $enabled) {
        $user->getSponsorUserPreferences()
            ->setReceivePeriodicDigestByEmail($enabled);
    }
    
    protected function updateReceiveDailyDigestByEmail(User $user, $enabled) {
        $user->getSponsorUserPreferences()
            ->setReceiveDailyDigestByEmail($enabled);
    }

    protected function updateName(User $user, $name) {
        try {
            $user->setName($name);
        }
        catch (\Exception $e) {
            $this->errors['name'] = 'this name is invalid';
                // $this->translate->_('profileSave.error.invalidName');
        }
    }
  
    protected function updateSurname(User $user, $surname) {
        try {
            $user->setSurname($surname);
        }
        catch (\Exception $e) {
            $this->errors['surname'] = 'this surname is invalid';
                // $this->translate->_('profileSave.error.invalidSurname');
        }
    }

    protected function updateEmail(User $user, $email, $emailCheck) {
        // matching email addresses
        if ($email != $emailCheck) {
            $this->errors['email_check'] =
                    'email addresses do not match';
                   // $this->translate->_('profileSave.error.emailAddressesDoNotMatch');
        }
        
        $currentEmail = $user->getEmail();
        
        // valid email
        try {
            $user->setEmail($email);
        }
        catch (\Exception $e) {
            $this->errors['email'] = 'this email address is invalid';
                // $this->translate->_('profileSave.error.invalidEmail');
            return;
        }

        // email availability
        if(($email != $currentEmail) 
                && (!is_null($this->getByEmail('Exposure\Model\User', $email)))) {
            $this->errors['email'] =
                    'this email address is already in use';
            //        $this->translate->_('profileSave.error.unavailableEmailAddress');
        }
    }

    protected function updatePassword(User $user, $oldPassword, 
        $password, $passwordCheck) {
        // old password correct
        if (!$this->isPasswordCorrectForUser($user, $oldPassword)) {
            $this->errors['old_password'] = 'incorrect password';
                // $this->translate->_('profileSave.error.incorrectPassword');
        }
        
        // matching passwords
        if ($password != $passwordCheck) {
            $this->errors['password_check'] =
                    'passwords do not match';
                    // $this->translate->_('profileSave.error.passwordsDoNotMatch');
        }

        // valid password
        $passwordAuthenticator = new \Sociable\Model\PasswordAuthenticator;
        try {
            $passwordAuthenticator->setParams(array('password' => $password));
            $user->setAuthenticator($passwordAuthenticator);
        }
        catch (StringException $e) {
            switch ($e->getMessage()) {
            case StringValidator::EXCEPTION_TOO_SHORT:
                $this->errors['password'] = 'this password is too short';
                    // $this->translate->_('profileSave.error.passwordTooShort');
                break;
            case StringValidator::EXCEPTION_TOO_LONG:
                $this->errors['password'] = 'this password is too long';
                    // $this->translate->_('profileSave.error.passwordTooLong');
                break;
            default:
                $this->errors['password'] = 'this password is invalid';
                    // $this->translate->_('profileSave.error.invalidPassword');
                break;
            }
        }

    }

    protected function updateUserPlace(User $user, $locationLabel, $countryCode) {
        if (!empty($locationLabel)) {
            $this->updatePlaceAsLocation($user, 'setPlace', $locationLabel,
                array(
                    'error_field' => 'location',
                    'error_message' => 'location is invalid', // $this->translate->_('profileSave.error.invalidLocation'),
                )
            );
            $this->autofill['location'] = $locationLabel;
            return;
        }

        $this->updatePlaceAsCountry($user, 'setPlace', $countryCode,
            array(
                'error_field' => 'country',
                'error_message' => 'country missing', // $this->translate->_('profileSave.error.missingCountry'),
            )
        );
        $this->autofill['country'] = $countryCode;
        return;
    }

    protected function updatePhoto (User $user, $errorCode, $filePath, $fileSize, $descriptionString, $languageCode) {
        $fileErrorArray = array (
            'error_field' => 'photo',
            'error_messages' => array (
                PostActions::ERROR_MISSING_FILE => 'file missing', // $this->translate->_('profileSave.error.missingFile');
                PostActions::ERROR_FILE_TOO_LARGE => 'file is too large', // $this->translate->_('profileSave.error.photoFileTooLarge');
                PostActions::ERROR_UPLOAD_ERROR => 'upload error', // $this->translate->_('profileSave.error.uploadError');
                PostActions::ERROR_INVALID_FILE_TYPE => 'file type is invalid', // $this->translate->_('profileSave.error.photoInvalidFileType');
            ),
        );

        $descriptionStringExceptionArray = array (
            'error_field' => 'photo',
            'default_error_message' => 'cannot save this photo', // $this->translate->_('profileSave.error.cannotSavePhoto');
        );

        // update image and description
        if ($this->upsertLabelledImageInObject($user, 'getPhoto', 'setPhoto',
            $errorCode, $filePath, $fileSize,
            User::PHOTO_MAX_SIZE, $fileErrorArray, 
            $descriptionString, $languageCode, $descriptionStringExceptionArray)) {
            $user->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
        }
    }

    protected function updatePresentation(User $user, $presentation,
        $languageCode) {
        $stringExceptionArray = array (
            'error_field' => 'presentation',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'presentation is missing', // $this->translate->_('profileSave.error.emptyPresentation'),
                StringValidator::EXCEPTION_TOO_LONG => 'this presentation is too long', // $this->translate->_('profileSave.error.presentationTooLong'),
            ),
            'default_error_message' => 'this presentation is invalid', // $this->translate->_('profileSave.error.invalidPresentation'),
        );
        if ($this->updateMultiLanguageString($user, 'getPresentation', 'setPresentation',
            $presentation, $languageCode, $stringExceptionArray)) {
            $user->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
        }
    }
    
    protected function firstTimeSaveUpdateUser() {
        $this->errors = array();
        
        // retrieve user by current id
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);
        
        // update object
        $this->updateName($user, $_POST['name']);
        $this->updateSurname($user, $_POST['surname']);
        $this->updatePhoto($user, 
            $_FILES['photo']['error'], $_FILES['photo']['tmp_name'], $_FILES['photo']['size'],
            $_POST['name']. ' ' . $_POST['surname'], $user->getLanguageCode());
        $this->updatePresentation($user, $_POST['presentation'],
            $user->getLanguageCode());
        $this->updateUserPlace($user,
            array_key_exists('location', $_POST)?$_POST['location']:null,
            $_POST['country']);
        
        // clear dm in case of errors
        if ($this->errors) {
            $this->config->getDocumentManager()->clear();
        }
        // flush otherwise
        else {
            if ($user->getType() == User::TYPE_PROJECT_OWNER) {
                $user->setFirstTime(User::FIRST_TIME_PROJECT);
            }
            else {
                $user->setFirstTime(User::FIRST_TIME_ORGANISATION);
            }
            $this->config->getDocumentManager()->flush();
        } 
        
        $_SESSION['errors'] = $this->errors;
        $_SESSION['autofill'] = array (
            'name' => $_POST['name'],
            'surname' => $_POST['surname'],
            'country' => $_POST['country'],
            'presentation' => $_POST['presentation'],
        );
        
    }
    
    protected function updateUser(User $user) {
        $this->errors = array();
        
        // update name, surname, email, password, place, photo, presentation
        $this->updateName($user, $_POST['name']);
        $this->updateSurname($user, $_POST['surname']);
        $this->updateEmail($user, $_POST['email'], $_POST['email_check']);

        if (array_key_exists('photo', $_FILES)) {
            $this->updatePhoto($user, 
                $_FILES['photo']['error'], $_FILES['photo']['tmp_name'], $_FILES['photo']['size'],
                $_POST['name']. ' ' . $_POST['surname'], $user->getLanguageCode());
        }
        elseif (is_null($user->getLogo())) {
            $this->errors['photo'] = 'missing photo';
        }

        $this->updatePresentation($user, $_POST['presentation'],
            $user->getLanguageCode());
        $this->updateUserPlace($user,
            array_key_exists('location', $_POST)?$_POST['location']:null,
            $_POST['country']);

        // clear dm in case of errors
        if ($this->errors) {
            $this->config->getDocumentManager()->clear();
        }
        // flush otherwise
        else {
            $this->config->getDocumentManager()->flush();
        } 
        
        $_SESSION['errors'] = $this->errors;
        $_SESSION['autofill'] = array (
            'name' => $_POST['name'],
            'surname' => $_POST['surname'],
            'email' => $_POST['email'],
            'email_check' => $_POST['email_check'],
            'country' => $_POST['country'],
            'presentation' => $_POST['presentation'],
        );        
    }
    
    protected function passwordChangeUpdateUser() {
        $this->errors = array();
        
        // retrieve user by current id
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);
        
        // update password
        $this->updatePassword($user, $_POST['old_password'], 
            $_POST['password'], $_POST['password_check']);
        
        // clear dm in case of errors
        if ($this->errors) {
            $this->config->getDocumentManager()->clear();
        }
        // flush otherwise
        else {
            $this->config->getDocumentManager()->flush();
        } 
        
        $_SESSION['errors'] = $this->errors;        
    }
    
    protected function isPasswordCorrectForUser($user, $password) {
        // test for password authenticator
        $authenticator = $user->getAuthenticator();
        if ($authenticator->getType() != \Sociable\Model\Authenticator::PASSWORD_AUTHENTICATOR) {
            return false;
        }
        
        // test password
        try {
            if (!$authenticator->authenticate(array('password' => $password))) {
                return false;
            }
        }
        catch (\Exception $e) {
            return false;
        }
        
        return true;
    }

    protected function setSession() {
        if ($this->errors) {
             $_SESSION['errors'] = $this->errors;
             $_SESSION['message'] = array (
                 'content' => 'some fields are incorrectly filled in',
                 // 'content' => $this->translate->_('profileSave.error.incorrectFields'),
                 'type' => 'error');

             return self::INVALID_DATA;
        }
            
        $_SESSION['message'] = array (
                'content' => 'profile saved',
                // 'content' => $this->translate->_('profileSave.success.profileSaved'),
                'type' => 'success');
        unset($_SESSION['autofill']);

        return self::SUCCESS;
    }

    public function save() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->saveIsValidPost()) { return self::INVALID_POST; }
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);
        if (!$this->isProfileEditable($user)) { return self::PROFILE_NOT_EDITABLE; }
        $this->updateUser($user);
        return $this->setSession();
    }

    public function firstSave() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->firstTimeSaveIsValidPost()) { return self::INVALID_POST; }
        $this->firstTimeSaveUpdateUser();
        return $this->setSession();
    }

    public function passwordChange() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->passwordChangeIsValidPost()) { return self::INVALID_POST; }

        $this->passwordChangeUpdateUser();

        if ($this->errors) {
             $_SESSION['errors'] = $this->errors;
             $_SESSION['message'] = array (
                 'content' => 'some fields are incorrectly filled in',
                 // 'content' => $this->translate->_('passwordChange.error.incorrectFields'),
                 'type' => 'error');

             return self::INVALID_DATA;
        }
            
        $_SESSION['message'] = array (
                'content' => 'your password was updated',
                // 'content' => $this->translate->_('passwordChange.success.passwordChanged'),
                'type' => 'success');
        unset($_SESSION['autofill']);

        return self::SUCCESS;
    }

    protected function isProfileEditable(User $user) {
        switch ($user->getModerationStatus()->getStatus()) {
        case ModerationStatus::STATUS_FLAGGED:
            $_SESSION['message'] = array (
             'content' => 'your profile has been flagged as inappropriate - it is being reviewed',
             // 'content' => $this->translate->_('profilePublish.warning.flaggedProfile'),
             'type' => 'warning');
            return false;
            break;
        case ModerationStatus::STATUS_REJECTED:
            $_SESSION['message'] = array (
             'content' => 'your profile has been rejected',
             // 'content' => $this->translate->_('profilePublish.error.rejectedProfile'),
             'type' => 'error');
            return false;
            break;
        }
        return true;
    }

    protected function isProfilePublishable(User $user) {
        if (!$this->isProfileEditable($user)) {
            return false;
        }
        switch ($user->getModerationStatus()->getStatus()) {
        case ModerationStatus::STATUS_SUBMITTED:
            $_SESSION['message'] = array (
             'content' => 'your profile has already been submitted',
             // 'content' => $this->translate->_('profilePublish.warning.userAlreadySubmitted'),
             'type' => 'warning');
            return false;
            break;
        case ModerationStatus::STATUS_APPROVED:
            $_SESSION['message'] = array (
             'content' => 'your profile has already been approved',
             // 'content' => $this->translate->_('profilePublish.warning.userAlreadyApproved'),
             'type' => 'warning');
            return false;
            break;
        }
        return true;
    }

    public function profilePublish() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }

        // get current user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);

        if (!$this->isProfilePublishable($user)) {
            return self::PROFILE_NOT_PUBLISHABLE;
        }

        // update status
        $user->getModerationStatus()->setStatus(ModerationStatus::STATUS_SUBMITTED);
        
        // now notify the admin
        $admin = $this->getByLabel('Exposure\Model\Administration', $this->config->getParam('adminLabel'));
        $notification = new ProfileModerationNotification;
        $notification->setStatus(ProfileModerationNotification::STATUS_UNREAD);
        $notification->setContent($user->getName() . ' ' . $user->getSurname());
        $notification->setEvent(ProfileModerationNotification::EVENT_SUBMITTED_PROFILE);
        $notification->setDateTime(new \DateTime);
        $notification->setUser($user);
        $this->config->getDocumentManager()->persist($notification);
        $admin->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        $_SESSION['message'] = array (
            'content' => 'your profile has been submitted',
            // 'content' => $this->translate->_('profilePublish.sucess.submittedProfile'),
            'type' => 'success');

        return self::SUCCESS;
    }

    public function preferencesSave() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }

        // get user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);

        // update preferences
        if ($user->getType() == User::TYPE_PROJECT_OWNER) {
            $this->updateReceiveNotificationsByEmailProjectOwner($user, 
                isset($_POST['receive_notifications_by_email']));
            $this->updateReceiveNotificationsByEmailWhenWanted($user, 
                isset($_POST['receive_notifications_by_email_when_wanted']));
            $this->updateReceivePeriodicDigestByEmailProjectOwner($user, 
                isset($_POST['receive_periodic_digest_by_email_project_owner']));
            $this->updateReceiveNewsletter($user, 
                isset($_POST['receive_newsletter']));
            $this->updateReceiveNotificationsWhenCommented($user, 
                isset($_POST['receive_notifications_when_commented']));
            $this->updateReceiveNotificationsWhenSubscriptionWillExpire($user, 
                isset($_POST['receive_notifications_when_subscription_will_expire'])) ;
        }
        if ($user->getType() == User::TYPE_SPONSOR) {
            $this->updateReceiveNotificationsByEmailSponsor($user, 
                isset($_POST['receive_notifications_by_email']));
            $this->updateReceivePeriodicDigestByEmailSponsor($user, 
                isset($_POST['receive_periodic_digest_by_email_sponsor']));
            $this->updateReceiveDailyDigestByEmail($user, 
                isset($_POST['receive_daily_digest_by_email']));
        }
        
        // flush and success message
        $this->config->getDocumentManager()->flush();
        $_SESSION['message'] = array (
                'content' => 'preferences saved',
                // 'content' => $this->translate->_('preferencesSave.success.preferencesSaved'),
                'type' => 'success');
        return self::SUCCESS;
    }

}