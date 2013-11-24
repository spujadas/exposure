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

use Exposure\Model\ModerationStatus;

class ProfileViews extends View {
    protected $user = null;


    /************
        Common
    */

    protected function displayProfileEditTemplate() {
        $this->displayTemplate(array(
            'user' => $this->signedInUser,
            'countries' => $this->getCountriesInLanguage($_SESSION['language']),
            'country' => is_null($this->signedInUser->getCountry())?'':$this->signedInUser->getCountry()->getCode(),
            'locationtree' => FormHelpers::getFormDataForPlace($this->signedInUser->getPlace()),
            
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /**********
        Edit
    */

    protected function editPreRoute() {
        return $this->isSignedInOrRedirect();
    }

    public function edit() {
        if ($preRouting = $this->editPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('profile-edit.tpl.html');
        $this->displayProfileEditTemplate();
    }


    /****************
        First edit
    */

    protected function firstEditPreRoute() {
        return $this->isSignedInOrRedirect();
    }

    public function firstEdit() {
        if ($preRouting = $this->firstEditPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('profile-first-edit.tpl.html');
        $this->displayProfileEditTemplate();
    }

    /*********************
        Password change
    */

    protected function passwordChangePreRoute() {
        return $this->isSignedInOrRedirect();
    }

    public function passwordChange() {
        if ($preRouting = $this->passwordChangePreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('password-change.tpl.html');
        $this->displayTemplate(array(
            
        ));

        unset($_SESSION['errors']);
    }


    /***********
        Photo
    */
    
    protected function photoPreRoute() {
        // no user id in URL => out
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // get user and check for photo
        $this->user = $this->getById('Exposure\Model\User', $this->request[1]);
        if (is_null($this->user) || is_null($this->user->getPhoto())) {
            return self::INVALID_PARAMS;
        }

        // check signed in user if signed in
        if (isset($_SESSION['user']['id'])) {
            $this->checkSignedInUser();
        }

        // authorise:
        // - if the user (profile) has been approved
        // - or if the owning user is signed in
        // - or if the signed in user is an admin
        if (($this->user->getModerationStatus()->getStatus() == ModerationStatus::STATUS_APPROVED)
            || ($this->user == $this->signedInUser)
            || isset($_SESSION['adminuser']['id'])) {
            return;
        }

        return self::NOT_AUTHORISED;
    }

    public function photo() {
        if ($preRouting = $this->photoPreRoute()) {
            $this->emptyImage(); 
            return;
        }

        header('Content-type: ' . $this->user->getPhoto()->getMime());
        echo $this->user->getPhoto()->getImageFile()->getBytes();
    }


    /**********
        View
    */

    protected function viewPreRoute() {
        // if user id passed in URL, does the user exist?
        if(isset($this->request[1])) {
            if(is_null($this->user = $this->getById('Exposure\Model\User', $this->request[1]))) {
                return self::INVALID_PARAMS;
            }
            return;
        }

        // no parameter => needs user to be logged in
        return $this->isSignedInOrRedirect();
    }

    
    public function view() {
        if ($preRouting = $this->viewPreRoute()) {
            return $preRouting;
        }

        // no user => default to signed in user
        if (is_null($this->user)) {
            $this->user = $this->signedInUser;
        }

        $this->loadTemplate('profile-view.tpl.html');
        $this->displayTemplate(array(
            'user' => $this->user,
            'viewedUserIsSignedIn' => $this->user == $this->signedInUser,
            'locationtree' => DisplayHelpers::getDisplayDataForPlace($this->user->getPlace(), $_SESSION['language']),
        ));

    }


    /***********************
        Preferences edit
    */

    protected function preferencesEditPreRoute() {
        return $this->isSignedInOrRedirect();
    }        

    public function preferencesEdit() {
        if ($preRouting = $this->preferencesEditPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('preferences-edit.tpl.html');
        $this->displayTemplate(array(
            'user' => $this->signedInUser,
        ));

    }
}

