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

use Exposure\Model\SponsorReturnType,
    Exposure\Model\User;

class ReturnViews extends View {
    protected $return = null;
    protected $need = null;
    protected $project = null;


    /**********
        View
    */

    // URL format is /return/<return-id>
    protected function viewPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no return id in URL => out
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        if (is_null($this->return = 
            $this->getById('Exposure\Model\SponsorReturn', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }
    }

    public function view() {
        if ($preRouting = $this->viewPreRoute()) {
            return $preRouting;
        }

        $need = $this->return->getNeed();

        $this->loadTemplate('return-view.tpl.html');

        // is user from a sponsoring organisation?
        if (is_null($this->signedInUser) 
            || is_null($contribution = $need->getContribution())) {            
            $userIsProjectSponsor = false;
        }
        else {
            $userIsProjectSponsor = $this->signedInUser
                ->belongsToOrganisation($contribution->getContributor());
        }

        $this->displayTemplate(array(
            'return' => $this->return,
            'need' => $need,
            'userIsProjectOwner' => is_null($this->signedInUser)?
                false:
                $this->signedInUser->ownsProject($need->getProject()),
            'userIsProjectSponsor' => $userIsProjectSponsor,
        ));
    }


    /****************
        Return new
    */

    // e.g. /return-new/[financial-need-by-amount|non-financial-need]/<need-id>
    protected function returnNewPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no need type and id in URL => out
        if (count($this->request) < 3) {
            return self::INVALID_PARAMS;
        }

        // select object for project need type
        switch ($this->request[1]) {
        case 'financial-need-by-amount':
            $object = 'Exposure\Model\FinancialNeedByAmount';
            break;
        case 'non-financial-need':
            $object = 'Exposure\Model\NonFinancialNeed';
            break;
        default:
            return self::INVALID_PARAMS;
        }

        // get need from URL
        if (is_null($this->need = $this->getById($object, $this->request[2]))) {
            return self::INVALID_PARAMS;
        }

        // if need already has a return => out
        if (!is_null($this->need->getReturn())) {
            return self::INVALID_PARAMS;
        }

        $this->project = $this->need->getProject();

        // check if user owns the project
        if (!$this->signedInUser->ownsProject($this->project)) {
            return self::NOT_AUTHORISED;
        }
    }        

    public function returnNew() {
        if ($preRouting = $this->returnNewPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('return-new.tpl.html');
        $this->displayTemplate(array(
            'need' => $this->need,
            'typetree' => $this->getSponsorReturnTypes(),
            'project' => $this->project,
        ));


        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /*****************
        Return edit
    */

    // URL format is /return-edit/<return-id>
    protected function returnEditPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no return id in URL => out
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // get return from URL
        if (is_null($this->return = 
            $this->getById('Exposure\Model\SponsorReturn', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        // get project
        if (is_null($this->project = $this->return->getNeed()->getProject())) {
            return self::INVALID_PARAMS;
        }

        // check if signed in user owns the project
        if (!$this->signedInUser->ownsProject($this->project)) {
            return self::NOT_AUTHORISED;
        }

        // check if need unfulfilled
        if ($this->return->getNeed()->isFulfilled()) {
            return self::NOT_AUTHORISED;
        }
    }

    public function returnEdit() {
        if ($preRouting = $this->returnEditPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('return-edit.tpl.html');
        $this->displayTemplate(array(
            'return' => $this->return,
            'need' => $this->need,
            'typetree' => $this->getSponsorReturnTypes(),
            'project' => $this->project,
            'new' => true,
        ));


        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /*************
        Returns
    */

    protected function returnsViewPreRoute() {
        return $this->isSignedInOrRedirect();
    }

    public function returnsView() {
        if ($preRouting = $this->returnsViewPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('returns-view.tpl.html');
        $this->displayTemplateWithSignedInUser();
    }    
}
