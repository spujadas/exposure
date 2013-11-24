<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Controller;

use Sociable\Model\Address,
    Exposure\Model\User,
    Exposure\Model\Subscription,
    Sociable\Model\ConfirmationCode;

class SubscriptionPostActions extends \Exposure\Controller\PostActions {
    protected $errors;
    protected $autofill;

    protected $user = null;
    protected $subscriptionTypeAndDuration = null;

    protected function billingAddressSaveIsValidPost() {
        return $this->postHasIndices(array('address1', 'post_code', 'city',
            'country', 'subscription_id'));
    }

    protected function updateAddress1(Address $address, $address1) {
        $this->autofill['address1'] = $address1;
        try {
            $address->setAddress1($address1);
        }
        catch (\Exception $e) {
            $this->errors['address1'] = 'this address line is invalid';
                // $this->translate->_('billingAddressSave.error.invalidAddress1');
        }
    }

    protected function updateAddress2(Address $address, $address2) {
        $this->autofill['address2'] = $address2;
        if (empty($address2)) {
            $address->setAddress2(null);
            return;
        }
        try {
            $address->setAddress2($address2);
        }
        catch (\Exception $e) {
            $this->errors['address2'] = 'this address line is invalid';
                // $this->translate->_('billingAddressSave.error.invalidAddress2');
        }
    }

    protected function updateCityAreaOrDistrict(Address $address, $cityAreaOrDistrict) {
        $this->autofill['city_area_or_district'] = $cityAreaOrDistrict;
        if (empty($cityAreaOrDistrict)) {
            $address->setCityAreaOrDistrict(null);
            return;
        }
        try {
            $address->setCityAreaOrDistrict($cityAreaOrDistrict);
        }
        catch (\Exception $e) {
            $this->errors['city_area_or_district'] = 'this city area or district is invalid';
                // $this->translate->_('billingAddressSave.error.invalidCityAreaOrDistrict');
        }
    }

    protected function updatePostCode(Address $address, $postCode) {
        $this->autofill['post_code'] = $postCode;
        try {
            $address->setPostCode($postCode);
        }
        catch (\Exception $e) {
            $this->errors['post_code'] = 'this post code is invalid';
                // $this->translate->_('billingAddressSave.error.invalidPostCode');
        }
    }

    protected function updateCityOrTownOrVillage(Address $address, $city) {
        $this->autofill['city'] = $city;
        try {
            $address->setCityOrTownOrVillage($city);
        }
        catch (\Exception $e) {
            $this->errors['city'] = 'this city is invalid';
                // $this->translate->_('billingAddressSave.error.invalidCityOrTownOrVillage');
        }
    }

    protected function updateCounty(Address $address, $county) {
        $this->autofill['county'] = $county;
        if (empty($county)) {
            $address->setCounty(null);
            return;
        }
        try {
            $address->setCounty($county);
        }
        catch (\Exception $e) {
            $this->errors['county'] = 'this county is invalid';
                // $this->translate->_('billingAddressSave.error.invalidCounty');
        }
    }

    protected function updateCountry(Address $address, $countryCode) {
        $this->autofill['country'] = $countryCode;
        $country = $this->getByCode('Sociable\Model\Country', $countryCode);
        if (is_null($country)) {
            $this->errors['country'] = 'this country is invalid';
                // $this->translate->_('billingAddressSave.error.invalidCountry');
        }
        else {
            $address->setCountry($country);
        }
    }


    protected function updateBillingAddress(User $user) {
        $this->errors = array();
        
        if (is_null($address = $user->getBillingAddress())) {
            $address = new Address;
            $user->setBillingAddress($address);
        }

        // update all fields
        $this->updateAddress1($address, $_POST['address1']);
        $this->updateAddress2($address, $_POST['address2']);
        $this->updateCityAreaOrDistrict($address, $_POST['city_area_or_district']);
        $this->updatePostCode($address, $_POST['post_code']);
        $this->updateCityOrTownOrVillage($address, $_POST['city']);
        $this->updateCounty($address, $_POST['county']);
        $this->updateCountry($address, $_POST['country']);

        // clear dm in case of errors
        if ($this->errors) {
            $this->config->getDocumentManager()->clear();
        }
        // flush otherwise
        else {
            $this->config->getDocumentManager()->flush();
        } 
        
        $_SESSION['errors'] = $this->errors;
    }

