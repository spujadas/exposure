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

use Sociable\Model\MultiCurrencyValue,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator;

class SponsorReturnOnFinancialContribution {
    protected $id = null;
    
    /** @var Theme */
    protected $theme = null;
    const EXCEPTION_INVALID_THEME = 'invalid theme';
    
    /** @var MultiCurrencyValue */
    protected $amount = null;
    const EXCEPTION_INVALID_AMOUNT = 'invalid amount';
    
    /** @var MultiLanguageString */
    protected $description = null;
    const EXCEPTION_INVALID_DESCRIPTION = 'invalid description';
    
    /** @var SponsorReturnType */
    protected $type = null;
    const EXCEPTION_INVALID_TYPE = 'invalid type';
    
    public function getId() {
        return $this->id;
    }
    
    public function getTheme() {
        return $this->theme;
    }

    public function setTheme(Theme $theme = null) {
        $this->theme = $theme;
        return $this->theme;
    }
    
    public function setDescription(MultiLanguageString $description) {
        try {
            $this->validateReturnDescription($description);
        }
        catch (Exception $e) {
            $this->description = null;
            throw $e;
        }
        $this->description = $description;
        return $this->description;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    protected function validateReturnDescription (MultiLanguageString $description) {
        $description->validate(array(
            'not_empty' => true,
            'max_length' => SponsorReturn::DESCRIPTION_MAX_LENGTH));
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount(MultiCurrencyValue $amount) {
        $this->amount = $amount;
        return $this->amount;
    }
    
    protected function validateAmount(MultiCurrencyValue $amount) {
        $amount->validate();
    }
    
        public function getType() {
        return $this->type;
    }

    public function setType(SponsorReturnType $type) {
        $this->type = $type;
        return $this->type;
    }

    public function validate() {
        if (!is_null($this->theme) && !is_a($this->theme, 'Exposure\Model\Theme')) {
            throw new SponsorReturnOnFinancialContributionException(
                    self::EXCEPTION_INVALID_THEME);
        }
        if (!is_a($this->amount, 'Sociable\Model\MultiCurrencyValue')) {
            throw new SponsorReturnOnFinancialContributionException(
                    self::EXCEPTION_INVALID_AMOUNT);
        }
        $this->validateAmount($this->amount);
        if (!is_a($this->description, 'Sociable\Model\MultiLanguageString')) {
            throw new SponsorReturnOnFinancialContributionException(
                    self::EXCEPTION_INVALID_DESCRIPTION);
        }
        $this->validateReturnDescription($this->description);
        if (!is_a($this->type, 'Exposure\Model\SponsorReturnType')) {
            throw new SponsorReturnOnFinancialContributionException(
                    self::EXCEPTION_INVALID_TYPE);
        }
    }
}


