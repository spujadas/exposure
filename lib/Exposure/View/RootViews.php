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

use Exposure\Model\Theme,
    Exposure\Model\ModerationStatus,
	Exposure\Model\User,
	Exposure\Model\Project;

class RootViews extends View {
    /***********
        Index
    */

    protected function indexPreRoute() {
        // not signed in => no pre-routing
        if (!isset($_SESSION['user']['id'])) {
            return;
        }

        // otherwise (signed in)
        if ($result = $this->checkSignedInUser()) {
            return $result;
        }

        // registered => needs validation
        if ($this->signedInUser->getStatus() == User::STATUS_REGISTERED) { 
            return self::USER_REGISTERED; 
        }

        // first times
        switch ($this->signedInUser->getFirstTime()) {
        case User::FIRST_TIME_PROFILE:
            return self::FIRST_TIME_PROFILE;
        case User::FIRST_TIME_PROJECT:
            return self::FIRST_TIME_PROJECT;
        case User::FIRST_TIME_ORGANISATION:
            return self::FIRST_TIME_ORGANISATION_SPONSOR;
        }
        
        // validated
        switch ($this->signedInUser->getType()) {
        case User::TYPE_PROJECT_OWNER: // project owner => redirect to dashboard
            return self::PROJECT_OWNER;
        case User::TYPE_SPONSOR: // sponsor => redirect to dashboard
            return self::SPONSOR;
        }
    }

    public function index() {
        if ($preRouting = $this->indexPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('index.tpl.html');
        $this->displayTemplate(array(
            
            
            'projects' => $this->getPreviouslyApprovedProjects(),
            'themes' => $this->getRootThemes(),
    
        ));
    }
}


