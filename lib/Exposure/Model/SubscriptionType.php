<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Model;

use Sociable\Utility\StringValidator,
    Sociable\Model\MultiLanguageString;

use Doctrine\Common\Collections\ArrayCollection;

class SubscriptionType {
    protected $id = null;
    
    protected $label = null;
    const LABEL_MAX_LENGTH = 32;
    
    protected $name = null;
    const NAME_MAX_LENGTH = 64;
    
    /** @var ArrayCollection of SubscriptionPrice */
    protected $subscriptionPrices;
    const EXCEPTION_INVALID_SUBSCRIPTION_PRICE = 'invalid subscription price';
    const EXCEPTION_NO_SUBSCRIPTION_PRICE = 'no subscription price';
    
    /** @var ProjectRights */
    protected $projectRights = null;
    const EXCEPTION_INVALID_PROJECT_RIGHTS = 'invalid project rights';
    
    /** @var ViewRights */
    protected $viewRights = null;
    const EXCEPTION_INVALID_VIEW_RIGHTS = 'invalid view rights';
    
    public function __construct() {
        $this->subscriptionPrices = new ArrayCollection;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setLabel($label) {
        try {
            $this->validateLabel($label);
        } catch (Exception $e) {
            $this->label = null;
            throw $e;
        }

        $this->label = $label;
        return $this->label;
    }

    protected function validateLabel($label) {
        StringValidator::validate($label, array(
            'max_length' => self::LABEL_MAX_LENGTH,
            'not_empty' => true)
        );
    }

    public function getLabel() {
        return $this->label;
    }
    
    public function setName(MultiLanguageString $name) {
        try {
            $this->validateName($name);
        } catch (Exception $e) {
            $this->name = null;
            throw $e;
        }
        $this->name = $name;
        return $this->name;
    }
    
    protected function validateName(MultiLanguageString $name) {
        $name->validate(array(
            'not_empty' => true,
            'max_length' => self::NAME_MAX_LENGTH));
    }

    public function getName() {
        return $this->name;
    }

    public function getSubscriptionPrices() {
        return $this->subscriptionPrices;
    }
        
    public function addSubscriptionPrice(SubscriptionPrice $subscriptionPrice) {    
        $this->subscriptionPrices[] = $subscriptionPrice;
    }

    public function removeSubscriptionPrice(SubscriptionPrice $subscriptionPrice) {
        return $this->subscriptionPrices->removeElement($subscriptionPrice);
    }
    
    public function getProjectRights() {
        return $this->projectRights;
    }

    public function setProjectRights(ProjectRights $projectRights) {
        $this->projectRights = $projectRights;
        return $this->projectRights;
    }

    public function getViewRights() {
        return $this->viewRights;
    }

    public function setViewRights(ViewRights $viewRights) {
        $this->viewRights = $viewRights;
        return $this->viewRights;
    }

        
    public function validate() {
        $this->validateLabel($this->label);
        $this->validateName($this->name);
        foreach ($this->subscriptionPrices as $subscriptionPrice) {
            if (!is_a($subscriptionPrice, 'Exposure\Model\SubscriptionPrice')) {
                throw new SubscriptionTypeException(self::EXCEPTION_INVALID_SUBSCRIPTION_PRICE);
            }
        }
        if ($this->subscriptionPrices->count() == 0) {
            throw new SubscriptionTypeException(self::EXCEPTION_NO_SUBSCRIPTION_PRICE);
        }
        if (!is_a($this->projectRights, 'Exposure\Model\ProjectRights')) {
            throw new SubscriptionTypeException(self::EXCEPTION_INVALID_PROJECT_RIGHTS);
        }
        if (!is_a($this->viewRights, 'Exposure\Model\ViewRights')) {
            throw new SubscriptionTypeException(self::EXCEPTION_INVALID_VIEW_RIGHTS);
        }
    }

}

