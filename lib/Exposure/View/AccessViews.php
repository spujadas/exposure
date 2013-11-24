<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\View;

class AccessViews extends View {
    /**************
        Sign-up
    */
    protected function signUpPreRoute() {
        // already signed in => go home
        if (isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are already signed in',
                // 'content' => $this->translate->_('signup.warning.alreadySignedIn'),
                'type' => 'warning');
            return self::ALREADY_SIGNED_IN;
        }
    }

    public function signUp() {
        if ($preRouting = $this->signupPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('sign-up.tpl.html');
        $this->displayTemplate(array());

        unset($_SESSION['errors']);
        unset($_SESSION['autofill']);
    }

    /*************
        Sign-in
    */

    protected function signInPreRoute() {
        // already signed in => go home
        if (isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are already signed in',
                // 'content' => $this->translate->_('signin.warning.alreadySignedIn'),
                'type' => 'warning');
            return self::ALREADY_SIGNED_IN;
        }
    }
    
    public function signIn() {
        if ($preRouting = $this->signInPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('sign-in.tpl.html');
        $this->displayTemplate(array(
            
    
        ));

        unset($_SESSION['errors']);
        unset($_SESSION['autofill']);
    }

    /********************
        Lost password
    */
    
    protected function lostPasswordPreRoute() {
        // already signed in => go home
        if (isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are already signed in',
                // 'content' => $this->translate->_('lostPassword.warning.alreadySignedIn'),
                'type' => 'warning');
            return self::ALREADY_SIGNED_IN;
        }
    }

    public function lostPassword() {
        if ($preRouting = $this->lostPasswordPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('lost-password.tpl.html');
        $this->displayTemplate(array());

        unset($_SESSION['errors']);
        unset($_SESSION['autofill']);
    }


    /*****************************
        Awaiting password reset
    */ 

    protected function awaitingPasswordResetPreRoute() {
        // already signed in => go home
        if (isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are already signed in',
                // 'content' => $this->translate->_('awaitingPasswordReset.warning.alreadySignedIn'),
                'type' => 'warning');
            return self::ALREADY_SIGNED_IN;
        }
        // no password reset request => go home
        if (!isset($_SESSION['state']['passwordresetrequest'])) {
            return self::NO_PASSWORD_RESET_REQUEST;
        }
    }

    public function awaitingPasswordReset() {
        if ($preRouting = $this->awaitingPasswordResetPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('awaiting-password-reset.tpl.html');
        $this->displayTemplate(array());
        unset($_SESSION['errors']);
        unset($_SESSION['autofill']);
    }


    /***********************
        Set new password
    */

    protected function setNewPasswordPreRoute() {
        // not authorised to reset password => go home
        if (!isset($_SESSION['state']['passwordresettoken'])) {
            return self::NO_PASSWORD_RESET_TOKEN;
        }
        
        // check if signed in
        if (!isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are no longer signed in, please follow the password reset link again',
                // 'content' => $this->translate->_('setNewPassword.warning.notSignedIn'),
                'type' => 'warning');
            return self::NOT_SIGNED_IN;
        }
    }

    public function setNewPassword() {
        if ($preRouting = $this->setNewPasswordPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('set-new-password.tpl.html');
        $this->displayTemplate(array());

        unset($_SESSION['errors']);
        unset($_SESSION['autofill']);
    }


    /******************
        User created
    */
    
    protected function userCreatedPreRoute() {
        // not a newly created user => go home
        if (!isset($_SESSION['state']['newuser'])) {
            return self::NOT_A_NEW_USER;
        }
    }

    public function userCreated() {
        if ($preRouting = $this->userCreatedPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('user-created.tpl.html');
        $this->displayTemplate(array());

        unset($_SESSION['state']['newuser']);
    }


    /*********************************
        Awaiting email confirmation
    */

    protected function awaitingEmailConfirmationPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }
        
        // not in registered (i.e. awaiting email confirmation) status => go home
        if (isset($_SESSION['user']['status']) && ($_SESSION['user']['status'] != 'registered')) {
            return self::NOT_REGISTERED;
        }
        
        // check for confirmation code
        if (is_null($user->getEmailConfirmationCode())) {
            $_SESSION['message'] = array (
                'content' => 'your email address has already been validated',
                // 'content' => $this->translate->_('resendConfirmationEmail.warning.alreadyValidated'),
                'type' => 'warning');
            return self::ALREADY_VALIDATED;
        }
    }

    public function awaitingEmailConfirmation() {
        if ($preRouting = $this->awaitingEmailConfirmationPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('awaiting-email-confirmation.tpl.html');
        $this->displayTemplate(array());

    }


    /**********************
        Email confirmed
    */

    protected function emailConfirmedPreRoute() {
        // not a newly confirmed email => go home
        if (!isset($_SESSION['state']['emailconfirmed'])) {
            return self::NO_EMAIL_TO_CONFIRM;
        }
    }

    public function emailConfirmed() {
        if ($preRouting = $this->emailConfirmedPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('email-confirmed.tpl.html');
        $this->displayTemplate(array());

        unset($_SESSION['state']['emailconfirmed']);
    }


    /********************
        Reset password
    */

    protected function resetPasswordPreRoute() {
        // parse and validate params
        parse_str($this->request[1], $request);
        if (!isset($request['email']) || !isset($request['code'])) {
            return self::INVALID_PARAMS;
        }

        // get user by email
        $user = $this->getByEmail('Exposure\Model\User', $request['email']);
        
        // no user for entered email
        if(is_null($user)) {
            $_SESSION['message'] = array (
                'content' => 'no user is registered to this email address', 
                // 'content' => $this->translate->_('resetPassword.error.nonexistentUser'),
                'type' => 'error');
            return self::NONEXISTENT_USER;
        }
        
        // validate user and check for confirmation code
        $user->validatePartial();
        
        if (is_null($user->getPasswordResetCode())) {
            $_SESSION['message'] = array (
                'content' => 'your password has already been reset',
                // 'content' => $this->translate->_('resetPassword.warning.passwordAlreadyReset'),
                'type' => 'warning');
            return self::ALREADY_RESET;
        }
        
        // check confirmation code
        if ($user->getPasswordResetCode()->getConfirmationCode() != $request['code']) {
            $_SESSION['message'] = array (
                'content' => 'incorrect reset code', 
                // 'content' => $this->translate->_('resetPassword.error.incorrectCode'),
                'type' => 'error');
            return self::INCORRECT_CODE;
        }
        
        // update $_SESSION
        SessionUtils::setUserSessionParams($user);
        unset ($_SESSION['state']['passwordresetrequest']);
        $_SESSION['state']['passwordresettoken'] = true;
        $_SESSION['message'] = array (
            'content' => 'valid reset code', 
            // 'content' => $this->translate->_('resetPassword.sucess.codeValidated'),
            'type' => 'success');
        
        return self::SUCCESS;
    }

    public function resetPassword() {
        return $this->resetPasswordPreRoute();
    }


    /*******************
        Confirm email
    */

    protected function confirmEmailPreRoute() {
        // parse and validate params
        if (!isset($this->request[1])) {
            return self::INVALID_PARAMS;
        }
        parse_str($this->request[1], $request);
        if (!isset($request['email']) || !isset($request['code'])) {
            return self::INVALID_PARAMS;
        }

        // get user by email
        $user = $this->getByEmail('Exposure\Model\User', $request['email']);
        
        // no user for entered email
        if(is_null($user)) {
            $_SESSION['message'] = array (
                'content' => 'no user is registered to this email address', 
                // 'content' => $this->translate->_('confirmEmail.error.nonexistentUser'),
                'type' => 'error');
            return self::NONEXISTENT_USER;
        }
        
        // validate user and check for confirmation code
        $user->validatePartial();
        
        if (is_null($user->getEmailConfirmationCode())) {
            $_SESSION['message'] = array (
                'content' => 'your email address has already been validated',
                // 'content' => $this->translate->_('confirmEmail.warning.alreadyValidated'),
                'type' => 'warning');
            return self::ALREADY_VALIDATED;
        }
        
        // check confirmation code
        if ($user->getEmailConfirmationCode()->getConfirmationCode() != $request['code']) {
            $_SESSION['message'] = array (
                'content' => 'confirmation code is invalid', 
                // 'content' => $this->translate->_('confirmEmail.error.incorrectCode'),
                'type' => 'error');
            return self::INCORRECT_CODE;
        }
        
        // update status
        $user->setEmailConfirmationCode(null);
        $user->setStatus(\Exposure\Model\User::STATUS_VALIDATED);

        // persist to DB
        $this->config->getDocumentManager()->flush();
        
        // update $_SESSION
        SessionUtils::setUserSessionParams($user);
        $_SESSION['state']['emailconfirmed'] = true;
        $_SESSION['message'] = array (
            'content' => 'email address validated', 
            // 'content' => $this->translate->_('confirmEmail.sucess.emailValidated'),
            'type' => 'success');
        
        return self::SUCCESS;
    }

    public function confirmEmail() {
        return $this->confirmEmailPreRoute();
    }
}


