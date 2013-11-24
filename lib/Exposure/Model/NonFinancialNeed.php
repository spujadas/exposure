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

use Doctrine\Common\Collections\ArrayCollection;

class NonFinancialNeed extends ProjectNeed {
    const EXCEPTION_RETURN_ALREADY_ASSIGNED_TO_FINANCIAL_NEED
        = 'return already assigned to financial need';
    
    const EXCEPTION_CONTRIBUTION_ALREADY_ASSIGNED_TO_FINANCIAL_NEED
        = 'contribution already assigned to financial need';

    /** @var Project */
    protected $project;
    const EXCEPTION_INVALID_PROJECT = 'invalid project';

    protected function validateType($type) {
        if (($type != ProjectNeed::TYPE_SERVICE)
                && ($type != ProjectNeed::TYPE_EQUIPMENT)) {
            throw new ProjectNeedException(ProjectNeed::EXCEPTION_INVALID_TYPE);
        }
    }
    
    public function setReturn(SponsorReturn $return) {
        if (!is_null($return->getReturnedFinancialNeedByAmount())) {
            $this->return = null;
            throw new NonFinancialNeedException(self::EXCEPTION_RETURN_ALREADY_ASSIGNED_TO_FINANCIAL_NEED);
        }
        $this->return = $return;
        return $this->return;
    }

    public function setContribution(SponsorContribution $contribution = null) {
        if (!is_null($contribution) && !is_null($contribution->getContributedFinancialNeedByAmount())) {
            $this->contribution = null;
            throw new NonFinancialNeedException(self::EXCEPTION_CONTRIBUTION_ALREADY_ASSIGNED_TO_FINANCIAL_NEED);
        }
        $this->contribution = $contribution;
        return $this->contribution;
    }
    
    public function getProject() {
        return $this->project;
    }

    public function setProject(Project $project) {
        $this->project = $project;
        return $this->project;
    }
    
    public function validate() {
        parent::validate();
        if (!is_a($this->return, 'Exposure\Model\SponsorReturn')) {
            throw new NonFinancialNeedException(self::EXCEPTION_INVALID_SPONSOR_RETURN);
        }
        if (!is_null($this->contribution) && !is_a($this->contribution, 'Exposure\Model\SponsorContribution')) {
            throw new NonFinancialNeedException(self::EXCEPTION_INVALID_SPONSOR_CONTRIBUTION);
        }
        if (!is_a($this->project, 'Exposure\Model\Project')) {
            throw new NonFinancialNeedException(self::EXCEPTION_INVALID_PROJECT);
        }
    }    
}


