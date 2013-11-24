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

use Exposure\Model\SponsorOrganisation,
    Exposure\Model\User,
	Exposure\Model\Theme;

class OrganisationViews extends View {
    protected $user = null;
    protected $organisation = null;


    /************
        Common
    */

    protected function canUserSeeOrganisation(User $user, SponsorOrganisation $organisation) {
        switch ($user->getType()) {
        case User::TYPE_SPONSOR:
            // check if (sponsor) user belongs to org...
            if (!$organisation->hasMember($user)) {
                return false; 
            }
            break;
        
        case User::TYPE_PROJECT_OWNER:
            // ... or if (project owner) user has a project wanted by org
            if (!$user->canSeeSponsorOrganisation($organisation)) {
                return false; 
            }
            break;

        default:
            return false; 
        }

        return true;
    }


    /**********
        Edit
    */

    protected function editPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // sponsor?
        if ($this->signedInUser->getType() != User::TYPE_SPONSOR) {
            return self::NOT_AUTHORISED;
        }

        // no organisation slug in URL => edit draft organisation
        if (count($this->request) < 2) {
            return; // $organisation = null
        }

        // organisation slug in URL - get organisation
        if (is_null($this->organisation = 
            $this->getByUrlSlug('Exposure\Model\SponsorOrganisation', $this->request[1]))) {
            return self::INVALID_PARAMS;
        }

        // check if user belongs to the organisation
        if (!$this->organisation->hasMember($this->signedInUser)) {
            return self::NOT_AUTHORISED;
        }
    }

    public function edit() {
        if ($preRouting = $this->editPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('organisation-edit.tpl.html');
        
        // render
        $this->displayTemplate(array(
            
            
    
            'organisation' => $this->organisation,
            'themes' => $this->getRootThemes(),
            'businessSectors' => $this->getBusinessSectorsInLanguage($_SESSION['language']),
            'maxnumberwebpresences' => SponsorOrganisation::WEB_PRESENCES_MAX_COUNT,
        ));

        unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
    }


    /***********
        View
    */

    protected function viewPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // no organisation slug in URL or doesn't match a organisation?
        if ((count($this->request) < 2) 
            || is_null($this->organisation = $this->getByUrlSlug('Exposure\Model\SponsorOrganisation', $this->request[1])))
        {
            return self::INVALID_PARAMS;
        }
       
        if (!$this->canUserSeeOrganisation($this->signedInUser, $this->organisation)) {
            return self::NOT_AUTHORISED;
        }
    }

    public function view() {
        if ($preRouting = $this->viewPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('organisation-view.tpl.html');
        
        // render
        $this->displayTemplate(array(
            
            
    
            'organisation' => $this->organisation,
            'userIsMember' => $this->organisation->hasMember($this->signedInUser),
        ));
    }


    /**********
        Logo
    */

    public function logo() {
        if ($preRouting = $this->viewPreRoute()) {
            $this->emptyImage(); 
            return; 
        }

        header('Content-type: ' . $this->organisation->getLogo()->getMime());
        echo $this->organisation->getLogo()->getImageFile()->getBytes();
    }


    /*******************
        Organisations
    */

    protected function organisationsViewPreRoute() {
        if ($result = $this->isSignedInOrRedirect()) {
            return $result;
        }

        // sponsor?
        if ($this->signedInUser->getType() != User::TYPE_SPONSOR) {
            return self::NOT_AUTHORISED;
        }
    }

    public function organisationsView() {
        if ($preRouting = $this->organisationsViewPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('organisations-view.tpl.html');
        
        // render
        $this->displayTemplate(array(
            
    
            'user' => $this->signedInUser,
        ));
    }

}