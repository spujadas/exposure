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
    Exposure\Model\Project,
    Exposure\Model\ModerationStatus,
    Exposure\Model\ProjectRights,
    Exposure\Model\User,
    Sociable\Utility\NumberValidator;

class ProjectViews extends View {
    protected $project = null;
    protected $photo = null;
    protected $theme = null;

    /**********
        Edit
    */

    protected function editPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }
        
        // project owner?
        if ($this->signedInUser->getType() != User::TYPE_PROJECT_OWNER) {
            return self::NOT_AUTHORISED;
        }

        // no project slug in URL => edit draft project
        if (count($this->request) < 2) {
            return;
        }

        // project slug in URL - get project
        if (is_null($this->project = 
            $this->getByUrlSlug('Exposure\Model\Project', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        // check if user owns the project
        if (!$this->signedInUser->ownsProject($this->project)) {
            return self::NOT_AUTHORISED;
        }
    }

    public function edit() {
        if ($preRouting = $this->editPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('project-edit.tpl.html');
        
        // no params in URL => new project
        if (count($this->request) < 2) {
            $theme = null;
            $country = $this->signedInUser->getCountry();
            $place = $this->signedInUser->getPlace();
        }
        // otherwise get current project
        else {
            $theme = $this->project->getTheme();
            $country = $this->project->getCountry();
            $place = $this->project->getPlace();
        }

        // get project rights
        if (is_a($projectRights = $this->signedInUser->getCurrentSubscription(), 'Exposure\Model\Subscription')) {
            // from subscription
            $projectRights = $this->signedInUser->getCurrentSubscription()
                ->getTypeAndDuration()->getType()->getProjectRights();
            $displayDescription = $projectRights->getDisplayDescription();
            $numberDisplayedPhotos = $projectRights->getNumberDisplayedPhotos();
            $displayWebPresence = $projectRights->getDisplayWebPresence();
            $subscribed = true;
        }
        else {
            // default
            $displayDescription = $this->config->getParam('displayDescription');
            $numberDisplayedPhotos = $this->config->getParam('numberDisplayedPhotos');
            $displayWebPresence = $this->config->getParam('displayWebPresence');
            $subscribed = false;
        }
        
        // get theme tree (overriding with autofill if present)
        if (isset($_SESSION['autofill']['theme'])) {
            $themetree = FormHelpers::getFormDataForThemeLabel(
                $this->config->getDocumentManager(), 
                $_SESSION['autofill']['theme']);
        }
        else {
            $themetree = FormHelpers::getFormDataForTheme(
                $this->config->getDocumentManager(), 
                $theme);
        }

        // get list of countries
        $countries = $this->getCountriesInLanguage($_SESSION['language']);

        // get country code
        if (isset($_SESSION['autofill']['country'])) {
            $countryCode = $_SESSION['autofill']['country'];
        }
        elseif (is_null($country)) {
            $countryCode = '';
        }
        else {
            $countryCode = $country->getCode();
        }

        // get place template data (overriding with autofill if present)
        if (isset($_SESSION['autofill']['location'])) {
            $locationtree = FormHelpers::getFormDataForLocationLabel(
                $this->config->getDocumentManager(), 
                $_SESSION['autofill']['location']);
        }
        else {
            if (isset($_SESSION['autofill']['country'])) {
                $locationtree = FormHelpers::getFormDataForCountryCode(
                    $this->config->getDocumentManager(), 
                    $_SESSION['autofill']['country']);
            }
            else {
                $locationtree = FormHelpers::getFormDataForPlace($place);
            }
        }
        
        // render
        $this->displayTemplate(array(
            'project' => $this->project,
            'user' => $this->signedInUser,
            'themetree' => $themetree,
            'countries' => $countries,
            'country' => $countryCode,
            'locationtree' => $locationtree,
            'displaydescription' => $displayDescription,
            'numberdisplayedphotos' => $numberDisplayedPhotos,
            'maxnumberdisplayedphotos' => ProjectRights::NUMBER_DISPLAYED_PHOTOS_MAX,
            'displaywebpresence' => $displayWebPresence,
            'maxnumberwebpresences' => Project::NUMBER_WEB_PRESENCES_MAX,
            'subscribed' => $subscribed,
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /***********
        Photo
    */

    public function photo() {
        switch (count($this->request)) {
        case 1:
            // no parameter: return empty image
            $this->emptyImage();
            break;
        case 2:
            $this->displayDraftPhoto();
            break;
        default:
            $this->displayProjectPhoto();
            break;
        }
    }

    // 1 parameter in URL: labelledImage.id (user's draft photos)
    protected function displayDraftPhotoPreRoute() {
        if ($result = $this->checkSignedInUser()) {
            return $result;
        }
        if (is_null($this->photo = 
            $this->signedInUser->getTempDraftProjectPhotoById($this->request[1]))) {
            return self::INVALID_PARAMS;
        }
    }

    protected function displayDraftPhoto() {
        if ($preRouting = $this->displayDraftPhotoPreRoute()) {
            $this->emptyImage();
            return;
        }

        header('Content-type: ' . $this->photo->getMime());
        echo $this->photo->getImageFile()->getBytes();
    }

    // 2 parameters in URL: project.urlSlug, ALI.id (display default photo)
    // 3 parameters in URL: project.urlSlug, ALI.id, 'current'|'previous'
    protected function displayProjectPhotoPreRoute() {
        if (is_null($this->project = 
            $this->getByUrlSlug('Exposure\Model\Project', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        // get ALI
        if (is_null($projectPhoto = 
            $this->project->getPhotoById($this->request[2]))) {
            return self::INVALID_PARAMS;
        }     

        // get requested photo
        if (isset($this->request[3])) {
            // specific photo - check if signed in user owns the project or if admin is signed in
            if (!isset($_SESSION['adminuser']['id'])) {
                if ($result = $this->checkSignedInUser()) {
                    return $result;
                }
                if (!$this->signedInUser->ownsProject($this->project)) {
                    return self::NOT_AUTHORISED;
                }
            }
            $this->photo = ($this->request[3]=='current')?$projectPhoto->getCurrent():$projectPhoto->getPrevious();
        }
        else {
            // default photo
            $this->photo = $projectPhoto->getLatestApproved();
        }
        
        if (is_null($this->photo)) { 
            return self::INVALID_PARAMS; 
        }
    }

    protected function displayProjectPhoto() {
        if ($preRouting = $this->displayProjectPhotoPreRoute()) {
            $this->emptyImage();
            return;
        }

        header('Content-type: ' . $this->photo->getMime());
        echo $this->photo->getImageFile()->getBytes();
    }


    /*******************
        Projects view
    */

    protected function projectsViewPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // project owner?
        if ($this->signedInUser->getType() != User::TYPE_PROJECT_OWNER) {
            return self::NOT_AUTHORISED;
        }
    }

    public function projectsView() {
        if ($preRouting = $this->projectsViewPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('projects-view.tpl.html');
        $this->displayTemplateWithSignedInUser();
    }


    /******************
        Project view
    */

    protected function viewPreRoute() {
        // no project slug in URL or doesn't match a project?
        if ((count($this->request) < 2) 
            || is_null($this->project = 
                $this->getByUrlSlug('Exposure\Model\Project', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        // pass through if not signed in
        if (!isset($_SESSION['user']['id'])) {
            return;
        }

        // check if valid user if signed in
        if ($result = $this->checkSignedInUser()) {
            return $result;
        }
    }

    public function view() {
        if ($preRouting = $this->viewPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('project-view.tpl.html');

        // params depending on whether user is anonymous...
        if (isset($_SESSION['user']['id'])) {
            $this->checkSignedInUser();
            $user = $this->signedInUser;
            $userIsOwner = $user->ownsProject($this->project);
            $userIsSponsor = ($user->getType() == User::TYPE_SPONSOR);
        }
        else {
        // ... or signed in
            $user = null;
            $userIsOwner = false;
            $userIsSponsor = false;
        }

        // get project rights for first owner (only one user per project in this version)
        $firstOwner = $this->project->getOwners()[0];
        
        if (is_null($firstOwner->getCurrentSubscription())) {
            // default if no subscription
            $displayDescription = $this->config->getParam('displayDescription');
            $numberDisplayedPhotos = $this->config->getParam('numberDisplayedPhotos');
            $displayWebPresence = $this->config->getParam('displayWebPresence');
            $subscribed = false;
        }
        else {
            // from subscription
            $projectRights = $firstOwner->getCurrentSubscription()
                ->getTypeAndDuration()->getType()->getProjectRights();
            $displayDescription = $projectRights->getDisplayDescription();
            $numberDisplayedPhotos = $projectRights->getNumberDisplayedPhotos();
            $displayWebPresence = $projectRights->getDisplayWebPresence();
            $subscribed = true;
        }

        // never been published or rejected project - show as unpublished and done
        if (!$userIsOwner 
            && in_array($this->project->getModerationStatus()->getStatus(),
                array(ModerationStatus::STATUS_FIRST_USER_EDIT, 
                ModerationStatus::STATUS_REJECTED))) {
            $this->loadTemplate('project-view-unpublished.tpl.html');
            $this->displayTemplate(array(
                
            ));
            return;
        }

        // increase page views if viewing user is not the owner
        if (!$userIsOwner) {
            $this->project->increasePageViews();
            $this->config->getDocumentManager()->flush();
        }

        // currently or previously published project
        $this->loadTemplate('project-view.tpl.html');
        $this->displayTemplate(array(
            'project' => $this->project,
            'user' => $user,
            'userIsOwner' => $userIsOwner,
            'userIsSponsor' => $userIsSponsor,
            'themetree' => DisplayHelpers::getDisplayDataForTheme($this->project->getTheme(), $_SESSION['language']),
            'locationtree' => DisplayHelpers::getDisplayDataForPlace($this->project->getPlace(), $_SESSION['language']),
            'displaydescription' => $displayDescription,
            'numberdisplayedphotos' => $numberDisplayedPhotos,
            'maxnumberdisplayedphotos' => ProjectRights::NUMBER_DISPLAYED_PHOTOS_MAX,
            'displaywebpresence' => $displayWebPresence,
            'maxnumberwebpresences' => Project::NUMBER_WEB_PRESENCES_MAX,
            'subscribed' => $subscribed,
        ));
    }


    /****************
        List items
    */

    public function listItems() {
        /* optional params:
           - offset (default 0)
           - limit (default: 48, max: 100)
           - theme
           e.g. /project-list-items/theme/web/offset/16/limit/16
        */

        $qb = $this->config->getDocumentManager()
            ->createQueryBuilder('Exposure\Model\Project')
            ->field('moderationStatus.status')->in(array(ModerationStatus::STATUS_APPROVED, ModerationStatus::STATUS_USER_EDIT));
        $limit = 48;

        // ignore last request item if not paired
        if (($maxCount = count($this->request)-1) % 2) { $maxCount--;}
        for ($i = 1; $i<$maxCount; $i++) {
            switch($this->request[$i++]) {
            case 'offset':
                $arg = is_numeric($this->request[$i])?(int) $this->request[$i]:$this->request[$i];
                try {
                    NumberValidator::validate($arg, array('positive' => true, 'int' => true));
                }
                catch (\Exception $e) {
                    break;
                }
                $qb = $qb->skip($arg);
                break;
            case 'limit':
                $arg = is_numeric($this->request[$i])?(int) $this->request[$i]:$this->request[$i];
                try {
                    NumberValidator::validate($arg, array('positive' => true, 'int' => true, 'max' => 100));
                }
                catch (\Exception $e) {
                    break;
                }
                $limit = $arg;
                break;
            case 'theme':
                $themes = $this->config->getDocumentManager()
                    ->createQueryBuilder('Exposure\Model\Theme')
                    ->field('path')->equals(new \MongoRegex('/^\\|' . $this->request[$i] . '\\|/'))
                    ->hydrate(false)
                    ->select('_id')
                    ->getQuery()->execute();
                $themeIds = array();
                foreach ($themes as $theme) {
                    $themeIds[] = $theme['_id'];
                }
                $qb = $qb->field('theme.id')->in($themeIds);
                break;
            }
        }
        $qb = $qb->limit($limit);
        $projects = $qb->getQuery()->execute();

        $this->loadTemplate('project-list-items.inc.tpl.html');
        $this->displayTemplate(array(
            'projects' => $projects,
    
        ));
    }


    /******************
        Project list
    */

    protected function projectListPreRoute() {
        // theme label in URL and label in URL doesn't match a theme => out
        if ((count($this->request) > 1)
            && is_null($this->getByLabel('Exposure\Model\Theme', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }
    }

    public function projectList() {
        if ($preRouting = $this->projectListPreRoute()) {
            return $preRouting;
        }
        $qb = $this->config->getDocumentManager()
            ->createQueryBuilder('Exposure\Model\Project')
            ->field('moderationStatus.status')->in(array(ModerationStatus::STATUS_APPROVED, 
                ModerationStatus::STATUS_USER_EDIT));

        // label passed in URL?
        if (count($this->request) > 1) {
            $matchingThemes = $this->getSelfOrChildrenThemesMatchingLabel($this->request[1]);

            $themeIds = array();
            foreach ($matchingThemes as $theme) {
                $themeIds[] = $theme->getId();
            }
                
            $qb = $qb->field('theme.id')->in($themeIds);
        }
        $projects = $qb->getQuery()->execute();

        $this->loadTemplate('project-list.tpl.html');
        $this->displayTemplate(array(
            'projects' => $projects,
            'themes' => $this->getRootThemes(),
        ));
    }


    /************************
        Sponsored projects
    */

    protected function sponsoredProjectsPreRoute() {
        return $this->checkIfSignedInUserIsSponsor();
    }

    public function sponsoredProjects() {
        if ($preRouting = $this->sponsoredProjectsPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('sponsored-projects.tpl.html');
        $this->displayTemplateWithSignedInUser();
    }


    /*********************
        Wanted projects
    */

    protected function wantedProjectsPreRoute() {
        return $this->checkIfSignedInUserIsSponsor();
    }

    public function wantedProjects() {
        if ($preRouting = $this->wantedProjectsPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('wanted-projects.tpl.html');
        $this->displayTemplateWithSignedInUser();
    }


    /*************************
        Bookmarked projects
    */

    protected function bookmarkedProjectsPreRoute() {
        return $this->checkIfSignedInUserIsSponsor();
    }

    public function bookmarkedProjects() {
        if ($preRouting = $this->bookmarkedProjectsPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('bookmarked-projects.tpl.html');
        $this->displayTemplateWithSignedInUser();
    }
}