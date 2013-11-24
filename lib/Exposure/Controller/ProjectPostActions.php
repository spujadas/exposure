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

use Exposure\Model\Project,
    Exposure\Model\User,
	Exposure\Model\Theme,
    Exposure\Model\ModerationStatus,
    Exposure\Model\ApprovableContent,
    Sociable\Utility\StringValidator,
    Sociable\Utility\StringException,
    Sociable\Utility\NumberException,
    Sociable\Utility\NumberValidator,
    Exposure\Model\ProjectException,
    Exposure\Model\ProjectRights,
    Sociable\Model\URLException,
    Sociable\Model\WebPresence,
    Sociable\Model\URL,
    Exposure\Model\ProjectModerationNotification,
    Exposure\Model\ProjectWant,
    Exposure\Model\ProjectWantNotification,
    Exposure\Model\SponsorOrganisation,
    Sociable\Utility\SEO;

use Doctrine\Common\Collections\ArrayCollection;

class ProjectPostActions extends \Exposure\Controller\PostActions {
    protected $errors;
    protected $autofill;

    protected $user = null;
    protected $project = null;
    protected $organisation = null;

    protected function saveIsValidPost() {
        if (!$this->postHasIndices(array('name', 'theme', 'summary', 'audience_description',
        	'audience_range_min', 'audience_range_max', 'country', 
            'sponsoring_deadline', 'event_date',
            'web_presence_urls', 'web_presence_descriptions'))) {
            return false;
        }
        if ((count($_POST['web_presence_urls']) != count($_POST['web_presence_descriptions'])) 
            || (count($_POST['web_presence_urls']) > Project::NUMBER_WEB_PRESENCES_MAX)) {
            return false;
        }
        return true;
    }

    protected function updateProjectPlace(Project $project, $locationLabel, $countryCode) {
        if (!empty($locationLabel)) {
            $this->updatePlaceAsLocation($project, 'setPlace', $locationLabel,
                array(
                    'error_field' => 'location',
                    'error_message' => 'location is invalid', // $this->translate->_('projectSave.error.invalidLocation'),
                )
            );
            $this->autofill['location'] = $locationLabel;
            return;
        }

        if (!empty($countryCode)) {
            $this->updatePlaceAsCountry($project, 'setPlace', $countryCode,
                array(
                    'error_field' => 'country',
                    'error_message' => 'country is invalid', // $this->translate->_('projectSave.error.invalidCountry'),
                )
            );
            $this->autofill['country'] = $countryCode;
            return;
        }

        $project->setPlace(null);
    }

    protected function updateWebPresences(Project $project, $webPresenceUrls, 
        $webPresenceDescriptions, $languageCode) {
        $this->autofill['web_presences'] = array();
        $this->errors['web_presences'] = array();

        $urlStringExceptionArray = array (
            'error_field' => 'webpresence_url_temp',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'URL is missing',  // $this->translate->_('projectSave.error.urlEmpty'),
                StringValidator::EXCEPTION_TOO_LONG => 'this URL is too long',  // $this->translate->_('projectSave.error.urlTooLong'),
            ),
            'default_error_message' => 'URL is invalid', // $this->translate->_('projectSave.error.invalidUrl'),
        );
        $urlExceptionArray = array (
            'error_field' => 'webpresence_url_temp',
            'error_messages' => array (
                URL::EXCEPTION_INVALID_URL => 'this URL is invalid',  // $this->translate->_('projectSave.error.invalidUrl'),
            ),
        );
        $descriptionStringExceptionArray = array (
            'error_field' => 'webpresence_description_temp',
            'error_messages' => array (
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long',  // $this->translate->_('projectSave.error.urlDescriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('projectSave.error.invalidUrlDescription'),
        );

        // ignore last web presence if URL and description is empty
        $count = (empty($webPresenceUrls[count($webPresenceUrls)-1])
            && empty($webPresenceDescriptions[count($webPresenceUrls)-1]))
            ?count($webPresenceUrls)-1
            :count($webPresenceUrls);

        $project->resetWebPresences();

        // fill in web presences array with each web presence from form
        for ($i=0;$i<$count;$i++) {
            $this->autofill['web_presences'][$i]['url'] = $webPresenceUrls[$i];
            $this->autofill['web_presences'][$i]['description'] = $webPresenceDescriptions[$i];

            $webPresence = new WebPresence;

            $this->updateUrl($webPresence, 'setUrlInferType', $webPresenceUrls[$i], 
                $urlStringExceptionArray, $urlExceptionArray, $webPresenceDescriptions[$i],
                $languageCode, $descriptionStringExceptionArray);
            if (!empty($this->errors['webpresence_url_temp'])) {
                $this->errors['web_presences'][$i]['url'] 
                    = $this->errors['webpresence_url_temp'];
                unset($this->errors['webpresence_url_temp']);
            }
            if (!empty($this->errors['webpresence_description_temp'])) {
                $this->errors['web_presences'][$i]['description'] 
                    = $this->errors['webpresence_description_temp'];
                unset($this->errors['webpresence_description_temp']);
            }
            $project->addWebPresence($webPresence);
        }

        // remove from error array if no errors
        if (count($this->errors['web_presences']) == 0) {
            unset($this->errors['web_presences']);
        }
    }

