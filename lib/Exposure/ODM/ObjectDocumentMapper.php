<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\ODM;

use Exposure\Model\ModerationStatus;

use Doctrine\ODM\MongoDB\DocumentManager;

class ObjectDocumentMapper extends \Sociable\ODM\ObjectDocumentMapper {
    public static function getRootThemes(DocumentManager $dm) {
        return $dm->createQueryBuilder('Exposure\Model\Theme')
            ->field('parentTheme')
            ->exists(false)
            ->getQuery()
            ->execute();
    }

    public static function getSelfOrChildrenThemesMatchingLabel(DocumentManager $dm, 
        $label) {
        return $dm->createQueryBuilder('Exposure\Model\Theme')
            ->field('path')->equals(new \MongoRegex('/^\\|' . $label . '\\|/'))
            ->getQuery()
            ->execute();
    }

    public static function getSponsorReturnTypes(DocumentManager $dm) {
        return $dm->createQueryBuilder('Exposure\Model\SponsorReturnType')
            ->getQuery()
            ->execute();
    }

    public static function getPreviouslyApprovedProjects(DocumentManager $dm) {
        return $dm->createQueryBuilder('Exposure\Model\Project')
            ->field('moderationStatus.status')->in(array(
                ModerationStatus::STATUS_APPROVED, 
                ModerationStatus::STATUS_USER_EDIT))
            ->getQuery()
            ->execute();
    }

    public static function getSubscriptionTypeAndDurations(DocumentManager $dm) {
        return $dm->createQueryBuilder('Exposure\Model\SubscriptionTypeAndDuration')
            ->sort('durationInMonths', 'asc')
            ->getQuery()
            ->execute();
    }

    public static function getSubscriptionTypeAndDuration(DocumentManager $dm, 
        $label, $duration) {
        if (is_null($subscriptionType = $dm
            ->getRepository('Exposure\Model\SubscriptionType')
            ->findOneByLabel($label))) {
            return null;
        }
        return $dm->getRepository('Exposure\Model\SubscriptionTypeAndDuration')
            ->findOneBy(array(
                'durationInMonths' => $duration,
                'type.id' => $subscriptionType->getId()
            )) ;
    }
}

