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

use Exposure\View\View;

class GetController extends \Sociable\Controller\GetController {
    public static function initActions() {
        self::$actions = array(
            /*
              root pages
            */

            'index' => array(
                'routes' => array(
                    View::USER_REGISTERED => '/awaiting-email-confirmation',
                    View::FIRST_TIME_PROFILE => '/profile-first-edit',
                    View::FIRST_TIME_PROJECT => '/dashboard-first',
                    View::PROJECT_OWNER => '/dashboard',
                    View::SPONSOR => '/dashboard',
                    View::FIRST_TIME_ORGANISATION_SPONSOR => '/organisation-edit',
                ),
                'object' => 'Exposure\View\RootViews',
                'method' => 'index',
            ),
            'dashboard-first' => array(
                'object' => 'Exposure\View\UserViews',
                'method' => 'first'
            ),
            'dashboard' => array(
                'object' => 'Exposure\View\UserViews',
                'method' => 'index'
            ),


            /* 
              sign-in/-up/-out pages
            */

            'sign-up' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'signUp',
                'routes' => array(
                    View::ALREADY_SIGNED_IN => '/',
                ),
            ),
            'sign-in' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'signIn',
                'routes' => array(
                    View::ALREADY_SIGNED_IN => '/',
                ),
            ),
            'lost-password' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'lostPassword',
                'routes' => array(
                    View::ALREADY_SIGNED_IN => '/',
                ),
            ),
            'awaiting-password-reset' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'awaitingPasswordReset',
                'routes' => array(
                    View::ALREADY_SIGNED_IN => '/',
                    View::NO_PASSWORD_RESET_REQUEST => '/',
                ),
            ),
            'user-created' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'userCreated',
                'routes' => array(
                    View::NOT_A_NEW_USER => '/',
                ),
            ),
            'awaiting-email-confirmation' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'awaitingEmailConfirmation',
                'routes' => array(
                    View::NOT_REGISTERED => '/',
                    View::NOT_SIGNED_IN => '/',
                    View::ALREADY_VALIDATED => '/',
                    View::NONEXISTENT_USER => '/',
                ),
            ),
            'reset-password' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'resetPassword',
                'routes' => array(
                    View::INVALID_PARAMS => '/',
                    View::NONEXISTENT_USER => '/',
                    View::INCORRECT_CODE => '/',
                    View::ALREADY_RESET => '/',
                    View::SUCCESS => '/set-new-password',
                ),
            ),
            'set-new-password' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'setNewPassword',
                'routes' => array(
                    View::NO_PASSWORD_RESET_TOKEN => '/',
                    View::NOT_SIGNED_IN => '/',
                ),
            ),
            'confirm-email' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'confirmEmail',
                'routes' => array(
                    View::INVALID_PARAMS => '/',
                    View::NONEXISTENT_USER => '/',
                    View::INCORRECT_CODE => '/',
                    View::ALREADY_VALIDATED => '/',
                    View::SUCCESS => '/email-confirmed',
                ),
            ),
            'email-confirmed' => array(
                'object' => 'Exposure\View\AccessViews',
                'method' => 'emailConfirmed',
                'routes' => array(
                    View::NO_EMAIL_TO_CONFIRM => '/',
                ),
            ),


            /*
              profile management
            */

            'profile-edit' => array(
                'object' => 'Exposure\View\ProfileViews',
                'method' => 'edit'
            ),
            'password-change' => array(
                'object' => 'Exposure\View\ProfileViews',
                'method' => 'passwordChange'
            ),
            'profile-first-edit' => array(
                'object' => 'Exposure\View\ProfileViews',
                'method' => 'firstEdit'
            ),
            'profile-photo' => array(
                'object' => 'Exposure\View\ProfileViews',
                'method' => 'photo',
                'raw_render' => true,
            ),
            'profile' => array(
                'object' => 'Exposure\View\ProfileViews',
                'method' => 'view'
            ),
            'preferences-edit' => array(
                'object' => 'Exposure\View\ProfileViews',
                'method' => 'preferencesEdit'
            ),


            /*
              utility
            */

            'place' => array(
                'routes' => array(
                    View::INVALID_PARAMS => null,
                ),
                'object' => 'Exposure\View\PlaceViews',
                'method' => 'place'
            ),
            'theme' => array(
                'object' => 'Exposure\View\ThemeViews',
                'method' => 'theme'
            ),
            'content' => array(
                'object' => 'Exposure\View\ContentViews',
                'method' => 'index'
            ),


            /*
              project pages
            */

            'project-edit' => array(
                'object' => 'Exposure\View\ProjectViews',
                'method' => 'edit'
            ),
            'project' => array(
                'object' => 'Exposure\View\ProjectViews',
                'method' => 'view'
            ),
            'projects' => array(
                'object' => 'Exposure\View\ProjectViews',
                'method' => 'projectsView'
            ),
            'project-photo' => array(
                'object' => 'Exposure\View\ProjectViews',
                'method' => 'photo',
                'raw_render' => true,
            ),
            'project-list-items' => array(
                'object' => 'Exposure\View\ProjectViews',
                'method' => 'listItems',
                'raw_render' => true,
            ),
            'project-list' => array(
                'object' => 'Exposure\View\ProjectViews',
                'method' => 'projectList',
            ),
            'wanted-projects' => array(
                'object' => 'Exposure\View\ProjectViews',
                'method' => 'wantedProjects'
            ),
            'bookmarked-projects' => array(
                'object' => 'Exposure\View\ProjectViews',
                'method' => 'bookmarkedProjects'
            ),


            /*
              admin
            */

            'admin-sign-in' => array(
                'object' => 'Exposure\View\AdminViews',
                'method' => 'signIn',
                'routes' => array(
                    View::ALREADY_SIGNED_IN => '/admin',
                ),
            ),
            'admin' => array(
                'object' => 'Exposure\View\AdminViews',
                'method' => 'index',
                'routes' => array(
                    View::NOT_SIGNED_IN => '/admin-sign-in',
                ),
            ),
            'admin-notifications' => array(
                'object' => 'Exposure\View\AdminViews',
                'method' => 'notifications',
                'routes' => array(
                    View::NOT_SIGNED_IN => '/admin-sign-in',
                ),
            ),
            'admin-profile-moderate' => array(
                'object' => 'Exposure\View\AdminViews',
                'method' => 'profileModerate',
                'routes' => array(
                    View::NOT_SIGNED_IN => '/admin-sign-in',
                ),
            ),
            'admin-project-moderate' => array(
                'object' => 'Exposure\View\AdminViews',
                'method' => 'projectModerate',
                'routes' => array(
                    View::NOT_SIGNED_IN => '/admin-sign-in',
                ),
            ),


            /*
              needs
            */

            'needs' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'needs',
            ),
            'financial-need-new' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'financialNeedNew'
            ),
            'financial-need-edit' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'financialNeedEdit'
            ),
            'financial-need-list-items' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'financialNeedListItems',
                'raw_render' => true,
            ),
            'financial-need-by-amount-new' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'financialNeedByAmountNew'
            ),
            'financial-need-by-amount-edit' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'financialNeedByAmountEdit'
            ),            
            'non-financial-need-new' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'nonFinancialNeedNew'
            ),
            'non-financial-need-edit' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'nonFinancialNeedEdit'
            ),
            'non-financial-needs-list-items' => array(
                'object' => 'Exposure\View\NeedViews',
                'method' => 'nonFinancialNeedsListItems',
                'raw_render' => true,
            ),


            /*
              returns
            */

            'return' => array(
                'object' => 'Exposure\View\ReturnViews',
                'method' => 'view'
            ),
            'return-new' => array(
                'object' => 'Exposure\View\ReturnViews',
                'method' => 'returnNew'
            ),
            'return-edit' => array(
                'object' => 'Exposure\View\ReturnViews',
                'method' => 'returnEdit'
            ),


            /*
              organisation
            */

            'organisation-edit' => array(
                'object' => 'Exposure\View\OrganisationViews',
                'method' => 'edit'
            ),
            'organisation' => array(
                'object' => 'Exposure\View\OrganisationViews',
                'method' => 'view'
            ),
            'organisation-logo' => array(
                'object' => 'Exposure\View\OrganisationViews',
                'method' => 'logo',
                'raw_render' => true,
            ),
            'organisations' => array(
                'object' => 'Exposure\View\OrganisationViews',
                'method' => 'organisationsView'
            ),


            /*
              contribution
            */
            'contributions' => array(
                'object' => 'Exposure\View\ContributionViews',
                'method' => 'contributionsView'
            ),
            'contribution' => array(
                'object' => 'Exposure\View\ContributionViews',
                'method' => 'view'
            ),


            /*
              notifications
            */

            'notifications' => array(
                'object' => 'Exposure\View\NotificationViews',
                'method' => 'notifications'
            ),


            /* 
              returns
            */

            'returns' => array(
                'object' => 'Exposure\View\ReturnViews',
                'method' => 'returnsView'
            ),
            'return' => array(
                'object' => 'Exposure\View\ReturnViews',
                'method' => 'view'
            ),


            /*
              subscriptions
            */

            'subscriptions' => array(
                'object' => 'Exposure\View\SubscriptionViews',
                'method' => 'subscriptionsView'
            ),
            'subscribe' => array(
                'object' => 'Exposure\View\SubscriptionViews',
                'method' => 'subscribe'
            ),
            'billing-address-edit' => array(
                'object' => 'Exposure\View\SubscriptionViews',
                'method' => 'billingAddressEdit'
            ),
            'subscription-history' => array(
                'object' => 'Exposure\View\SubscriptionViews',
                'method' => 'subscriptionHistory'
            ),
            'subscription-order' => array(
                'object' => 'Exposure\View\SubscriptionViews',
                'method' => 'subscriptionOrder'
            ),
            'subscription-order-confirmed' => array(
                'object' => 'Exposure\View\SubscriptionViews',
                'method' => 'subscriptionOrderConfirmed'
            ),
            'subscription-order-failed' => array(
                'object' => 'Exposure\View\SubscriptionViews',
                'method' => 'subscriptionOrderFailed'
            ),


            /*
              default routes
            */

            'default' => array(
                'routes' => array(
                    View::INVALID_PARAMS => '/',
                    View::NOT_AUTHORISED => '/',
                    View::NOT_SIGNED_IN => '/sign-in',
                    View::MAINTENANCE => '/maintenance.html',
                )
            ),
        );
    }
}

GetController::initActions();


