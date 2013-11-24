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

use Exposure\Model\SponsorReturn,
    Exposure\Model\SponsorReturnType,
    Exposure\Model\SponsorReturnNotification,
    Exposure\Model\ProjectNeed,
    Exposure\Model\Project,
    Exposure\Model\User,
    Sociable\Utility\StringValidator;

class ReturnPostActions extends \Exposure\Controller\PostActions {
    protected $errors;
    protected $autofill;


    /************
        Common
    */

    protected function returnStatusUpdateRequestIsValidPost() {
        return $this->postHasIndices(array('return_id'));
    }

    protected function getReturnIfStatusUpdateRequestValid() {
        // valid request?
        if (!$this->returnStatusUpdateRequestIsValidPost()) { return null; }

        return $this->getById('Exposure\Model\SponsorReturn', 
            $_POST['return_id']);
    }

    protected function getReturnIfStatusUpdateRequestFromProjectOwnerValid() {
        if (is_null($return = $this->getReturnIfStatusUpdateRequestValid())) {
            return null;
        }

        // check if user owns the project
        if (is_null($user = $this->getSignedInUser())
            || ($user->getType() != User::TYPE_PROJECT_OWNER)
            || !$user->ownsProject($return->getNeed()->getProject())) {
            return null; 
        }

        return $return;
    }

    protected function getReturnIfStatusUpdateRequestFromSponsorValid() {
        if (is_null($return = $this->getReturnIfStatusUpdateRequestValid())) {
            return null;
        }

        // check if the user belongs to the organisation that is sponsoring the project
        if (is_null($user = $this->getSignedInUser())
            || ($user->getType() != User::TYPE_SPONSOR)
            || !$user->belongsToOrganisation($return->getNeed()->getContribution()->getContributor())) { 
            return null; 
        }

        return $return;
    }

    protected function sendNotificationEmail($emailTemplate, 
        SponsorReturn $return, User $user, Project $project) {
        $parameters = array (
            'return' => $return,
            'need' => $return->getNeed(),
            'project' => $project,
            'language' => $user->getLanguageCode(),
            'currency' => $_SESSION['currency'],
        );

        return $this->sendEmail($emailTemplate, $parameters, $user);
    }

    protected function updateReturnType(SponsorReturn $return, $typeLabel) {
        $type = $this->getByLabel('Exposure\Model\SponsorReturnType', $typeLabel);
        if (is_null($type)) { 
            $this->errors['type'] = 'type is invalid'; // $this->translate->_('returnUpdate.error.invalidType'),
        }
        else {
            $return->setType($type);
        }

        $this->autofill['type'] = $typeLabel;
    }

    protected function updateReturnDescription(SponsorReturn $return, 
        $description, $languageCode) {
        $stringExceptionArray = array (
            'error_field' => 'description',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'description missing', // $this->translate->_('returnUpdate.error.emptyDescription'),
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long', // $this->translate->_('returnUpdate.error.descriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('returnUpdate.error.invalidDescription');
        );
        $this->updateMultiLanguageString($return, 
            'getDescription', 'setDescription',
            $description, $languageCode, $stringExceptionArray);

        $this->autofill['description'] = $description;
    }

    protected function updateReturn(SponsorReturn $return) {
        $this->autofill = array();
        $this->errors = array();

        // update return attributes
        $this->updateReturnType($return, $_POST['type']);
        $this->updateReturnDescription(
            $return, $_POST['description'], $_SESSION['language']);
    }


    /*******************
        Return create
    */

    protected function returnCreateIsValidPost() {
        return $this->postHasIndices(array('type', 'description', 'need_type', 
            'need_id'));
    }

