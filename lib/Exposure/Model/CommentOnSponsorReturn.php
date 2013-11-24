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

class CommentOnSponsorReturn extends Comment {
    /** @var SponsorReturn */
    protected $sponsorReturn = null;
    const EXCEPTION_INVALID_SPONSOR_RETURN = 'invalid sponsor organisation';
    
    public function __construct() {
        $this->setType(parent::TYPE_COMMENT_ON_SPONSOR_RETURN);
    }
    
    public function getSponsorReturn() {
        return $this->sponsorReturn;
    }

    public function setSponsorReturn(SponsorReturn $sponsorReturn) {
        $this->sponsorReturn = $sponsorReturn;
        return $this->sponsorReturn;
    }
    
    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_COMMENT_ON_SPONSOR_RETURN) {
            throw new CommentException(parent::EXCEPTION_TYPE_MISMATCH);
        }

        if (!is_a($this->sponsorReturn, 'Exposure\Model\SponsorReturn')) {
            throw new CommentException(self::EXCEPTION_INVALID_SPONSOR_RETURN);
        }
    }
}