    protected function updateProjectPhotos(Project $project, $photoIds, $photoDescriptions,
        $languageCode, User $user) {
        $this->autofill['photos'] = array();
        $this->errors['photos'] = array();
        
        $descriptionStringExceptionArray = array (
            'error_field' => 'photo_description_temp',
            'error_messages' => array (
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long',  // $this->translate->_('projectSave.error.photoDescriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('projectSave.error.invalidPhotoDescription'),
        );

        // ignore last photo if id is empty
        $count = empty($photoIds[count($photoIds)-1])
            ?count($photoIds)-1
            :count($photoIds);

        if ($count == 0) {
            $this->errors['photos']['general'] = 'photo is missing'; // $this->translate->_('projectSave.error.missingPhoto'),
        }

        // update descriptions from form
        for ($i=0;$i<$count;$i++) {
            $this->autofill['photos'][$i]['id'] = $photoIds[$i];
            $this->autofill['photos'][$i]['description'] = $photoDescriptions[$i];

            // update ALI or LI depending on if project already exists
            if (is_null($project->getId())) {
                $this->updateLabelledImageDescriptionInArrayCollection(
                    $user, $photoIds[$i], 'getTempDraftProjectPhotos',
                    $photoDescriptions[$i], $languageCode, $descriptionStringExceptionArray);
            }
            else {
                $this->updateApprovableLabelledImageDescriptionInArrayCollection(
                    $project, $photoIds[$i], 'getPhotos',
                    $photoDescriptions[$i], $languageCode, $descriptionStringExceptionArray);
            }
        }

        if (!empty($this->errors['photo_description_temp'])) {
            $this->errors['photos'][$i]['description'] 
                = $this->errors['photo_description_temp'];
            unset($this->errors['photo_description_temp']);
        }

        // stop here if errors
        if (count($this->errors['photos'])) { return; }

        // remove 'photos' from error array 
        unset($this->errors['photos']);

        // if new project then move photos from user temp drafts to project
        if (is_null($project->getId())) {
            foreach ($user->getTempDraftProjectPhotos() as $photo) {
                $approvableLabelledImage = $this->initNewApprovableLabelledImage();
                $approvableLabelledImage->setCurrent($photo);
                $project->addPhoto($approvableLabelledImage);
                $this->config->getDocumentManager()->persist($approvableLabelledImage);
                $user->getTempDraftProjectPhotos()->removeElement($photo);
            }
        }
    }

    protected function updateSponsoringDeadline(Project $project, $sponsoringDeadline) {
        $this->autofill['sponsoring_deadline'] = $sponsoringDeadline;
        if (empty($sponsoringDeadline)) {
            $project->setSponsoringDeadline(null);
            return;
        }

        $dt = \Sociable\Utility\DateTime::validateDate($sponsoringDeadline, 'd/m/Y');
        if (is_null($dt)) {
            $this->errors['sponsoring_deadline'] = 'this sponsoring deadline is invalid';
                // $this->translate->_('projectSave.error.invalidSponsoringDeadline');
        }
        else {
            $project->setSponsoringDeadline($dt);
        }
    }

    protected function updateEventDate(Project $project, $eventDate) {
        $this->autofill['event_date'] = $eventDate;
        if (empty($eventDate)) {
            $project->setEventDateTime(null);
            return;
        }
        
        $dt = \Sociable\Utility\DateTime::validateDate($eventDate, 'd/m/Y');
        if (is_null($dt)) {
            $this->errors['event_date'] = 'this event date is invalid';
                // $this->translate->_('projectSave.error.invalidEventDate');
        }
        else {
            $project->setEventDateTime($dt);
        }
    }

    protected function updateProject(Project $project, User $user) {
        $this->autofill = array();
    	$this->errors = array();

        // update project attributes
        $this->updateName($project, $_POST['name']);
        $this->updateTheme($project, $_POST['theme']);
        $this->updateSummary($project, $_POST['summary'], 
            $_SESSION['language']);
        $this->updateAudienceDescription($project, 
            $_POST['audience_description'], $_SESSION['language']);
        $this->updateAudienceRange($project, 
            $_POST['audience_range_min'], $_POST['audience_range_max']);
        $this->updateDescription($project, 
            $_POST['description'], $_SESSION['language']);
        $this->updateSponsoringDeadline($project, 
            $_POST['sponsoring_deadline']);
        $this->updateEventDate($project, 
            $_POST['event_date']);
        $this->updateProjectPlace($project,
            array_key_exists('location', $_POST)?$_POST['location']:null,
            $_POST['country']);
        $this->updateProjectPhotos($project, $_POST['photo_ids'], $_POST['photo_descriptions'],
            $_SESSION['language'], $user);
        $this->updateWebPresences($project, $_POST['web_presence_urls'], 
            $_POST['web_presence_descriptions'], $_SESSION['language']);
    }

    protected function isSlugUsable($currentSlug, $newSlug) {
        // no slug change => remains available
        if ($currentSlug == $newSlug) {
            return true;
        }

        // otherwise get project by new slug
        $project = $this->getByUrlSlug('Exposure\Model\Project', $newSlug);

        // slug unused => authorised
        if (is_null($project)) {
            return true;
        }

        // otherwise not authorised
        return false;
        
    }

    protected function updateName(Project $project, $name) {
        $currentSlug = $project->getUrlSlug();

        $stringExceptionArray = array (
            'error_field' => 'name',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'name missing', // $this->translate->_('projectSave.error.emptyName'),
                StringValidator::EXCEPTION_TOO_LONG => 'this name is too long',  // $this->translate->_('projectSave.error.nameTooLong'),
            ),
            'default_error_message' => 'this name is invalid', // $this->translate->_('projectSave.error.invalidName'),
        );
        $this->updateString($project, 'setName', $name, $stringExceptionArray);

        if (!$this->isSlugUsable($currentSlug, $project->getUrlSlug())) {
            $this->errors['name'] = 'this name already in use'; // $this->translate->_('projectSave.error.nameUnavailable'),
        }

        $this->autofill['name'] = $name;
    }