    public function returnCreate() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->returnCreateIsValidPost()) { return self::INVALID_POST; }
        
        // get need from POST
        switch ($_POST['need_type']) {
        case 'financial-need-by-amount':
            $repository = 'Exposure\Model\FinancialNeedByAmount';
            break;
        case 'non-financial-need':
            $repository = 'Exposure\Model\NonFinancialNeed';
            break;
        default:
            return self::INVALID_POST;
        }

        $need = $this->getById($repository, $_POST['need_id']);
        if (is_null($need) || !is_null($need->getReturn())) { 
            return self::INVALID_POST; 
        }

        $project = $need->getProject();
        if (!$this->canSignedInUserEditProject($project)) { return self::NOT_AUTHORISED; }

        $return = new SponsorReturn;
        $this->config->getDocumentManager()->persist($return);
        $return->setStatus(SponsorReturn::STATUS_NOT_STARTED);

        // update from form data
        $this->updateReturn($return);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['request'] = $_POST['need_type'] . '/' . $need->getId();
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('returnSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $need->setReturn($return);
        $this->config->getDocumentManager()->flush();
        $_SESSION['request'] = $return->getId();
        $_SESSION['message'] = array (
                'content' => 'compensation saved',
                // 'content' => $this->translate->_('returnSave.success.returnSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }


    /*****************
        Return save
    */

    protected function returnSaveIsValidPost() {
        return $this->postHasIndices(array('type', 'description', 'return_id'));
    }
    
    public function returnSave() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->returnSaveIsValidPost()) { return self::INVALID_POST; }
        
        // get return from POST
        $return = $this->getById('Exposure\Model\SponsorReturn', $_POST['return_id']);
        if (is_null($return)) { return self::INVALID_POST; }
        
        // check if need unfulfilled
        $need = $return->getNeed();
        if ($need->isFulfilled()) {
            return self::NOT_AUTHORISED;
        }

        // get project
        if (is_null($project = $need->getProject())) {
            return self::INVALID_POST;
        }

        if (!$this->canSignedInUserEditProject($project)) { return self::NOT_AUTHORISED; }

        // update from form data
        $this->updateReturn($return);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['request'] = $return->getId();
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('returnSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $this->config->getDocumentManager()->flush();

        $_SESSION['request'] = $return->getId();
        $_SESSION['message'] = array (
                'content' => 'compensation saved',
                // 'content' => $this->translate->_('returnSave.success.returnSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }


    /******************
        Return start
    */

    protected function notifyStarted(SponsorReturn $return) {
        // init notification
        $notification = new SponsorReturnNotification;
        $notification->setStatus(SponsorReturnNotification::STATUS_UNREAD);
        $notification->setReturn($return);
        $notification->setDateTime(new \DateTime);
        $notification->setEvent(SponsorReturnNotification::EVENT_STARTED);

        // attach notification to organisation
        $this->config->getDocumentManager()->persist($notification);
        $contributor = $return->getNeed()->getContribution()->getContributor();
        $contributor->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to sponsor users if req'd
        $project = $return->getNeed()->getProject();
        foreach ($contributor->getSponsorUsers() as $user) {
            if ($user->getReceiveNotificationsByEmail()) {
                $this->sendReturnStartedEmail($return, $user, $project);
            }
        }
    }

    protected function sendReturnStartedEmail(SponsorReturn $return, 
        User $user, Project $project) {
        $emailTemplate = $this->config->getTwig()
            ->loadTemplate('return-started-email.tpl.html');

        return $this->sendNotificationEmail($emailTemplate, $return, 
            $user, $project);
    }

    public function returnStart() {
        $this->updateStatusAndNotify(
            'getReturnIfStatusUpdateRequestFromProjectOwnerValid',
            SponsorReturn::STATUS_NOT_STARTED,
            SponsorReturn::STATUS_IN_PROGRESS,
            'notifyStarted');
    }


    /*********************
        Return complete
    */

    protected function notifyCompleted(SponsorReturn $return) {
        $notification = new SponsorReturnNotification;
        $notification->setStatus(SponsorReturnNotification::STATUS_UNREAD);
        $notification->setReturn($return);
        $notification->setDateTime(new \DateTime);
        $notification->setEvent(SponsorReturnNotification::EVENT_COMPLETED_BY_PROJECT);

        // attach notification to organisation
        $this->config->getDocumentManager()->persist($notification);
        $contributor = $return->getNeed()->getContribution()->getContributor();
        $contributor->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to sponsor users if req'd
        $project = $return->getNeed()->getProject();
        foreach ($contributor->getSponsorUsers() as $user) {
            if ($user->getReceiveNotificationsByEmail()) {
                $this->sendReturnCompletedEmail($return, $user, $project);
            }
        }
    }

    protected function sendReturnCompletedEmail(SponsorReturn $return, 
        User $user, Project $project) {
        $emailTemplate = $this->config->getTwig()
            ->loadTemplate('return-completed-email.tpl.html');

        return $this->sendNotificationEmail($emailTemplate, $return, 
            $user, $project);
    }

    public function returnComplete() {
        $this->updateStatusAndNotify(
            'getReturnIfStatusUpdateRequestFromProjectOwnerValid',
            SponsorReturn::STATUS_IN_PROGRESS,
            SponsorReturn::STATUS_COMPLETED_BY_PROJECT_OWNER,
            'notifyCompleted');
    }


    /********************
        Return approve
    */

    protected function notifyApproved(SponsorReturn $return) {
        $notification = new SponsorReturnNotification;
        $notification->setStatus(SponsorReturnNotification::STATUS_UNREAD);
        $notification->setReturn($return);
        $notification->setDateTime(new \DateTime);
        $notification->setEvent(SponsorReturnNotification::EVENT_APPROVED);

        // attach notification to project
        $this->config->getDocumentManager()->persist($notification);
        $project = $return->getNeed()->getProject();
        $project->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to project owners if req'd
        foreach ($project->getOwners() as $user) {
            if ($user->getReceiveNotificationsByEmail()) {
                $this->sendReturnApprovedEmail($return, $user, $project);
            }
        }
    }

    protected function sendReturnApprovedEmail(SponsorReturn $return, 
        User $user, Project $project) {
        $emailTemplate = $this->config->getTwig()
            ->loadTemplate('return-approved-email.tpl.html');

        return $this->sendNotificationEmail($emailTemplate, $return, 
            $user, $project);
    }

    public function returnApprove() {
        $this->updateStatusAndNotify(
            'getReturnIfStatusUpdateRequestFromSponsorValid',
            SponsorReturn::STATUS_COMPLETED_BY_PROJECT_OWNER,
            SponsorReturn::STATUS_APPROVED,
            'notifyApproved');
    }

}


