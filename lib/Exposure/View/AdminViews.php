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

class AdminViews extends View {
    protected $project = null;
    protected $user = null;

    /************
        Common
    */

    protected function isAdminSignedInOrRedirect() {
        // check $_SESSION
        if (!isset($_SESSION['adminuser']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are not signed in, please sign in',
                // 'content' => $this->translate->_('common.warning.notSignedInRedirect'),
                'type' => 'warning');
            $_SESSION['last-operation'] = $this->request[0];
            return self::NOT_SIGNED_IN;
        }
    }

    protected function getAdmin() {
        return $this->getByLabel('Exposure\Model\Administration', $this->config->getParam('adminLabel'));
    }


    /*************
        Sign in
    */

    protected function signInPreRoute() {
        // already signed in => go home
        if (isset($_SESSION['adminuser']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are already signed in',
                // 'content' => $this->translate->_('adminSignin.warning.alreadySignedIn'),
                'type' => 'warning');
            return self::ALREADY_SIGNED_IN;
        }
    }

    public function signIn() {
        if ($preRouting = $this->signInPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('admin-sign-in.tpl.html');
        $this->displayTemplate(array(
            
    
        ));
        unset($_SESSION['errors']);
        unset($_SESSION['autofill']);
    }


    /***********
        Index
    */

    protected function indexPreRoute() {
        return $this->isAdminSignedInOrRedirect();
    }

    public function index() {
        if ($preRouting = $this->indexPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('admin.tpl.html');
        $this->displayTemplate(array(
            
            'admin' => $this->getAdmin(),
    
        ));
        
    }


    /*******************
        Notifications
    */

    protected function notificationsPreRoute() {
        return $this->isAdminSignedInOrRedirect();
    }

    public function notifications() {
        if ($preRouting = $this->notificationsPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('admin-notifications.tpl.html');
        $this->displayTemplate(array(
            
            'admin' => $this->getAdmin(),
    
        ));        
    }


    /**********************
        Project moderate
    */

    protected function projectModeratePreRoute() {
        if ($result = $this->isAdminSignedInOrRedirect()) {
            return $result;
        }

        // require project slug in URL
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // get project by slug or fail
        if (is_null($this->project = 
            $this->getByUrlSlug('Exposure\Model\Project', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }
    }

    public function projectModerate() {
        if ($preRouting = $this->projectModeratePreRoute()) {
            return $preRouting;
        }
        
        $this->loadTemplate('admin-project-moderate.tpl.html');
        $this->displayTemplate(array(
            'project' => $this->project,
            'themetree' => DisplayHelpers::getDisplayDataForTheme(
                $this->project->getTheme(), $_SESSION['language']),
            'locationtree' => DisplayHelpers::getDisplayDataForPlace(
                $this->project->getPlace(), $_SESSION['language']),
        ));
    }


    /**********************
        Profile moderate
    */

    protected function profileModeratePreRoute() {
        if ($result = $this->isAdminSignedInOrRedirect()) {
            return $result;
        }

        // require project slug in URL
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // get user by id or fail
        if (is_null($this->user = 
            $this->getById('Exposure\Model\User', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }
    }
    
    public function profileModerate() {
        if ($preRouting = $this->profileModeratePreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('admin-profile-moderate.tpl.html');
        $this->displayTemplate(array(
            'user' => $this->user,
            'locationtree' => DisplayHelpers::getDisplayDataForPlace($this->user->getPlace(), $_SESSION['language']),
    
        ));
    }
    
}


