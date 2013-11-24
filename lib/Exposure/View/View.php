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

use Exposure\ODM\ObjectDocumentMapper,
    Exposure\Model\User;

class View extends \Sociable\View\View {
    /** @var Exposure\Model\Project */
    protected $project = null;

    /** @var Exposure\Model\User */
    protected $signedInUser = null;

    const NOT_SIGNED_IN = 'not signed in';
    const NOT_AUTHORISED = 'not authorised';
    const INVALID_PARAMS = 'invalid params';
    const INVALID_SESSION = 'invalid session';
    const MAINTENANCE = 'maintenance';
    const SUCCESS = 'success';

    // Root routes
    const USER_REGISTERED = 'user registered';
    const FIRST_TIME_PROFILE = 'first time profile';
    const FIRST_TIME_PROJECT = 'first time project';
    const PROJECT_OWNER = 'project owner';
    const SPONSOR = 'sponsor';
    const FIRST_TIME_ORGANISATION_SPONSOR = 'first time organisation sponsor';

    // Access routes
    const ALREADY_SIGNED_IN = 'already signed in'; // Admin routes
    const NOT_A_NEW_USER = 'not a new user';
    const NOT_REGISTERED = 'not registered';
    const NONEXISTENT_USER = 'nonexistent user';
    const INCORRECT_CODE = 'incorrect code';
    const NO_EMAIL_TO_CONFIRM = 'no email to confirm';
    const ALREADY_VALIDATED = 'already validated';
    const NO_PASSWORD_RESET_REQUEST = 'no password reset request';
    const NO_PASSWORD_RESET_TOKEN = 'no password reset token';
    const ALREADY_RESET = 'already reset';

    // populates $signedInUser
    protected function isSignedInOrRedirect() {
        // check $_SESSION
        if (!isset($_SESSION['user']['id'])) {
            $_SESSION['message'] = array (
                'content' => 'you are not signed in, please sign in',
                // 'content' => $this->translate->_('common.warning.notSignedInRedirect'),
                'type' => 'warning');
            $_SESSION['last-operation'] = $this->request[0];
            return self::NOT_SIGNED_IN;
        }

        return $this->checkSignedInUser();
    }

    // populates $signedInUser
    protected function checkSignedInUser() {
        if (!isset($_SESSION['user']['id'])) {
            return self::NOT_SIGNED_IN;
        }
    	if (is_null($this->signedInUser = $this->getById('Exposure\Model\User', $_SESSION['user']['id']))) {
            return self::MAINTENANCE;
    	}
    }

    /* generic function for URLs in /<some_page>/<project_slug>
       - checks if <project_slug> is set and matches a project owned by 
       the currently logged in user, or redirects
       populates $project, $user */
    protected function checkOwnedProjectSlugPassedAsArgument() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no project slug in URL => out
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // project slug in URL - get project
        if (is_null($this->project = 
        	$this->getByUrlSlug('Exposure\Model\Project', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        if (!$this->signedInUser->ownsProject($this->project)) {
        	return self::NOT_AUTHORISED;
        }
    }

    protected function displayTemplateWithSignedInUser($params = array()) {
        $params['user'] = $this->signedInUser ;
        $this->displayTemplate($params);
    }

    public function checkIfSignedInUserIsSponsor() {
        return $this->checkIfSignedInUserHasType(User::TYPE_SPONSOR);
    }

    public function checkIfSignedInUserIsProjectOwner() {
        return $this->checkIfSignedInUserHasType(User::TYPE_PROJECT_OWNER);
    }

    public function checkIfSignedInUserHasType($type) {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // get user
        if ($result = $this->checkSignedInUser()) {
            return $result;
        }
        
        // check type
        if ($this->signedInUser->getType() != $type) {
            return self::NOT_AUTHORISED;
        }
    }

    protected function getRootThemes() {
    	return ObjectDocumentMapper::getRootThemes($this->config->getDocumentManager());
    }

    protected function getBusinessSectorsInLanguage($language) {
    	return ObjectDocumentMapper::getBusinessSectorsInLanguage(
    		$this->config->getDocumentManager(),
    		$language);
    }

    protected function getCountriesInLanguage($language) {
    	return ObjectDocumentMapper::getCountriesInLanguage(
    		$this->config->getDocumentManager(),
    		$language);
    }

    protected function getSelfOrChildrenThemesMatchingLabel($label) {
    	return ObjectDocumentMapper::getSelfOrChildrenThemesMatchingLabel(
    		$this->config->getDocumentManager(),
    		$label);
    }

    protected function getSponsorReturnTypes() {
    	return ObjectDocumentMapper::getSponsorReturnTypes(
    		$this->config->getDocumentManager());
    }

    protected function getPreviouslyApprovedProjects() {
        return ObjectDocumentMapper::getPreviouslyApprovedProjects(
            $this->config->getDocumentManager());
    }

    protected function getSubscriptionTypeAndDurations() {
        return ObjectDocumentMapper::getSubscriptionTypeAndDurations(
            $this->config->getDocumentManager());
    }

    protected function getSubscriptionTypeAndDuration($label, $duration) {
        return ObjectDocumentMapper::getSubscriptionTypeAndDuration(
            $this->config->getDocumentManager(), $label, $duration);
    }
}


