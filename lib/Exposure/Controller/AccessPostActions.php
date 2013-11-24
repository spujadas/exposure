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
    Exposure\Model\ModerationStatus,
    Sociable\Model\PasswordAuthenticator,
    Sociable\Utility\StringValidator,
    Sociable\Utility\StringException;

class AccessPostActions extends \Sociable\Controller\PostActions {
    protected $errors;

    protected function signupIsValidPost() {
        return $this->postHasIndices(array('password', 'password_check', 'email', 
            'email_check', 'usertype'));
    }

    protected function signupCreateUser() {
        $this->errors = array();
        
        // matching passwords
        if ($_POST['password'] != $_POST['password_check']) {
            $this->errors['password_check'] =
                    'passwords do not match';
                    // $this->translate->_('signup.error.passwordsDoNotMatch');
        }

        // matching email addresses
        if ($_POST['email'] != $_POST['email_check']) {
            $this->errors['email_check'] =
                    'email addresses do not match';
                   // $this->translate->_('signup.error.emailAddressesDoNotMatch');
        }
        
        $user = new User();

        // valid email
        try {
            $user->setEmail($_POST['email']);
        }
        catch (\Exception $e) {
            $this->errors['email'] = 'email address is invalid';
                // $this->translate->_('signup.error.invalidEmail');
        }

        // password
        $passwordAuthenticator = new PasswordAuthenticator;
        try {
            $passwordAuthenticator->setParams(array('password' => $_POST['password']));
            $user->setAuthenticator($passwordAuthenticator);
        }
        catch (StringException $e) {
            switch ($e->getMessage()) {
            case StringValidator::EXCEPTION_TOO_SHORT:
                $this->errors['password'] = 'password is too short';
                    // $this->translate->_('signup.error.passwordTooShort');
                break;
            case StringValidator::EXCEPTION_TOO_LONG:
                $this->errors['password'] = 'password is too long';
                    // $this->translate->_('signup.error.passwordTooLong');
                break;
            default:
                $this->errors['password'] = 'password is invalid';
                    // $this->translate->_('signup.error.invalidPassword');
                break;
            }
        }
        
        // type
        switch($_POST['usertype']) {
        case 'projectowner':
            $user->setType(User::TYPE_PROJECT_OWNER);
            $user->setProjectOwnerPreferences(new \Exposure\Model\ProjectOwnerPreferences);
            break;
        case 'sponsor':
            $user->setType(User::TYPE_SPONSOR);
            $user->setSponsorUserPreferences(new \Exposure\Model\SponsorUserPreferences);
            break;
        default:
            $this->errors['usertype'] = 'type is invalid';
                // $this->translate->_('signup.error.invalidType');
            break;
        }

        // T&Cs
        if (!isset($_POST['terms_and_conditions_accepted'])) {
            $this->errors['tcs'] = 'terms and conditions must be accepted';
                // $this->translate->_('signup.error.tCsNotAccepted');
        }
        
        // any errors?
        if ($this->errors) {
            return null;
        }
        
        $user->setStatus(User::STATUS_REGISTERED);
        $moderationStatus = new ModerationStatus;
        $moderationStatus->setStatus(ModerationStatus::STATUS_USER_EDIT);
        $user->setModerationStatus($moderationStatus);
        $user->setRegistrationDateTime(new \DateTime);
        $user->setFirstTime(User::FIRST_TIME_PROFILE);
        $user->setEmailConfirmationCode(new \Sociable\Model\ConfirmationCode);
        $user->setLanguageCode($this->config->getParam('defaultLanguageCode'));
        $user->setCurrencyCode($this->config->getParam('defaultCurrencyCode'));
        
        return $user;
    }

    public function signup() {
        if (isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are already signed in',
                // 'content' => $this->translate->_('signup.warning.alreadySignedIn'),
                'type' => 'warning');
            return self::ALREADY_SIGNED_IN;
        }
        
        // check $_POST
        if (!$this->signupIsValidPost()) {
            return self::INVALID_POST;
        }

        // create user
        $user = $this->signupCreateUser();

        if (is_null($user)) {
            $_SESSION['errors'] = $this->errors;
            $_SESSION['message'] = array (
                'content' => 'some fields are incorrectly filled in',
                // 'content' => $this->translate->_('signup.errors'),
                'type' => 'error');

            // fill in autofill
            $_SESSION['autofill'] = array (
                'usertype' => $_POST['usertype'],
                'email' => $_POST['email'],
                'email_check' => $_POST['email_check'],
            );
        
            return self::INVALID_DATA;
        }

