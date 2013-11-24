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

class CommentOnSponsorOrganisation extends Comment {
    /** @var SponsorOrganisation */
    protected $sponsorOrganisation = null;
    const EXCEPTION_INVALID_SPONSOR_ORGANISATION = 'invalid sponsor organisation';
    
    public function __construct() {
        $this->setType(parent::TYPE_COMMENT_ON_SPONSOR_ORGANISATION);
    }
    
    public function getSponsorOrganisation() {
        return $this->sponsorOrganisation;
    }

    public function setSponsorOrganisation(SponsorOrganisation $sponsorOrganisation) {
        $this->sponsorOrganisation = $sponsorOrganisation;
        return $this->sponsorOrganisation;
    }
    
    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_COMMENT_ON_SPONSOR_ORGANISATION) {
            throw new CommentException(parent::EXCEPTION_TYPE_MISMATCH);
        }

        if (!is_a($this->sponsorOrganisation, 'Exposure\Model\SponsorOrganisation')) {
            throw new CommentException(self::EXCEPTION_INVALID_SPONSOR_ORGANISATION);
        }
    }
}


