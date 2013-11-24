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

use Exposure\Model\User;

class ContributionViews extends View {
    protected $contribution = null;
    protected $user = null;

    /*******************
        Contributions
    */

    protected function contributionsViewPreRoute() {
        return $this->isSignedInOrRedirect();
    }

    public function contributionsView() {
        if ($preRouting = $this->contributionsViewPreRoute()) {
            return $preRouting;
        }
        $this->loadTemplate('contributions-view.tpl.html');
        $this->displayTemplateWithSignedInUser();
    }


    /******************
        Contribution
    */

    // expected url is /contribution/<contribution-id>
    protected function viewPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no contribution id in URL => out
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // contribution id in URL - get contribution
        if (is_null($this->contribution = 
            $this->getById('Exposure\Model\SponsorContribution', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        switch ($this->signedInUser->getType()) {
        case User::TYPE_SPONSOR:
            if (!$this->signedInUser->belongsToOrganisation($this->contribution->getContributor())) {
                return self::NOT_AUTHORISED;
            }
            break;
        case User::TYPE_PROJECT_OWNER:
            if (!$this->signedInUser->ownsProject($this->contribution->getProject())) {
                return self::NOT_AUTHORISED;
            }
            break;
        }
    }

    public function view() {
        if ($preRouting = $this->viewPreRoute()) {
            return $preRouting;
        }
    	$this->loadTemplate('contribution-view.tpl.html');

        // render
        $this->displayTemplate(array(
            
            'user' => $this->signedInUser,
            'contribution' => $this->contribution,
            'userIsProjectOwner' => $this->signedInUser->getType() == User::TYPE_PROJECT_OWNER,
            'userIsSponsor' => $this->signedInUser->getType() == User::TYPE_SPONSOR,
        ));
    }
}