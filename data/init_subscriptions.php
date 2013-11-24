<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

/*
 * Generates subscription data
 * 
 * INSTRUCTIONS
 * Configure the $subscriptionTypeAndDurationList, $subscriptionTypes and
 * $subscriptionPricesList arrays to your liking, then run:
 * $ php init_subscriptions.php
 */

/* These are the subscriptions that are made available to the project owners */
$subscriptionTypeAndDurationList = array (
    array (
        'duration' => 1, // in months
        'type_label' => 'default', // see $subscriptionTypeList array
    ),
    array (
        'duration' => 3,
        'type_label' => 'default',
    ),
    array (
        'duration' => 12,
        'type_label' => 'default',
    )
) ;

$subscriptionTypeList = array (
    'default' => array (
        'name' => 'Standard',
        'name_language' => 'en',
        'subscription_prices' => array (
            array (
                'duration' => 1,
                'monthly_price' => array (
                    'currency' => 'EUR',
                    'value' => 15,
                ),
            ),
            array (
                'duration' => 3,
                'monthly_price' => array (
                    'currency' => 'EUR',
                    'value' => 12,
                ),
            ),
            array (
                'duration' => 12,
                'monthly_price' => array (
                    'currency' => 'EUR',
                    'value' => 7.5,
                ),
            ),
        ),
        'project_rights' => array (
            'display_description' => true,
            'number_displayed_photos' => 10,
            'display_web_presence' => true,
        ),
        'view_rights' => array (
            'can_see_sponsors' => true,
        ),
    ),
) ;


/* ********************************************************************** */
// Don't touch anything below if looking for a basic usage of this script

require_once 'bootstrap.php' ;
require_once (ROOT.'/sys/config/config.inc.php') ; // initialises $config

use Exposure\Model\SubscriptionTypeAndDuration,
    Exposure\Model\SubscriptionType,
    Exposure\Model\SubscriptionPrice,
    Exposure\Model\ProjectRights,
    Exposure\Model\ViewRights,
    Sociable\ODM\ObjectDocumentMapper,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\MultiCurrencyValue;

$dm = $config->getDocumentManager() ;

// Create subscription types
echo "Creating subscription types\n" ;
$new = false ;
foreach ($subscriptionTypeList as $label => $subscriptionTypeItem) {
    echo "- $label " ;
    if ($subscriptionType = 
        ObjectDocumentMapper::getByLabel($dm, 'Exposure\Model\SubscriptionType', $label)) {
        echo "already exists (ignoring)\n" ;
        continue ;
    }
    $subscriptionType = new SubscriptionType ;
    $subscriptionType->setLabel($label) ;
    $subscriptionType->setName(new MultiLanguageString(
        $subscriptionTypeItem['name'], 
        $subscriptionTypeItem['name_language'])
    ) ;
    foreach ($subscriptionTypeItem['subscription_prices'] as $subscriptionPriceItem) {
        $subscriptionPrice = new SubscriptionPrice ;
        $subscriptionPrice->setDurationInMonths($subscriptionPriceItem['duration']) ;
        $subscriptionPrice->setMonthlyPrice(new MultiCurrencyValue(
            $subscriptionPriceItem['monthly_price']['value'],
            $subscriptionPriceItem['monthly_price']['currency'])
        ) ;
        $subscriptionType->addSubscriptionPrice($subscriptionPrice) ;
    }
    $projectRights = new ProjectRights ;
    $projectRights->setDisplayDescription($subscriptionTypeItem['project_rights']['display_description']) ;
    $projectRights->setNumberDisplayedPhotos($subscriptionTypeItem['project_rights']['number_displayed_photos']) ;
    $projectRights->setDisplayWebPresence($subscriptionTypeItem['project_rights']['display_web_presence']) ;
    $subscriptionType->setProjectRights($projectRights) ;

    $viewRights = new ViewRights ;
    $viewRights->setCanSeeSponsors($subscriptionTypeItem['view_rights']['can_see_sponsors']) ;
    $subscriptionType->setViewRights($viewRights) ;

    $dm->persist($subscriptionType) ;
    echo "created\n";
    $new = true ;
}

if ($new) {
    $dm->flush() ;
}


// Create subscription type/duration items
echo "Creating subscription type/duration items\n" ;
$new = false ;
foreach ($subscriptionTypeAndDurationList as $subscriptionTypeAndDurationItem) {
    $duration = $subscriptionTypeAndDurationItem['duration'] ;
    $typeLabel = $subscriptionTypeAndDurationItem['type_label'] ;

    echo '- Subscription type|duration: ' . $duration . '|' 
        . $typeLabel . ' ' ;

    if (is_null($subscriptionType = 
        ObjectDocumentMapper::getByLabel($dm, 'Exposure\Model\SubscriptionType', $typeLabel))) {
        echo "missing subscription type label $typeLabel -- exiting\n" ;
        exit ;
    }
    if ($dm->getRepository('Exposure\Model\SubscriptionTypeAndDuration')
        ->findOneBy(array(
            'durationInMonths' => $duration,
            'type.id' => $subscriptionType->getId()))) {
        echo "already exists (ignoring)\n" ;
        continue ;
    }

    $subscriptionTypeAndDuration = new SubscriptionTypeAndDuration ;
    $subscriptionTypeAndDuration->setDurationInMonths($duration) ;
    $subscriptionTypeAndDuration->setType($subscriptionType) ;
    
    $dm->persist($subscriptionTypeAndDuration);
    echo "created\n";
    $new = true ;

}

if ($new) {
   $dm->flush() ;
}

?>