    public function billingAddressSave() {
        if (is_null($user = $this->getSignedInUser())) { return self::NOT_SIGNED_IN; }
        if (!$this->billingAddressSaveIsValidPost()) { return self::INVALID_POST; }
        $this->updateBillingAddress($user);

        $_SESSION['request'] = $_POST['subscription_id'];

        if ($this->errors) {
             $_SESSION['errors'] = $this->errors;
             $_SESSION['message'] = array (
                 'content' => 'some fields are incorrectly filled in',
                 // 'content' => $this->translate->_('billingAddressSave.error.incorrectFields'),
                 'type' => 'error');


             return self::INVALID_DATA;
        }
            
        $_SESSION['message'] = array (
                'content' => 'billing address saved',
                // 'content' => $this->translate->_('billingAddressSave.success.billingAddressSaved'),
                'type' => 'success');
        
        unset($_SESSION['autofill']);

        return self::SUCCESS;
    }

    protected function subscriptionPayIsValidPost() {
        if (!$this->postHasIndices(array('user_id', 'subscription_id'))) {
            return false;
        }
        if ($this->user->getId() != $_POST['user_id']) { return false; }
        if (is_null($this->subscriptionTypeAndDuration = 
            $this->getById('Exposure\Model\SubscriptionTypeAndDuration', 
                $_POST['subscription_id']))) {
            return false;
        }
        if (!$this->user->canSubscribe()) {
            return false;
        }
        return true;
    }

    public function subscriptionPay() {
        if (is_null($this->user = $this->getSignedInUser())) { return self::NOT_SIGNED_IN; }
        if (!$this->subscriptionPayIsValidPost()) { return self::INVALID_POST; }

        // get price
        $monthlyPrice = $this->subscriptionTypeAndDuration->getMonthlyPrice();
        $currencyCode = $this->user->getCurrencyCode();
        if (is_null($monthlyPriceInCurrency = $monthlyPrice->getValueByCurrencyCode($currencyCode))) {
            $currencyCode = $monthlyPrice->getDefaultCurrencyCode();
            if (is_null($monthlyPriceInCurrency = $monthlyPrice->getDefaultValue())) {
                return self::MAINTENANCE;
            }
        }
        $durationInMonths = $this->subscriptionTypeAndDuration->getDurationInMonths();
        $totalAmountInCurrency = $monthlyPriceInCurrency * $durationInMonths;
        
        // remove zombie pending payments
        if (!is_null($currentSubscription = $this->user->getCurrentSubscription()) &&
            ($currentSubscription->getStatus() == Subscription::STATUS_PENDING_PAYMENT)) {
            $this->user->setCurrentSubscription(null);
            $this->config->getDocumentManager()->remove($currentSubscription);
        }
        if (!is_null($nextSubscription = $this->user->getNextSubscription()) &&
            ($nextSubscription->getStatus() == Subscription::STATUS_PENDING_PAYMENT)) {
            $this->user->setNextSubscription(null);
            $this->config->getDocumentManager()->remove($nextSubscription);
        }

        // add new subscription to subscription "queue"
        $subscription = new Subscription;
        $subscription->setStatus(Subscription::STATUS_PENDING_PAYMENT);
        $subscription->setPaymentConfirmationCode(new ConfirmationCode);
        $subscription->setTypeAndDuration($this->subscriptionTypeAndDuration);
        $this->config->getDocumentManager()->persist($subscription);

        if (!$this->user->addSubscription($subscription)) {
            return self::MAINTENANCE;
        }

        $this->config->getDocumentManager()->flush();

        // Hey! Free subs!
        $_SESSION['request'] = $this->user->getId() .
            '/' . $subscription->getId() .
            '/' . $subscription->getPaymentConfirmationCode()->getConfirmationCode();
        return self::SUCCESS;
}
}

