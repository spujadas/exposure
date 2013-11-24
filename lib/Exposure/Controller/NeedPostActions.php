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

use Exposure\Model\ProjectNeed,
    Exposure\Model\FinancialNeed,
    Exposure\Model\NonFinancialNeed,
	Exposure\Model\FinancialNeedByAmount,
    Exposure\Model\Project,
    Exposure\Model\User,
    Exposure\Model\SponsorContribution,
    Exposure\Model\SponsorContributionNotification,
    Sociable\Utility\StringValidator,
    Sociable\Utility\NumberValidator;

class NeedPostActions extends \Exposure\Controller\PostActions {
    protected $errors;
    protected $autofill;

    protected $organisation = null;


    /************
        Common
    */

    protected function updateFinancialNeedTotalAmount(FinancialNeed $financialNeed, 
        $totalAmountValueString, $currencyCode) {
        $totalAmountValue = is_numeric($totalAmountValueString)?(float) $totalAmountValueString:$totalAmountValueString;
        $numberExceptionArray = array (
            'error_field' => 'total_amount_value',
            'default_error_message' => 'this value is invalid', // $this->translate->_('financialNeedUpdate.error.invalidValue');
        );
        $this->updateMultiCurrencyValue($financialNeed, 
            'getTotalAmount', 'setTotalAmount',
            $totalAmountValue, $currencyCode, $numberExceptionArray);

        $this->autofill['total_amount_value'] = $totalAmountValue;
    }

    protected function updateFinancialNeed(FinancialNeed $financialNeed) {
        $this->autofill = array();
        $this->errors = array();

        // update financial need attributes
        $this->updateFinancialNeedTotalAmount($financialNeed, $_POST['total_amount_value'],
            $_POST['currency']);
    }
    

    /***************************
        Financial need create
    */

    protected function financialNeedCreateIsValidPost() {
        return $this->postHasIndices(array('total_amount_value', 'currency', 'project_id'));
    }
    
