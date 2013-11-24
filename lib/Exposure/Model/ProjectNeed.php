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

use Sociable\Model\MultiLanguageString;

abstract class ProjectNeed {
    protected $id = null;

    protected $type = null;
    const TYPE_FINANCIAL = 'financial';
    const TYPE_SERVICE = 'service';
    const TYPE_EQUIPMENT = 'equipment';
    const EXCEPTION_INVALID_TYPE = 'invalid type';
    const EXCEPTION_TYPE_MISMATCH = 'type mismatch';

    /** @var SponsorReturn */
    protected $return = null;
    const EXCEPTION_INVALID_SPONSOR_RETURN = 'invalid sponsor return';

    /** @var SponsorContribution */
    protected $contribution = null;
    const EXCEPTION_INVALID_CONTRIBUTION = 'invalid contribution';

    
    /** @var MultiLanguageString */
    protected $description = null;
    const DESCRIPTION_MAX_LENGTH = 250;
    const EXCEPTION_MISSING_DESCRIPTION = 'missing description';
    
    public function getId() {
        return $this->id;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        try {
            $this->validateType($type);
        } catch (Exception $e) {
            $this->type = null;
            throw $e;
        }
        $this->type = $type;
        return $this->type;
    }
    
    abstract protected function validateType($type);

    public function setDescription(MultiLanguageString $description) {
        try {
            $this->validateDescription($description);
        } catch (Exception $e) {
            $this->description = null;
            throw $e;
        }
        $this->description = $description;
        return $this->description;
        
    }
    
    protected function validateDescription(MultiLanguageString $description) {
        $description->validate(array(
            'not_empty' => true,
            'max_length' => self::DESCRIPTION_MAX_LENGTH));
    }

    public function getDescription() {
        return $this->description;
    }
    
    public function validate() {
        $this->validateType($this->type);
        if (is_null($this->description)) {
            throw new ProjectNeedException(self::EXCEPTION_MISSING_DESCRIPTION);
        }
        $this->validateDescription($this->description);
    }
    
    abstract public function getProject();

    public function getReturn() {
        return $this->return;
    }

    abstract public function setReturn(SponsorReturn $return);

    public function getContribution() {
        return $this->contribution;
    }

    abstract public function setContribution(SponsorContribution $contribution = null);

    public function isFulfilled() {
        return (!is_null($this->contribution));
    }

    public function isFulfilledByOrganisationWithUser(User $user) {
        if (!$this->isFulfilled()) { return false; }
        return $this->getContribution()->getContributor()->getSponsorUsers()->contains($user);
    }

    public function isFulfilledByOrganisation(SponsorOrganisation $organisation) {
        if (!$this->isFulfilled()) { return false; }
        return $this->getContribution()->getContributor() == $organisation;
    }
}


