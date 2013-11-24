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

use Exposure\Model\Subscription;

class SubscriptionViews extends View {
	protected $subscriptionTypeAndDuration = null;
	protected $user = null;
	protected $payerId = null;


	/************
	    Common
	*/

	// checks for valid user id, subscription id and subscription confirmation 
	// code in URL
	protected function checkSubscriptionOrderParams() {
		if ((count($this->request) < 4) 
        	|| is_null($this->user = 
        		$this->getById('Exposure\Model\User', $this->request[1]))
			|| is_null($this->subscription = 
        		$this->getById('Exposure\Model\Subscription', $this->request[2]))
			|| is_null($this->subscription->getPaymentConfirmationCode())
			|| !$this->subscription->getPaymentConfirmationCode()
				->confirmCode($this->request[3])
    	) {
        	return false;
        }
        return true;
	}


	/***********************
		Subscription view
	*/

	protected function subscriptionsViewPreRoute() {
		if ($result = $this->checkIfSignedInUserIsProjectOwner()) {
			return $result;
		}
	}

	public function subscriptionsView() {
		if ($preRouting = $this->subscriptionsViewPreRoute()) {
            return $preRouting;
        }
		$this->loadTemplate('subscriptions.tpl.html');
        $this->displayTemplateWithSignedInUser();
	}


	/***************
		Subscribe
	*/

	protected function subscribePreRoute() {
		if ($result = $this->checkIfSignedInUserIsProjectOwner()) {
			return $result;
		}
	}

	public function subscribe() {
		if ($preRouting = $this->subscribePreRoute()) {
            return $preRouting;
        }
		$this->loadTemplate('subscribe.tpl.html');
        $this->displayTemplateWithSignedInUser(
        	array(
        		'subscriptions' => $this->getSubscriptionTypeAndDurations(),
        	)
    	);
	}


	/**************************
		Subscription history
	*/

	protected function subscriptionHistoryPreRoute() {
		if ($result = $this->checkIfSignedInUserIsProjectOwner()) {
			return $result;
		}
	}

	public function subscriptionHistory() {
		if ($preRouting = $this->subscriptionHistoryPreRoute()) {
            return $preRouting;
        }
		$this->loadTemplate('subscription-history.tpl.html');
		$this->displayTemplateWithSignedInUser();
	}


	/************************
		Subscription order
	*/

	// populates $subscriptionTypeAndDuration
	protected function subscriptionOrderPreRoute() {
		if ($result = $this->checkIfSignedInUserIsProjectOwner()) {
			return $result;
		}

		// check for valid subscriptionTypeAndDuration id in URL
        if ((count($this->request) < 2) 
        	|| is_null($this->subscriptionTypeAndDuration = 
        		$this->getById('Exposure\Model\SubscriptionTypeAndDuration', $this->request[1]))) {
        	return self::INVALID_PARAMS;
        }

        // current or next subscription must be empty
		if (!$this->signedInUser->canSubscribe()) {
			$_SESSION['message'] = array (
                'content' => 'as long as you have an ongoing subscription and an upcoming subscription, you cannot subscribe',
                // 'content' => $this->translate->_('subscriptionOrder.warning.noFreeSubscriptionSlots'),
                'type' => 'warning');
			return self::INVALID_PARAMS;
		}
	}

	public function subscriptionOrder() {
		if ($preRouting = $this->subscriptionOrderPreRoute()) {
            return $preRouting;
        }
		$this->loadTemplate('subscription-order.tpl.html');
		$this->displayTemplateWithSignedInUser(array(
			'subscription' => $this->subscriptionTypeAndDuration));
	}


	/**************************
		Billing address edit
	*/

	protected function billingAddressEditPreRoute() {
		if ($result = $this->checkIfSignedInUserIsProjectOwner()) {
			return $result;
		}

		// check for valid subscriptionTypeAndDuration id in URL
        if ((count($this->request) < 2) 
        	|| is_null($this->subscriptionTypeAndDuration = 
        		$this->getById('Exposure\Model\SubscriptionTypeAndDuration', $this->request[1]))) {
        	return self::INVALID_PARAMS;
        }

	}

	public function billingAddressEdit() {
		if ($preRouting = $this->billingAddressEditPreRoute()) {
            return $preRouting;
        }

        // get user's country if no billing address is defined
        if (is_null($billingAddress = $this->signedInUser->getBillingAddress())) {
        	$country = $this->signedInUser->getCountry();
        }
        else {
        	$country = $billingAddress->getCountry();
        }
		
        // get country code for form data
        if (isset($_SESSION['autofill']['country'])) {
            $countryCode = $_SESSION['autofill']['country'];
        }
        elseif (is_null($country)) {
            $countryCode = '';
        }
        else {
            $countryCode = $country->getCode();
        }

		$this->loadTemplate('billing-address-edit.tpl.html');
		$this->displayTemplateWithSignedInUser(array(
			'subscription' => $this->subscriptionTypeAndDuration,
			'countries' => $this->getCountriesInLanguage($_SESSION['language']),
			'country' => $countryCode,
		));

		unset($_SESSION['autofill']);
        unset($_SESSION['errors']);
	}

	/**********************************
		Subscription order confirmed
	*/

	protected function subscriptionOrderConfirmedPreRoute() {
		if ($result = $this->checkIfSignedInUserIsProjectOwner()) {
			return $result;
		}
		if (!$this->checkSubscriptionOrderParams()
			|| ($this->user != $this->signedInUser)) {
			return self::INVALID_PARAMS;
		}
	}

	public function subscriptionOrderConfirmed() {
		if ($preRouting = $this->subscriptionOrderConfirmedPreRoute()) {
            return $preRouting;
        }

        if ($this->subscription == $this->signedInUser->getCurrentSubscription()) {
        	$this->subscription->startSubscription();
        }
        $this->subscription->setStatus(Subscription::STATUS_ACTIVE);
        $this->subscription->setPaymentConfirmationCode(null);
        $this->config->getDocumentManager()->flush();

        $this->loadTemplate('subscription-order-confirmed.tpl.html');
		$this->displayTemplateWithSignedInUser();
	}


	/*******************************
		Subscription order failed
	*/

	protected function subscriptionOrderFailedPreRoute() {
		if ($result = $this->checkIfSignedInUserIsProjectOwner()) {
			return $result;
		}
	}

	public function subscriptionOrderFailed() {
		if ($preRouting = $this->subscriptionOrderFailedPreRoute()) {
            return $preRouting;
        }

		if ($this->checkSubscriptionOrderParams()
			&& ($this->user == $this->signedInUser)) {
			$this->user->removeSubscription($this->subscription);
			$this->config->getDocumentManager()->remove($this->subscription);
			$this->config->getDocumentManager()->flush();
		}

		$this->loadTemplate('subscription-order-failed.tpl.html');
		$this->displayTemplateWithSignedInUser();
	}


}