        // email availability
        if(!is_null($this->getByEmail('Exposure\Model\User', $_POST['email']))) {
            $this->errors['email'] =
                    'this email address is already in use';
            //        $this->translate->_('signup.error.unavailableEmailAddress');
            $_SESSION['errors'] = $this->errors;
            $_SESSION['message'] = array (
                'content' => 'this email address is already in use, click on "Sign in" to sign in using this address',
                // 'content' => $this->translate->_('signup.warning.redirectToSignin'),
                'type' => 'warning');
            
            // fill in autofill
            $_SESSION['autofill'] = array (
                'usertype' => $_POST['usertype'],
                'email' => $_POST['email'],
                'email_check' => $_POST['email_check'],
            );
            
            return self::INVALID_DATA;
        }

        // persist and flush
        $this->config->getDocumentManager()->persist($user);
        $this->config->getDocumentManager()->flush();
        
        SessionUtils::setUserSessionParams($user);
        $_SESSION['state']['newuser'] = true;
        
        // send confirmation email
        $result = $this->sendConfirmationEmail($user);
        
        if (!$result) {
            return self::SEND_FAILED;
        }
        
        return self::SUCCESS;
    }
    
    protected function setNewPasswordIsValidPost() {
        return $this->postHasIndices(array('password', 'password_check'));
    }
    
    public function setNewPassword() {
        // signed in
        if (!isset($_SESSION['user']['id'])) {
            return self::NOT_SIGNED_IN;
        }

        // check $_POST
        if (!$this->setNewPasswordIsValidPost()) {
            return self::INVALID_POST;
        }
        
        // check for password reset token
        if (!isset($_SESSION['state']['passwordresettoken'])) {
            return self::INVALID_SESSION;
        }
        
        // retrieve user by id
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);
        
        // no user for id
        if(is_null($user)) {
            $this->errors['email'] = 'no user is registered to this email address';
                // $this->translate->_('resendConfirmationEmail.error.nonexistentUser');
            return self::INVALID_SESSION;
        }
        
        // validate user
        $user->validatePartial();
        
        $this->errors = array();
        
        // matching passwords
        if ($_POST['password'] != $_POST['password_check']) {
            $this->errors['password_check'] =
                    'passwords do not match';
                    // $this->translate->_('setNewPassword.error.passwordsDoNotMatch');
        }

        // password
        try {
            $authenticator = new PasswordAuthenticator;
            $authenticator->setParams(array('password' => $_POST['password']));
        }
        catch (StringException $e) {
            switch ($e->getMessage()) {
            case StringValidator::EXCEPTION_TOO_SHORT:
                $this->errors['password'] = 'this password is too short';
                    // $this->translate->_('setNewPassword.error.passwordTooShort');
                break;
            case StringValidator::EXCEPTION_TOO_LONG:
                $this->errors['password'] = 'this password is too long';
                    // $this->translate->_('setNewPassword.error.passwordTooLong');
                break;
            default:
                $this->errors['password'] = 'this password is invalid';
                    // $this->translate->_('setNewPassword.error.invalidPassword');
                break;
            }
        }
        
        if ($this->errors) {
            $_SESSION['errors'] = $this->errors;
            $_SESSION['message'] = array (
                'content' => 'some fields are incorrectly field in',
                // 'content' => $this->translate->_('signup.error.incorrectFields'),
                'type' => 'error');

            return self::INVALID_DATA;
        }
        
        // update password and remove reset code
        $user->setAuthenticator($authenticator);
        $user->setPasswordResetCode();

        // persist to DB
        $this->config->getDocumentManager()->flush();
        
        unset($_SESSION['state']['passwordresettoken']);
                
        return self::SUCCESS;
    }
    
    protected function resetPasswordIsValidPost() {
        return $this->postHasIndices(array('email'));
    }
    
    public function resetPassword() {
        // check $_POST
        if (!$this->resetPasswordIsValidPost()) {
            return self::INVALID_POST;
        }
        
        // retrieve user by email
        $user = $this->getByEmail('Exposure\Model\User', $_POST['email']);
        
        // no user for entered email
        if(is_null($user)) {
            $this->errors['email'] = 'no user is registered to this email address';
                // $this->translate->_('resetPassword.error.nonexistentUser');
            $_SESSION['errors'] = $this->errors;
            return self::INVALID_DATA;
        }
        
        // validate user
        $user->validatePartial();

        // test for password authenticator
        $authenticator = $user->getAuthenticator();
        if ($authenticator->getType() != \Sociable\Model\Authenticator::PASSWORD_AUTHENTICATOR) {
            $this->errors['password'] = 'password-based authentication is not supported for this user';
                // $this->translate->_('resetPassword.error.authenticatorMismatch');
            $_SESSION['errors'] = $this->errors;
            $this->config->getLogger()->addError(__FUNCTION__.' in '.__FILE__.' at '.__LINE__
                    . ' - ' . $authenticator->getType());
            return self::INVALID_DATA;
        }
        
        // generate password reset code
        $user->setPasswordResetCode(new \Sociable\Model\ConfirmationCode);
        
        // flush
        $this->config->getDocumentManager()->flush();
        
        $_SESSION['state']['passwordresetrequest'] = true;
                
        // send password reset email
        $result = $this->sendPasswordResetEmail($user);
        
        if (!$result) {
            return self::SEND_FAILED;
        }
        
        return self::SUCCESS;
    }
    
    protected function sendPasswordResetEmail(User $user) {
        $emailTemplate = $this->config->getTwig()->loadTemplate('password-reset-email.tpl.html');
        
        $resetUrl = 'http://' . $this->config->getParam('hostname') 
                . '/reset-password/email=' . $user->getEmail() 
                . '&code=' . $user->getPasswordResetCode()->getConfirmationCode();
        
        $parameters = array('reset_url' => $resetUrl);
        
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
    
    public function resendConfirmationEmail() {
        // check $_SESSION
        if (!isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are not signed in, please sign in',
                // 'content' => $this->translate->_('resendConfirmationEmail.warning.notSignedIn'),
                'type' => 'warning');
            return self::NOT_SIGNED_IN;
        }
        
        // retrieve user by id
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);
        
        // no user for id
        if(is_null($user)) {
            $this->errors['email'] = 'no user is registered to this email address';
                // $this->translate->_('resendConfirmationEmail.error.nonexistentUser');
            return self::INVALID_SESSION;
        }
        
        // validate user
        $user->validatePartial();
        
        // check for confirmation code
        if (is_null($user->getEmailConfirmationCode())) {
            $_SESSION['message'] = array (
                'content' => 'your email address has already been validated',
                // 'content' => $this->translate->_('resendConfirmationEmail.warning.alreadyValidated'),
                'type' => 'warning');
            return self::ALREADY_VALIDATED;
        }
        
        // send confirmation email
        $result = $this->sendConfirmationEmail($user);
        
        if (!$result) {
            return self::SEND_FAILED;
        }
        
        return self::SUCCESS;
    }
    
    protected function sendConfirmationEmail(User $user) {
        $emailTemplate = $this->config->getTwig()->loadTemplate('confirmation-email.tpl.html');
        
        $activationUrl = 'http://' . $this->config->getParam('hostname') 
                . '/confirm-email/email=' . $user->getEmail() 
                . '&code=' . $user->getEmailConfirmationCode()->getConfirmationCode();
        
        $parameters = array('activation_url' => $activationUrl);
        
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
    
    protected function signinIsValidPost() {
        return $this->postHasIndices(array('password', 'email'));
    }

    protected function signinIsAuthenticated() {
        $this->errors = array();
        
        // retrieve user by email
        $user = $this->getByEmail('Exposure\Model\User', $_POST['email']);
        
        // no user for entered email
        if(is_null($user)) {
            $this->errors['email'] = 'no user is registered to this email address';
                // $this->translate->_('signin.error.nonexistentUser');
            return self::INVALID_DATA;
        }
        
        // validate user
        $user->validatePartial();

        // test for password authenticator
        $authenticator = $user->getAuthenticator();
        if ($authenticator->getType() != \Sociable\Model\Authenticator::PASSWORD_AUTHENTICATOR) {
            $this->errors['password'] = 'password-based authentication is not supported for this user';
                // $this->translate->_('signin.error.authenticatorMismatch');
            $this->config->getLogger()->addError(__FUNCTION__.' in '.__FILE__.' at '.__LINE__
                    . ' - ' . $authenticator->getType());
            return self::INVALID_DATA;
        }
        
        // test password
        try {
            $authenticates = $authenticator->authenticate(array('password' => $_POST['password']));
        }
        catch (\Exception $e) {
            $this->errors['password'] = 'password is invalid';
                // $this->translate->_('signin.error.invalidPassword');
            return self::INVALID_DATA;
        }
        
        if (!$authenticates) {
            $this->errors['password'] = 'password is incorrect';
                // $this->translate->_('signin.error.incorrectPassword');
            return self::INVALID_DATA;
        }
        
        SessionUtils::setUserSessionParams($user);
        return self::SUCCESS;
    }

    public function signin() {
        if (isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are already signed in',
                // 'content' => $this->translate->_('signup.warning.alreadySignedIn'),
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
                // 'content' => $this->translate->_('signin.success.connected'),
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
                // 'content' => $this->translate->_('signin.errors'),
                'type' => 'error');

            // fill in autofill
            $_SESSION['autofill'] = array (
                'email' => $_POST['email'],
            );
        }
        
        return $result;
    }


}