    protected function updateTheme(Project $project, $themeLabel)  {
        $theme = $this->getByLabel('Exposure\Model\Theme', $themeLabel);
        if (is_null($theme)) {
            $this->errors['theme'] = 'theme is invalid';
                // $this->translate->_('projectSave.error.invalidTheme');
        }
        else {
            $project->setTheme($theme);
            $this->autofill['theme'] = $themeLabel;
        }
    }

    protected function updateSummary(Project $project, $summary, $languageCode) {
        $stringExceptionArray = array (
            'error_field' => 'summary',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'summary missing', // $this->translate->_('projectSave.error.emptySummary'),
                StringValidator::EXCEPTION_TOO_LONG => 'this summary is too long', // $this->translate->_('projectSave.error.summaryTooLong'),
            ),
            'default_error_message' => 'this summary is invalid', // $this->translate->_('projectSave.error.invalidSummary');
        );
        if ($this->updateApprovableMultiLanguageString($project, 'getSummary', 'setSummary',
            $summary, $languageCode, $stringExceptionArray)) {
            $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
        }
        $this->autofill['summary'] = $summary;
    }

    protected function updateAudienceDescription(Project $project, $audienceDescription, $languageCode) {
        $stringExceptionArray = array (
            'error_field' => 'audience_description',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'description is missing', // $this->translate->_('projectSave.error.emptyAudienceDescription'),
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long', // $this->translate->_('projectSave.error.audienceDescriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('projectSave.error.invalidAudienceDescription');
        );
        if ($this->updateApprovableMultiLanguageString($project, 'getAudienceDescription', 'setAudienceDescription',
            $audienceDescription, $languageCode, $stringExceptionArray)) {
            $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
        }
        $this->autofill['audience_description'] = $audienceDescription;
    }

    protected function updateAudienceRange(Project $project, $minString, $maxString) {
        $min = is_numeric($minString)?(int) $minString:$minString;
        $max = is_numeric($maxString)?(int) $maxString:$maxString;
        try {
            $project->setAudienceRange($min, $max);
        }
        catch (NumberException $e) {
            switch ($e->getMessage()) {
            case NumberValidator::EXCEPTION_NOT_POSITIVE:
                $this->errors['audience_range'] = 'the audience range values must be positive';
                    // $this->translate->_('projectSave.error.audienceRangeNotPositive');
                break;
            case NumberValidator::EXCEPTION_NOT_AN_INTEGER:
                $this->errors['audience_range'] = 'the audience range values must be integers';
                    // $this->translate->_('projectSave.error.audienceRangeNotInteger');
                break;
            case NumberValidator::EXCEPTION_TOO_LARGE:
                $this->errors['audience_range'] = 'the audience range values must be less than or equal to ' . Project::AUDIENCE_RANGE_MAX;
                    // $this->translate->_('projectSave.error.audienceRangeTooLarge');
                break;
            default:
                $this->errors['audience_range'] = 'the audience range is invalid';
                    // $this->translate->_('projectSave.error.invalidAudienceRangeValue');
                break;
            }
        }
        catch (ProjectException $e) {
            switch ($e->getMessage()) {
                case Project::EXCEPTION_INVALID_AUDIENCE_RANGE:
                    $this->errors['audience_range'] = 'the audience range is invalid';
                        // $this->translate->_('projectSave.error.invalidAudienceRange');
                    break;
            }
        }
        $this->autofill['audience_range_min'] = $min;
        $this->autofill['audience_range_max'] = $max;
    }

    protected function updateDescription(Project $project, $description, $languageCode) {
        $stringExceptionArray = array (
            'error_field' => 'description',
            'error_messages' => array (
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long', // $this->translate->_('projectSave.error.descriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('projectSave.error.invalidDescription');
        );
        if ($this->updateApprovableMultiLanguageString($project, 'getDescription', 'setDescription',
            $description, $languageCode, $stringExceptionArray)) {
            $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
        }
        $this->autofill['description'] = $description;
    }

    public function save() {
    	if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->saveIsValidPost()) { return self::INVALID_POST; }

        // get current user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);

        // either get current project...
        if (array_key_exists('project_id', $_POST)) {
            $project = $this->getById('Exposure\Model\Project', $_POST['project_id']);
            if (is_null($project)) { 
                return self::INVALID_POST; 
            }

            if (!$user->ownsProject($project)) { 
                return self::NOT_AUTHORISED; 
            }
            if (!$project->isEditable($project)) {
                $this->setMessageForNonEditableProject();
                return self::PROJECT_NOT_EDITABLE;
            }

            switch ($project->getModerationStatus()->getStatus()) {
                case ModerationStatus::STATUS_SUBMITTED_FIRST_TIME:
                case ModerationStatus::STATUS_FIRST_USER_EDIT:
                    $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_FIRST_USER_EDIT);
                    break;
                default:
                    $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
                    break;
            }
        }
        // ... or create new project
        else {
            // check if user is a project owner
            if ($user->getType() != User::TYPE_PROJECT_OWNER) {
                return self::NOT_AUTHORISED; 
            }

            // check if max number of projects not reached
            if ($user->getOwnedProjects()->count() >= User::OWNED_PROJECTS_MAX_NUMBER) {
                $_SESSION['message'] = array (
                    'content' => 'the maximum number of projects ('. User::OWNED_PROJECTS_MAX_NUMBER .') has been reached',
                    // 'content' => $this->translate->_('projectSave.error.maxNumberOwnedProjectsReached'),
                    'type' => 'error');
                return self::NOT_AUTHORISED; 
            }

            $project = new Project;
            $moderationStatus = new ModerationStatus;
            $moderationStatus->setStatus(ModerationStatus::STATUS_FIRST_USER_EDIT);
            $project->setModerationStatus($moderationStatus);
            $project->setCreationDateTime(new \DateTime);
            $this->config->getDocumentManager()->persist($project);
            $user->addOwnedProject($project);
            if ($user->getFirstTime() == User::FIRST_TIME_PROJECT) {
                $user->setFirstTime(null);
            }
        }

        // update from form data
        $this->updateProject($project, $user);

        $_SESSION['request'] = $project->getUrlSlug();

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('projectSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        switch ($project->getModerationStatus()->getStatus()) {
            case ModerationStatus::STATUS_SUBMITTED_FIRST_TIME:
                $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_FIRST_USER_EDIT);
                break;
            case ModerationStatus::STATUS_SUBMITTED:
                $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
                break;
        }
        
        $this->config->getDocumentManager()->flush();
        $_SESSION['message'] = array (
                'content' => 'project saved',
                // 'content' => $this->translate->_('projectSave.success.projectSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }

    protected function projectPublishIsValidPost() {
        return $this->postHasIndices(array('project_id'));
    }

    protected function setMessageForNonEditableProject(Project $project) {
        switch ($project->getModerationStatus()->getStatus()) {
        case ModerationStatus::STATUS_FLAGGED:
            $_SESSION['message'] = array (
                'content' => 'this project has been flagged as inappropriate – it is being reviewed',
                // 'content' => $this->translate->_('projectPublish.error.flaggedProject'),
                'type' => 'warning');
            break;
        case ModerationStatus::STATUS_REJECTED:
            $_SESSION['message'] = array (
                'content' => 'this project has been rejected',
                // 'content' => $this->translate->_('projectPublish.error.rejectedProject'),
                'type' => 'error');
            break;
        }
    }

    protected function setMessageForUnpublishableProject(Project $project) {
        if (!$project->isEditable()) {
            $this->setMessageForNonEditableProject($project);
            return;
        }
        switch ($project->getModerationStatus()->getStatus()) {
        case ModerationStatus::STATUS_SUBMITTED_FIRST_TIME:
        case ModerationStatus::STATUS_SUBMITTED:
            $_SESSION['message'] = array (
                'content' => 'this project has already been submitted',
                // 'content' => $this->translate->_('projectPublish.error.projectAlreadySubmitted'),
                'type' => 'warning');
            break;
        case ModerationStatus::STATUS_APPROVED:
            $_SESSION['message'] = array (
                'content' => 'this project has already been approved',
                // 'content' => $this->translate->_('projectPublish.error.projectAlreadyApproved'),
                'type' => 'warning');
            break;
        }
    }

    protected function changedApprovableContentMarkAsSubmitted(ApprovableContent $approvableContent) {
        if ($approvableContent->getModerationStatus()->getStatus() == ModerationStatus::STATUS_USER_EDIT) {
            $approvableContent->getModerationStatus()->setStatus(ModerationStatus::STATUS_SUBMITTED);
        }
    }

    public function projectPublish() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->projectPublishIsValidPost()) { return self::INVALID_POST; }

        // get current user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);

        // get project to publish
        $project = $this->getById('Exposure\Model\Project', $_POST['project_id']);
        if (is_null($project)) { return self::INVALID_POST; }

        if (!$user->ownsProject($project)) { 
            return self::NOT_AUTHORISED; 
        }

        if (!$project->isPublishable()) {
            $this->setMessageForUnpublishableProject($project);
            return self::PROJECT_NOT_PUBLISHABLE;
        }

        // mark changed approvable content as submitted
        $this->changedApprovableContentMarkAsSubmitted($project->getSummary());
        $this->changedApprovableContentMarkAsSubmitted($project->getDescription());
        $this->changedApprovableContentMarkAsSubmitted($project->getAudienceDescription());
        foreach ($project->getPhotos() as $photo) {
            $this->changedApprovableContentMarkAsSubmitted($photo);
        }

        // update status
        switch ($project->getModerationStatus()->getStatus()) {
            case ModerationStatus::STATUS_FIRST_USER_EDIT:
                $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_SUBMITTED_FIRST_TIME);
                break;
            case ModerationStatus::STATUS_USER_EDIT:
                $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_SUBMITTED);
                break;
        }

        // now notify the admin
        $admin = $this->getByLabel('Exposure\Model\Administration', $this->config->getParam('adminLabel'));
        $notification = new ProjectModerationNotification;
        $notification->setStatus(ProjectModerationNotification::STATUS_UNREAD);
        $notification->setContent($project->getName() . ' [NEW]');
        $notification->setEvent(ProjectModerationNotification::EVENT_SUBMITTED_PROJECT);
        $notification->setDateTime(new \DateTime);
        $notification->setProject($project);
        $this->config->getDocumentManager()->persist($notification);
        $admin->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        $_SESSION['message'] = array (
            'content' => 'your project has been submitted',
            // 'content' => $this->translate->_('projectPublish.error.submittedProject'),
            'type' => 'success');

        $_SESSION['request'] = $project->getUrlSlug();

        return self::SUCCESS;
    }

    public function projectPhotoUpsert() {
        $this->errors = array();
        
        header('Content-Type: application/json');

        // signed in?
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(null);
            return;
        }

        // get current user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);

        $fileErrorArray = array (
            'error_field' => 'photo',
            'error_messages' => array (
                PostActions::ERROR_FILE_TOO_LARGE => 'file is too large', // $this->translate->_('projectPhotoUpsert.error.photoFileTooLarge');
                PostActions::ERROR_UPLOAD_ERROR => 'upload error', // $this->translate->_('projectPhotoUpsert.error.uploadError');
                PostActions::ERROR_INVALID_FILE_TYPE => 'file type is invalid', // $this->translate->_('projectPhotoUpsert.error.photoInvalidFileType');
            ),
        );

        $descriptionStringExceptionArray = array (
            'error_field' => 'photo',
            'default_error_message' => 'cannot save this photo', // $this->translate->_('projectPhotoUpsert.error.cannotSavePhoto');
        );

        $photo = null;
        $upserted = false;

        // either project id passed => update current project...
        if (array_key_exists('project_id', $_POST)) {
            $project = $this->getById('Exposure\Model\Project', $_POST['project_id']);
            if (is_null($project)) { 
                echo json_encode(array(
                    'upserted' => false,
                    'error' => 'invalid request', // $this->translate->_('projectPhotoUpsert.error.invalidPost')
                    ));
                return;
            }
            if (!$user->ownsProject($project) || !$project->isEditable()) { 
                echo json_encode(array(
                    'upserted' => false,
                    'error' => 'unauthorised request', // $this->translate->_('projectPhotoUpsert.error.notAuthorised')
                    ));
                return;
            }

            $adderErrorArray = array(
                'error_field' => 'photo',
                'error_messages' => array (
                    PostActions::ERROR_TOO_MANY_APPROVABLE_LABELLED_IMAGES => 'too many photos', // $this->translate->_('projectPhotoUpsert.error.tooManyPhotos');
                ),
            );
            
            // insert photo with empty description if no id passed...
            if (empty($_POST['photo_id'])) {
                $photo = $this->insertApprovableLabelledImageInArrayCollection($project,
                    'getPhotos', 'addPhoto', ProjectRights::NUMBER_DISPLAYED_PHOTOS_MAX, 
                    $adderErrorArray,
                    $_FILES['photo_files']['error'][0], $_FILES['photo_files']['tmp_name'][0], $_FILES['photo_files']['size'][0], 
                    Project::PHOTO_MAX_SIZE, $fileErrorArray, 
                    '', $user->getLanguageCode(), $descriptionStringExceptionArray);
                $upserted = !is_null($photo);
            }
            // ... otherwise update file only if id passed
            else {
                $upserted = $this->updateApprovableLabelledImageFileInArrayCollection($project, 
                    $_POST['photo_id'], 'getPhotos', 
                    $_FILES['photo_files']['error'][0], $_FILES['photo_files']['tmp_name'][0], $_FILES['photo_files']['size'][0], 
                    Project::PHOTO_MAX_SIZE, $fileErrorArray);
            }

            if ($upserted) {
                switch ($project->getModerationStatus()->getStatus()) {
                    case ModerationStatus::STATUS_SUBMITTED_FIRST_TIME:
                    case ModerationStatus::STATUS_FIRST_USER_EDIT:
                        $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_FIRST_USER_EDIT);
                        break;
                    default:
                        $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
                        break;
                }
            }
        }

        // ... otherwise update to user's temporary draft photos
        else {
            $adderErrorArray = array(
                'error_field' => 'photo',
                'error_messages' => array (
                    PostActions::ERROR_TOO_MANY_LABELLED_IMAGES => 'too many photos', // $this->translate->_('projectPhotoUpsert.error.tooManyPhotos');
                ),
            );
            
            // insert photo with empty description if no id passed...
            if (empty($_POST['photo_id'])) {
                $photo = $this->insertLabelledImageInArrayCollection($user,
                    'getTempDraftProjectPhotos', 'addTempDraftProjectPhoto', 
                    ProjectRights::NUMBER_DISPLAYED_PHOTOS_MAX, $adderErrorArray, 
                    $_FILES['photo_files']['error'][0], $_FILES['photo_files']['tmp_name'][0], $_FILES['photo_files']['size'][0], 
                    Project::PHOTO_MAX_SIZE, $fileErrorArray, 
                    '', $user->getLanguageCode(), $descriptionStringExceptionArray);
                $upserted = !is_null($photo);
            }
            // ... otherwise update file only if id passed
            else {
                $upserted = $this->updateLabelledImageFileInArrayCollection($user, 
                    $_POST['photo_id'], 'getTempDraftProjectPhotos',
                    $_FILES['photo_files']['error'][0], $_FILES['photo_files']['tmp_name'][0], $_FILES['photo_files']['size'][0], 
                    Project::PHOTO_MAX_SIZE, $fileErrorArray);
            }
        }

        // return error message if upsert failed
        if (!$upserted) {
            echo json_encode(array(
                'upserted' => false,
                'error' => $this->errors));
            return;
        }

        $this->config->getDocumentManager()->flush();

        // returns (Approvable)LabelledImage id if upsert succeeded
        echo json_encode(array(
            'upserted' => true,
            'id' => is_null($photo)?$_POST['photo_id']:$photo->getId()));
    }

    public function projectPhotoDelete() {
        header('Content-Type: application/json');
        // signed in and photo id passed?
        if (!isset($_SESSION['user']['id']) || !array_key_exists('photo_id', $_POST)) {
            echo json_encode(null);
            return;
        }

        // get current user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);

        // either project id passed => remove photo from project...
        if (array_key_exists('project_id', $_POST)) {
            $project = $this->getById('Exposure\Model\Project', $_POST['project_id']);
            if (is_null($project)) { 
                echo json_encode(null);
                return;
            }
            if (!$user->ownsProject($project) || !$project->isEditable()) { 
                echo json_encode(array('deleted' => false));
                return;
            }

            // delete photo
            $deleted = $this->removeElementByIdFromArrayCollection(
                $project, $_POST['photo_id'], 'getPhotos');

            // if no photos left then project can no longer be displayed
            if ($deleted && ($project->getPhotos()->count() == 0)) {
                $project->getModerationStatus()->setStatus(ModerationStatus::STATUS_FIRST_USER_EDIT);
            }
        }

        // otherwise remove from user's temporary draft photos
        else {
            $deleted = $this->removeElementByIdFromArrayCollection(
                $user, $_POST['photo_id'], 'getTempDraftProjectPhotos');
        }

        // flush if OK
        if ($deleted) {
            $this->config->getDocumentManager()->flush();
        }
        echo json_encode(array('deleted' => $deleted));
    }

    protected function projectWantPostIsValid() {
        return $this->postHasIndices(array('project_id', 'organisation_id'));
    }

    public function projectWantRequestIsValid() {
        // signed in and POST valid?
        if (!isset($_SESSION['user']['id']) 
            || !$this->projectWantPostIsValid()) {
            return false;
        }

        // get current user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);
        if (is_null($user)) { return false; }

        // get organisation
        $this->organisation = $this->getById('Exposure\Model\SponsorOrganisation', $_POST['organisation_id']);
        if (is_null($this->organisation)) { return false; }
        
        // does current user belong to organisation?
        if (!$this->organisation->hasMember($user)) { return false; }

        // get project
        $this->project = $this->getById('Exposure\Model\Project', $_POST['project_id']);
        if (is_null($this->project)) { return false; }

        // does organisation already want this project?
        if ($this->organisation->wantsProject($this->project)) {
            return false;
        }

        return true;
    }

    protected function sendProjectWantedEmail(Project $project, 
        SponsorOrganisation $organisation, User $user) {
        $emailTemplate = $this->config->getTwig()->loadTemplate('project-wanted-email.tpl.html');

        $parameters = array (
            'organisation' => $organisation,
            'project' => $project,
            'language' => $user->getLanguageCode(),
            'canSeeSponsor' => $user->canSeeSponsorOrganisation($organisation),
        );

        return $this->sendEmail($template, $parameters, $user);
    }

    protected function notifyProjectWanted(ProjectWant $want) {
        // init notification
        $notification = new ProjectWantNotification;
        $notification->setStatus(ProjectWantNotification::STATUS_UNREAD);
        $notification->setWant($want);
        $notification->setDateTime(new \DateTime);
        $notification->setEvent(ProjectWantNotification::EVENT_WANTED_PROJECT);

        // attach notification to project
        $this->config->getDocumentManager()->persist($notification);
        $project->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to project owners if req'd
        foreach ($want->getProject()->getOwners() as $owner) {
            if ($owner->getReceiveNotificationsByEmail()) {
                $this->sendProjectWantedEmail($want->getProject(), 
                    $want->getSponsorOrganisation(), $owner);
            }
        }
    }

    public function projectWant() {
        header('Content-Type: application/json');
        if (!$this->projectWantRequestIsValid()) {
            echo json_encode(false);
            return;
        }

        $projectWant = new ProjectWant;
        $projectWant->setProject($this->project);
        $projectWant->setSponsorOrganisation($this->organisation);
        $projectWant->setDateTime(new \DateTime);
        $this->config->getDocumentManager()->persist($projectWant);
        $this->config->getDocumentManager()->flush();

        $this->notifyProjectWanted($projectWant);
        
        echo json_encode(true);
    }

    protected function projectBookmarkAddPostIsValid() {
        return $this->postHasIndices(array('project_id'));
    }

    protected function projectBookmarkAddRequestIsValid() {
        if (!$this->projectBookmarkAddPostIsValid()) {
            return false;
        }

        // get user and check if sponsor
        if (is_null($this->user = $this->getSignedInUser()) 
            || ($this->user->getType() != User::TYPE_SPONSOR)) {
            return false;
        }

        // get project
        if (is_null($this->project = $this->getById('Exposure\Model\Project', $_POST['project_id']))) {
            return false;
        }

        // check if not already bookmarked
        if ($this->user->hasBookmarkedProject($this->project)) {
            return false;
        }

        return true;
    }

    public function projectBookmarkAdd() {
        header('Content-Type: application/json');

        // validate request
        if (!$this->projectBookmarkAddRequestIsValid()) {
            echo json_encode(false);
            return;
        }

        $this->user->addBookmarkedProject($this->project);
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }

    protected function projectBookmarkRemovePostIsValid() {
        return $this->postHasIndices(array('project_id'));
    }

    protected function projectBookmarkRemoveRequestIsValid() {
        if (!$this->projectBookmarkRemovePostIsValid()) {
            return false;
        }

        // get user and check if sponsor
        if (is_null($this->user = $this->getSignedInUser()) 
            || ($this->user->getType() != User::TYPE_SPONSOR)) {
            return false;
        }

        // get project
        if (is_null($this->project = $this->getById('Exposure\Model\Project', $_POST['project_id']))) {
            return false;
        }

        // check if bookmarked
        if ($this->user->hasBookmarkedProject($this->project)) {
            return true;
        }

        return true;
    }

    public function projectBookmarkRemove() {
        header('Content-Type: application/json');

        // validate request
        if (!$this->projectBookmarkRemoveRequestIsValid()) {
            echo json_encode(false);
            return;
        }

        $this->user->removeBookmarkedProject($this->project);
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }
}

