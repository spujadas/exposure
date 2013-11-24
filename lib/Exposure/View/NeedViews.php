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

class NeedViews extends View {
    protected $financialNeed = null;
    protected $financialNeedByAmount = null;
    protected $nonFinancialNeed = null;
    protected $project = null;
    protected $user = null;
    protected $need = null;

    /************
        Common
    */

    // populates $financialNeed, $project, $user
    protected function checkOwnedProjectWithFinancialNeedIdPassedAsArgument() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no financial need id in URL => out
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // get financial need from URL
        if (is_null($this->financialNeed = 
            $this->getById('Exposure\Model\FinancialNeed', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        // check if signed in user owns the project
        $this->project = $this->financialNeed->getProject();
        if (!$this->signedInUser->ownsProject($this->project)) {
            return self::NOT_AUTHORISED;
        }
    }

    // populates $financialNeedByAmount, $project, $user
    protected function checkOwnedProjectWithFinancialNeedByAmountIdPassedAsArgument() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no financial need by amount id in URL => out
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // get financial need by amount from URL
        if (is_null($this->financialNeedByAmount = 
            $this->getById('Exposure\Model\FinancialNeedByAmount', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        // check if user owns the project
        $this->project = $this->financialNeedByAmount->getContributedTotal()->getProject();
        if (!$this->signedInUser->ownsProject($this->project)) {
            return self::NOT_AUTHORISED;
        }
    }

    // populates $nonFinancialNeed, $project, $user
    protected function checkOwnedProjectWithNonFinancialNeedIdPassedAsArgument() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no non financial need id in URL => out
        if (count($this->request) < 2) {
            return self::INVALID_PARAMS;
        }

        // get non financial need from URL
        if (is_null($this->nonFinancialNeed = 
            $this->getById('Exposure\Model\NonFinancialNeed', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        // check if user owns the project
        $this->project = $this->nonFinancialNeed->getProject();
        if (!$this->signedInUser->ownsProject($this->project)) {
            return self::NOT_AUTHORISED;
        }
    }

    /***********
        Needs
    */

	// expected URL is /needs/<project-slug>
    protected function needsPreRoute() {
        return $this->checkOwnedProjectSlugPassedAsArgument();
    }

    public function needs() {
        if ($preRouting = $this->needsPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('needs-view.tpl.html');
        $this->displayTemplate(array(
            
            'project' => $this->project,
        ));
    }


    /*************************
        Financial need edit
    */

    // expected URL is /financial-need-edit/<financial-need-id>
    protected function financialNeedEditPreRoute() {
        $result = $this->checkOwnedProjectWithFinancialNeedIdPassedAsArgument();
    }

    public function financialNeedEdit() {
        if ($preRouting = $this->financialNeedEditPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('financial-need-edit.tpl.html');
        $this->displayTemplate(array(
            
            'financialneed' => $this->financialNeed,
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /*******************************
        Financial need list items
    */

    // expected URL is /financial-need-list-items/<financial-need-id>
    protected function financialNeedListItemsPreRoute() {
        return $this->checkOwnedProjectWithFinancialNeedIdPassedAsArgument();
    }
 
    public function financialNeedListItems() {
        if ($preRouting = $this->financialNeedListItemsPreRoute()) {
            return; // invoked by AJAX
        }

        $this->loadTemplate('financial-need-list-items.tpl.html');
        $this->displayTemplate(array(
            
            'financialneed' => $this->financialNeed,
        ));
    }


    /************************
        Financial need new
    */

    // expected URL is /financial-need-new/<project-slug>
    protected function financialNeedNewPreRoute() {
        return $this->checkOwnedProjectSlugPassedAsArgument();
    }

    public function financialNeedNew() {
        if ($preRouting = $this->financialNeedNewPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('financial-need-new.tpl.html');
        $this->displayTemplate(array(
            
            'project' => $this->project,
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /***********************************
        Financial need by amount edit
    */

	// expected URL is /financial-need-by-amount-edit/<financial-need-by-amount-id>
    protected function financialNeedByAmountEditPreRoute() {
        if ($result = $this->checkOwnedProjectWithFinancialNeedByAmountIdPassedAsArgument()) {
            return $result;
        }
        if ($this->financialNeedByAmount->isFulfilled()) {
            return self::NOT_AUTHORISED;
        }
    }

    public function financialNeedByAmountEdit() {
        if ($preRouting = $this->financialNeedByAmountEditPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('financial-need-by-amount-edit.tpl.html');
        $this->displayTemplate(array(
            
            'financialneedbyamount' => $this->financialNeedByAmount,
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }

    /**********************************
        Financial need by amount new
    */

	// expected URL is /financial-need-by-amount-new/<financial-need-id>
    protected function financialNeedByAmountNewPreRoute() {
        return $this->checkOwnedProjectWithFinancialNeedIdPassedAsArgument();
    }

    public function financialNeedByAmountNew() {
        if ($preRouting = $this->financialNeedByAmountNewPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('financial-need-by-amount-new.tpl.html');
        $this->displayTemplate(array(
            
            'financialneed' => $this->financialNeed,
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /*****************************
        Non financial need edit
    */

    // expected URL is /non-financial-need-edit/<non-financial-need-id>
    protected function nonFinancialNeedEditPreRoute() {
        if ($result = $this->checkOwnedProjectWithNonFinancialNeedIdPassedAsArgument()) {
            return $result;
        }
        if ($this->nonFinancialNeed->isFulfilled()) {
            return self::NOT_AUTHORISED;
        }
    }

    public function nonFinancialNeedEdit() {
        if ($preRouting = $this->nonFinancialNeedEditPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('non-financial-need-edit.tpl.html');
        $this->displayTemplate(array(
            
            'nonfinancialneed' => $this->nonFinancialNeed,
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /****************************
        Non financial need new
    */

    // expected URL is /non-financial-need-new/<project-slug>
    protected function nonFinancialNeedNewPreRoute() {
        return $this->checkOwnedProjectSlugPassedAsArgument();
    }

    public function nonFinancialNeedNew() {
        if ($preRouting = $this->nonFinancialNeedNewPreRoute()) {
            return $preRouting;
        }

        // get project by slug from URL
        $project = $this->getByUrlSlug('Exposure\Model\Project', $this->request[1]);

        $this->loadTemplate('non-financial-need-new.tpl.html');
        $this->displayTemplate(array(
            
            'project' => $this->project,
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /***********************************
        Non financial need list items
    */

    // expected URL is /non-financial-needs-list-items/<project-url-slug>
    protected function nonFinancialNeedsListItemsPreRoute() {
        return $this->checkOwnedProjectSlugPassedAsArgument();
    }

    public function nonFinancialNeedsListItems() {
        if ($preRouting = $this->nonFinancialNeedsListItemsPreRoute()) {
            return; // AJAX
        }

        $this->loadTemplate('non-financial-needs-list-items.inc.tpl.html');
        $this->displayTemplate(array(
            
            'project' => $this->project,
        ));
    }
}