    public function financialNeedCreate() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->financialNeedCreateIsValidPost()) { return self::INVALID_POST; }
        
        // get project from POST
        $project = $this->getById('Exposure\Model\Project', $_POST['project_id']);
        if (is_null($project)) { return self::INVALID_POST; }
        if (!$this->canSignedInUserEditProject($project)) { return self::NOT_AUTHORISED; }

        $financialNeed = new FinancialNeed;
        $this->config->getDocumentManager()->persist($financialNeed);

        // update from form data
        $this->updateFinancialNeed($financialNeed);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['request'] = $project->getUrlSlug();
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('financialNeedSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $financialNeed->setProject($project);
        $this->config->getDocumentManager()->flush();
        $_SESSION['request'] = $project->getUrlSlug();
        $_SESSION['message'] = array (
                'content' => 'financial need saved',
                // 'content' => $this->translate->_('financialNeedSave.success.financialNeedSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }


    /*************************
        Financial need save
    */
    protected function financialNeedSaveIsValidPost() {
        return $this->postHasIndices(array('total_amount_value', 'currency', 'financial_need_id'));
    }

    public function financialNeedSave() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->financialNeedSaveIsValidPost()) { return self::INVALID_POST; }

        // get financial need from POST
        $financialNeed = $this->getById('Exposure\Model\FinancialNeed', 
            $_POST['financial_need_id']);
        if (is_null($financialNeed)) {  return self::INVALID_POST; }

        if (!$this->canSignedInUserEditProject($financialNeed->getProject())) { 
            return self::NOT_AUTHORISED; 
        }

        // update from form data
        $this->updateFinancialNeed($financialNeed);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['request'] = $financialNeed->getId();
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('financialNeedSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $this->config->getDocumentManager()->flush();
        $_SESSION['request'] = $financialNeed->getProject()->getUrlSlug();
        $_SESSION['message'] = array (
                'content' => 'financial need saved',
                // 'content' => $this->translate->_('financialNeedSave.success.financialNeedSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }

    /*************************************
        Financial need by amount create
    */

    protected function financialNeedByAmountCreateIsValidPost() {
        return $this->postHasIndices(array('amount_value', 'currency', 
            'description', 'financial_need_id'));
    }

    protected function updateFinancialNeedByAmountAmount(FinancialNeedByAmount $financialNeedByAmount, 
        $amountValueString, $currencyCode) {
        $amountValue = is_numeric($amountValueString)?(float) $amountValueString:$amountValueString;
        $numberExceptionArray = array (
            'error_field' => 'amount_value',
            'default_error_message' => 'this value is invalid', // $this->translate->_('financialNeedByAmountUpdate.error.invalidValue');
        );
        $this->updateMultiCurrencyValue($financialNeedByAmount, 
            'getAmount', 'setAmount',
            $amountValue, $currencyCode, $numberExceptionArray);

        $this->autofill['amount_value'] = $amountValue;
    }

    protected function updateFinancialNeedByAmountDescription(FinancialNeedByAmount $financialNeedByAmount, 
        $description, $languageCode) {
        $stringExceptionArray = array (
            'error_field' => 'description',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'description is missing', // $this->translate->_('financialNeedByAmountUpdate.error.emptyDescription'),
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long', // $this->translate->_('financialNeedByAmountUpdate.error.descriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('financialNeedByAmountUpdate.error.invalidDescription');
        );
        $this->updateMultiLanguageString($financialNeedByAmount, 
            'getDescription', 'setDescription',
            $description, $languageCode, $stringExceptionArray);

        $this->autofill['description'] = $description;
    }

    protected function updateFinancialNeedByAmount(FinancialNeedByAmount $financialNeedByAmount) {
        $this->autofill = array();
        $this->errors = array();

        // update financial need attributes
        $this->updateFinancialNeedByAmountAmount($financialNeedByAmount, $_POST['amount_value'],
            $_POST['currency']);
        $this->updateFinancialNeedByAmountDescription(
            $financialNeedByAmount, $_POST['description'], $_SESSION['language']);
    }

    public function financialNeedByAmountCreate() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->financialNeedByAmountCreateIsValidPost()) { return self::INVALID_POST; }
        
        // get financial need from POST
        $financialNeed = $this->getById('Exposure\Model\FinancialNeed', 
            $_POST['financial_need_id']);
        if (is_null($financialNeed)) { return self::INVALID_POST; }
        if (!$this->canSignedInUserEditProject($financialNeed->getProject())) { return self::NOT_AUTHORISED; }

        $financialNeedByAmount = new FinancialNeedByAmount;
        $this->config->getDocumentManager()->persist($financialNeedByAmount);

        // update from form data
        $this->updateFinancialNeedByAmount($financialNeedByAmount);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['request'] = $financialNeed->getId();
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('financialNeedByAmountSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $financialNeed->addFinancialNeedByAmount($financialNeedByAmount);
        $this->config->getDocumentManager()->flush();
        $_SESSION['request'] = $financialNeed->getProject()->getUrlSlug();
        $_SESSION['message'] = array (
                'content' => 'financial need by amount saved',
                // 'content' => $this->translate->_('financialNeedByAmountSave.success.financialNeedByAmountSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }

    /***********************************
        Financial need by amount save
    */

    protected function financialNeedByAmountSaveIsValidPost() {
        return $this->postHasIndices(array('amount_value', 'currency', 
            'description', 'financial_need_by_amount_id'));
    }

    public function financialNeedByAmountSave() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->financialNeedByAmountSaveIsValidPost()) { return self::INVALID_POST; }
        
        // get financial need by amount from POST
        $financialNeedByAmount = $this->getById('Exposure\Model\FinancialNeedByAmount', 
            $_POST['financial_need_by_amount_id']);
        if (is_null($financialNeedByAmount)) { return self::INVALID_POST; }
        if (!$this->canSignedInUserEditProject($financialNeedByAmount->getContributedTotal()->getProject())) { 
            return self::NOT_AUTHORISED; 
        }

        // update from form data
        $this->updateFinancialNeedByAmount($financialNeedByAmount);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['request'] = $financialNeedByAmount->getId();
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('financialNeedByAmountSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $this->config->getDocumentManager()->flush();
        $_SESSION['request'] = $financialNeedByAmount->getContributedTotal()->getProject()->getUrlSlug();
        $_SESSION['message'] = array (
                'content' => 'financial need by amount saved',
                // 'content' => $this->translate->_('financialNeedByAmountSave.success.financialNeedByAmountSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }

    /*************************************
        Financial need by amount delete
    */

    protected function financialNeedByAmountDeleteIsValidPost() {
        return $this->postHasIndices(array('financial_need_by_amount_id'));
    }

    protected function getFinancialNeedByAmountIfDeleteRequestValid() {
        // valid request?
        if (!$this->financialNeedByAmountDeleteIsValidPost()) { return null; }
        
        // existing financial need by amount?
        $financialNeedByAmount = $this->getById('Exposure\Model\FinancialNeedByAmount', 
            $_POST['financial_need_by_amount_id']);
        if (is_null($financialNeedByAmount)) { return null; }

        // check if financial need by amount belongs to the user
        if (!$this->canSignedInUserEditProject($financialNeedByAmount->getContributedTotal()->getProject())) { 
            return null; 
        }

        // only deletable in unfulfilled
        if ($financialNeedByAmount->isFulfilled()) {
            return null;
        }

        return $financialNeedByAmount;
    }

    public function financialNeedByAmountDelete() {
        header('Content-Type: application/json');

        // get financial need by amount to delete
        if (is_null($financialNeedByAmount = $this->getFinancialNeedByAmountIfDeleteRequestValid())) {
            echo json_encode(false);
            return;
        }

        // remove financial need by amount from financial need
        if ($financialNeedByAmount->getContributedTotal()->getNeedsByAmount()->removeElement($financialNeedByAmount)) {
            echo json_encode(false);
        }

        // delete financial need by amount
        $this->config->getDocumentManager()->remove($financialNeedByAmount);
        
        // also delete return if exists
        if (!is_null($return = $financialNeedByAmount->getReturn())) {
            $this->config->getDocumentManager()->remove($return);
        }
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }


    /*******************************
        Non financial need create
    */

    protected function nonFinancialNeedCreateIsValidPost() {
        return $this->postHasIndices(array('type', 'description', 'project_id'));
    }

    protected function updateNonFinancialNeedType(NonFinancialNeed $nonFinancialNeed, $type) {
        switch ($type) {
        case ProjectNeed::TYPE_SERVICE:
        case ProjectNeed::TYPE_EQUIPMENT:
            $nonFinancialNeed->setType($type);
            break;
        default:
            $this->errors['type'] = 'type is invalid'; // $this->translate->_('nonFinancialNeedUpdate.error.invalidType'),
        }

        $this->autofill['type'] = $type;
    }

    protected function updateNonFinancialNeedDescription(NonFinancialNeed $nonFinancialNeed, 
        $description, $languageCode) {
        $stringExceptionArray = array (
            'error_field' => 'description',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'description is missing', // $this->translate->_('nonFinancialNeedUpdate.error.emptyDescription'),
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long', // $this->translate->_('nonFinancialNeedUpdate.error.descriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('nonFinancialNeedUpdate.error.invalidDescription');
        );
        $this->updateMultiLanguageString($nonFinancialNeed, 
            'getDescription', 'setDescription',
            $description, $languageCode, $stringExceptionArray);

        $this->autofill['description'] = $description;
    }

    protected function updateNonFinancialNeed(NonFinancialNeed $nonFinancialNeed) {
        $this->autofill = array();
        $this->errors = array();

        // update financial need attributes
        $this->updateNonFinancialNeedType($nonFinancialNeed, $_POST['type']);
        $this->updateNonFinancialNeedDescription(
            $nonFinancialNeed, $_POST['description'], $_SESSION['language']);
    }


    /*******************************
        Non financial need create
    */

    public function nonFinancialNeedCreate() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->nonFinancialNeedCreateIsValidPost()) { return self::INVALID_POST; }
        
        // get project from POST
        $project = $this->getById('Exposure\Model\Project', $_POST['project_id']);
        if (is_null($project)) { return self::INVALID_POST; }
        if (!$this->canSignedInUserEditProject($project)) { return self::NOT_AUTHORISED; }

        $nonFinancialNeed = new NonFinancialNeed;
        $this->config->getDocumentManager()->persist($nonFinancialNeed);

        // update from form data
        $this->updateNonFinancialNeed($nonFinancialNeed);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['request'] = $project->getUrlSlug();
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('nonFinancialNeedSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $nonFinancialNeed->setProject($project);
        $this->config->getDocumentManager()->flush();
        $_SESSION['request'] = $project->getUrlSlug();
        $_SESSION['message'] = array (
                'content' => 'non-financial need saved',
                // 'content' => $this->translate->_('nonFinancialNeedSave.success.nonFinancialNeedSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }


    /*****************************
        Non financial need save
    */

    protected function nonFinancialNeedSaveIsValidPost() {
        return $this->postHasIndices(array('type', 'description', 
            'non_financial_need_id'));
    }
    
    public function nonFinancialNeedSave() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->nonFinancialNeedSaveIsValidPost()) { return self::INVALID_POST; }
        
        // get non financial need from POST
        $nonFinancialNeed = $this->getById('Exposure\Model\NonFinancialNeed',
            $_POST['non_financial_need_id']);
        if (is_null($nonFinancialNeed)) { return self::INVALID_POST; }
        if (!$this->canSignedInUserEditProject($nonFinancialNeed->getProject())) { return self::NOT_AUTHORISED; }

        // update from form data
        $this->updateNonFinancialNeed($nonFinancialNeed);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            $_SESSION['request'] = $nonFinancialNeed->getId();
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('nonFinancialNeedSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $this->config->getDocumentManager()->flush();
        $_SESSION['request'] = $nonFinancialNeed->getProject()->getUrlSlug();
        $_SESSION['message'] = array (
                'content' => 'non-financial need saved',
                // 'content' => $this->translate->_('nonFinancialNeedSave.success.nonFinancialNeedSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }


    /*******************************
        Non financial need delete
    */

    protected function nonFinancialNeedDeleteIsValidPost() {
        return $this->postHasIndices(array('non_financial_need_id'));
    }

    protected function getNonFinancialNeedIfDeleteRequestValid() {
        // valid request?
        if (!$this->nonFinancialNeedDeleteIsValidPost()) { return null; }
        
        // existing non financial need?
        $nonFinancialNeed = $this->getById('Exposure\Model\NonFinancialNeed',
            $_POST['non_financial_need_id']);
        if (is_null($nonFinancialNeed)) { return null; }
        // check if non financial need belongs to the user
        if (!$this->canSignedInUserEditProject($nonFinancialNeed->getProject())) { 
            return null; 
        }

        // only deletable if unfulfilled
        if ($nonFinancialNeed->isFulfilled()) {
            return null;
        }

        return $nonFinancialNeed;
    }

    public function nonFinancialNeedDelete() {
        header('Content-Type: application/json');

        // get non financial need to delete
        if (is_null($nonFinancialNeed = $this->getNonFinancialNeedIfDeleteRequestValid())) {
            echo json_encode(false);
            return;            
        }

        // also delete return if exists
        if (!is_null($return = $nonFinancialNeed->getReturn())) {
            $this->config->getDocumentManager()->remove($return);
        }

        $this->config->getDocumentManager()->remove($nonFinancialNeed);
        $this->config->getDocumentManager()->flush();
        echo json_encode(true);
    }


    /****************
        Contribute
    */

    protected function contributionRequestIsValidPost() {
        return $this->postHasIndices(array('organisation_id', 
            'need_type', 'need_id'));
    }

    protected function getNeedIfContributionRequestValid() {
        // valid request?
        if (!$this->contributionRequestIsValidPost()) { return null; }

        // valid need type?
        switch ($_POST['need_type']) {
        case 'financial-need-by-amount':
            $repository = 'Exposure\Model\FinancialNeedByAmount';
            break;
        case 'non-financial-need':
            $repository = 'Exposure\Model\NonFinancialNeed';
            break;
        default:
            return null;
        }

        // valid and unfulfilled need?
        if (is_null($need = $this->getById($repository, $_POST['need_id'])) 
            || $need->isFulfilled()) {
            return null;
        };

        // get user and check if sponsor
        if (is_null($user = $this->getSignedInUser()) 
            || ($user->getType() != User::TYPE_SPONSOR)) {
            return null;
        }

        // get organisation and check if user belongs to organisation
        if (is_null($this->organisation = 
            $this->getById('Exposure\Model\SponsorOrganisation', $_POST['organisation_id']))) {
            return null;
        }
        
        // does current user belong to organisation?
        if (!$this->organisation->hasMember($user)) { 
            return null; 
        }

        return $need;
    }

    protected function sendContributionProposedEmail(SponsorContribution $contribution, 
        User $user, Project $project) {
        $emailTemplate = $this->config->getTwig()->loadTemplate('need-contribution-proposed-email.tpl.html');

        $parameters = array (
            'contribution' => $contribution,
            'organisation' => $contribution->getContributor(),
            'project' => $project,
            'language' =>  $user->getLanguageCode(),
            'canSeeSponsor' => $user->canSeeSponsorOrganisation($contribution->getContributor()),
        );

        return $this->sendEmail($emailTemplate, $parameters, $user);
    }

    protected function notifyContributionProposed(SponsorContribution $contribution, 
        Project $project) {
        // init notification
        $notification = new SponsorContributionNotification;
        $notification->setStatus(SponsorContributionNotification::STATUS_UNREAD);
        $notification->setContribution($contribution);
        $notification->setDateTime(new \DateTime);
        $notification->setEvent(SponsorContributionNotification::EVENT_PROPOSAL_SUBMITTED_BY_SPONSOR);

        // attach notification to project
        $this->config->getDocumentManager()->persist($notification);
        $project->addNotification($notification);

        // flush and done
        $this->config->getDocumentManager()->flush();

        // cascade by mail to project owners if req'd
        foreach ($project->getOwners() as $owner) {
            if ($owner->getReceiveNotificationsByEmail()) {
                $this->sendContributionProposedEmail($contribution, $owner, $project);
            }
        }
    }

    public function contribute() {
        header('Content-Type: application/json');

        // get need to contribute to
        if (is_null($need = $this->getNeedIfContributionRequestValid())) {
            echo json_encode(false);
            return;
        }

        // instantiate contribution and attach to need
        $contribution = new SponsorContribution;
        $contribution->setContributor($this->organisation);
        $contribution->setStatus(SponsorContribution::STATUS_PROPOSAL_SUBMITTED_BY_SPONSOR);
        $this->config->getDocumentManager()->persist($contribution);

        $need->setContribution($contribution);
        $this->config->getDocumentManager()->flush();

        // notify and send email as req'd
        $this->notifyContributionProposed($contribution, $need->getProject());

        echo json_encode(true);
    }
}


