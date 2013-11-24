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

use Sociable\Controller\PostActions;

class PostController extends \Sociable\Controller\PostController {
    public static function initActions() {
        self::$actions = array(
            /* 
              access management
            */


            'user_signup' => array(
                'object' => 'Exposure\Controller\AccessPostActions',
                'method' => 'signup',
                'routes' => array(
                    PostActions::INVALID_DATA => '/sign-up',
                    PostActions::ALREADY_SIGNED_IN => '/',
                    PostActions::SUCCESS => '/user-created',
                ),
            ),
            'user_signin' => array(
                'object' => 'Exposure\Controller\AccessPostActions',
                'method' => 'signin',
                'routes' => array(
                    PostActions::INVALID_DATA => '/sign-in',
                    PostActions::ALREADY_SIGNED_IN => '/',
                    PostActions::SUCCESS => '/',
                ),
                'manual_route' => true,
            ),
            'user_signout' => array(
                'object' => 'Exposure\Controller\AccessPostActions',
                'method' => 'signout',
                'routes' => array(
                    PostActions::SUCCESS => '/',
                ),
            ),
            'reset_password' => array(
                'object' => 'Exposure\Controller\AccessPostActions',
                'method' => 'resetPassword',
                'routes' => array(
                    PostActions::INVALID_DATA => '/lost-password',
                    PostActions::ALREADY_SIGNED_IN => '/',
                    PostActions::SUCCESS => '/awaiting-password-reset',
                ),
            ),
            'set_new_password' => array(
                'object' => 'Exposure\Controller\AccessPostActions',
                'method' => 'setNewPassword',
                'routes' => array(
                    PostActions::NO_PASSWORD_RESET_TOKEN => '/',
                    PostActions::INVALID_SESSION => '/',
                    PostActions::INVALID_DATA => '/set-new-password',
                    PostActions::SUCCESS => '/',
                ),
            ),
            'resend_confirmation_email' => array(
                'object' => 'Exposure\Controller\AccessPostActions',
                'method' => 'resendConfirmationEmail',
                'routes' => array(
                    PostActions::ALREADY_VALIDATED => '/',
                    PostActions::NOT_SIGNED_IN => '/sign-in',
                    PostActions::SUCCESS => '/',
                ),
            ),


            /*
              profile management
            */

            'profile_first_save' => array(
                'object' => 'Exposure\Controller\ProfilePostActions',
                'method' => 'firstSave',
                'routes' => array(
                    PostActions::INVALID_DATA => '/profile-first-edit',
                    PostActions::SUCCESS => '/',
                ),
            ),
            'profile_save' => array(
                'object' => 'Exposure\Controller\ProfilePostActions',
                'method' => 'save',
                'routes' => array(
                    PostActions::INVALID_DATA => '/profile-edit',
                    PostActions::SUCCESS => '/profile',
                    PostActions::PROFILE_NOT_EDITABLE => '/profile',
                ),
            ),
            'preferences_save' => array(
                'object' => 'Exposure\Controller\ProfilePostActions',
                'method' => 'preferencesSave',
                'routes' => array(
                    PostActions::INVALID_DATA => '/preferences-edit',
                    PostActions::SUCCESS => '/preferences-edit',
                ),
            ),
            'password_change' => array(
                'object' => 'Exposure\Controller\ProfilePostActions',
                'method' => 'passwordChange',
                'routes' => array(
                    PostActions::INVALID_DATA => '/password-change',
                    PostActions::SUCCESS => '/profile-edit',
                ),
            ),
            'profile_publish' => array(
                'object' => 'Exposure\Controller\ProfilePostActions',
                'method' => 'profilePublish',
                'routes' => array(
                    PostActions::PROFILE_NOT_PUBLISHABLE => '/profile',
                    PostActions::SUCCESS => '/profile',
                ),
            ),


            /*
              project pages
            */

            'project_save' => array(
                'object' => 'Exposure\Controller\ProjectPostActions',
                'method' => 'save',
                'routes' => array(
                    PostActions::INVALID_DATA => '/project-edit',
                    PostActions::SUCCESS => '/project',
                    PostActions::PROJECT_NOT_EDITABLE => '/project',
                ),
            ),
            'project_photo_upsert' => array(
                'object' => 'Exposure\Controller\ProjectPostActions',
                'method' => 'projectPhotoUpsert',
                'manual_route' => true,
            ),
            'project_photo_delete' => array(
                'object' => 'Exposure\Controller\ProjectPostActions',
                'method' => 'projectPhotoDelete',
                'manual_route' => true,
            ),
            'project_publish' => array(
                'object' => 'Exposure\Controller\ProjectPostActions',
                'method' => 'projectPublish',
                'routes' => array(
                    PostActions::PROJECT_NOT_PUBLISHABLE => '/project',
                    PostActions::SUCCESS => '/project',
                ),
            ),
            'project_want' => array(
                'object' => 'Exposure\Controller\ProjectPostActions',
                'method' => 'projectWant',
                'manual_route' => true,
            ),
            'project_bookmark_add' => array(
                'object' => 'Exposure\Controller\ProjectPostActions',
                'method' => 'projectBookmarkAdd',
                'manual_route' => true,
            ),
            'project_bookmark_remove' => array(
                'object' => 'Exposure\Controller\ProjectPostActions',
                'method' => 'projectBookmarkRemove',
                'manual_route' => true,
            ),


            /*
              admin
            */

            'admin_signin' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'signin',
                'routes' => array(
                    PostActions::INVALID_DATA => '/admin-sign-in',
                    PostActions::ALREADY_SIGNED_IN => '/admin',
                    PostActions::SUCCESS => '/admin',
                ),
                'manual_route' => true,
            ),
            'admin_signout' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'signout',
                'routes' => array(
                    PostActions::SUCCESS => '/',
                ),
            ),
            'admin_profile_moderate' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'profileModerate',
                'routes' => array(
                    PostActions::SUCCESS => '/admin',
                ),
            ),
            'admin_notification_mark_read' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'notificationMarkRead',
                'manual_route' => true,
            ),
            'admin_notification_mark_unread' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'notificationMarkUnread',
                'manual_route' => true,
            ),
            'admin_notification_archive' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'notificationArchive',
                'manual_route' => true,
            ),
            'admin_notification_delete' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'notificationDelete',
                'manual_route' => true,
            ),
            'admin_project_moderate_string' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'projectModerateString',
                'manual_route' => true,
            ),
            'admin_project_moderate_photo' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'projectModeratePhoto',
                'manual_route' => true,
            ),
            'admin_project_moderate' => array(
                'object' => 'Exposure\Controller\AdminPostActions',
                'method' => 'projectModerate',
                'routes' => array(
                    PostActions::SUCCESS => '/admin',
                    PostActions::INVALID_DATA => '/admin-project-moderate',
                ),
            ),


            /*
              notifications
            */

            'notification_mark_read' => array(
                'object' => 'Exposure\Controller\NotificationPostActions',
                'method' => 'notificationMarkRead',
                'manual_route' => true,
            ),
            'notification_mark_unread' => array(
                'object' => 'Exposure\Controller\NotificationPostActions',
                'method' => 'notificationMarkUnread',
                'manual_route' => true,
            ),
            'notification_archive' => array(
                'object' => 'Exposure\Controller\NotificationPostActions',
                'method' => 'notificationArchive',
                'manual_route' => true,
            ),
            'notification_delete' => array(
                'object' => 'Exposure\Controller\NotificationPostActions',
                'method' => 'notificationDelete',
                'manual_route' => true,
            ),


            /*
              needs
            */

            'financial_need_create' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'financialNeedCreate',
                'routes' => array(
                    PostActions::SUCCESS => '/needs',
                    PostActions::INVALID_DATA => '/financial-need-new',
                ),
            ),
            'financial_need_save' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'financialNeedSave',
                'routes' => array(
                    PostActions::SUCCESS => '/needs',
                    PostActions::INVALID_DATA => '/financial-need-edit',
                ),
            ),
            'financial_need_by_amount_create' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'financialNeedByAmountCreate',
                'routes' => array(
                    PostActions::SUCCESS => '/needs',
                    PostActions::INVALID_DATA => '/financial-need-by-amount-new',
                ),
            ),
            'financial_need_by_amount_save' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'financialNeedByAmountSave',
                'routes' => array(
                    PostActions::SUCCESS => '/needs',
                    PostActions::INVALID_DATA => '/financial-need-by-amount-edit',
                ),
            ),
            'financial_need_by_amount_delete' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'financialNeedByAmountDelete',
                'manual_route' => true,
            ),
            'non_financial_need_create' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'nonFinancialNeedCreate',
                'routes' => array(
                    PostActions::SUCCESS => '/needs',
                    PostActions::INVALID_DATA => '/non-financial-need-new',
                ),
            ),
            'non_financial_need_save' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'nonFinancialNeedSave',
                'routes' => array(
                    PostActions::SUCCESS => '/needs',
                    PostActions::INVALID_DATA => '/non-financial-need-edit',
                ),
            ),
            'non_financial_need_delete' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'nonFinancialNeedDelete',
                'manual_route' => true,
            ),
            'need_contribute' => array(
                'object' => 'Exposure\Controller\NeedPostActions',
                'method' => 'contribute',
                'manual_route' => true,
            ),


            /*
              returns
            */

            'return_create' => array(
                'object' => 'Exposure\Controller\ReturnPostActions',
                'method' => 'returnCreate',
                'routes' => array(
                    PostActions::SUCCESS => '/return',
                    PostActions::INVALID_DATA => '/return-new',
                ),
            ),
            'return_save' => array(
                'object' => 'Exposure\Controller\ReturnPostActions',
                'method' => 'returnSave',
                'routes' => array(
                    PostActions::SUCCESS => '/return',
                    PostActions::INVALID_DATA => '/return-edit',
                ),
            ),
            'return_start' => array(
                'object' => 'Exposure\Controller\ReturnPostActions',
                'method' => 'returnStart',
                'manual_route' => true,
            ),
            'return_complete' => array(
                'object' => 'Exposure\Controller\ReturnPostActions',
                'method' => 'returnComplete',
                'manual_route' => true,
            ),
            'return_approve' => array(
                'object' => 'Exposure\Controller\ReturnPostActions',
                'method' => 'returnApprove',
                'manual_route' => true,
            ),


            /* 
              organisation
            */
              
            'organisation_save' => array(
                'object' => 'Exposure\Controller\OrganisationPostActions',
                'method' => 'save',
                'routes' => array(
                    PostActions::INVALID_DATA => '/organisation-edit',
                    PostActions::SUCCESS => '/organisation',
                ),
            ),
            'contribution_proposal_approve' => array(
                'object' => 'Exposure\Controller\ContributionPostActions',
                'method' => 'proposalApprove',
                'manual_route' => true,
            ),
            'contribution_mark_sent' => array(
                'object' => 'Exposure\Controller\ContributionPostActions',
                'method' => 'markSent',
                'manual_route' => true,
            ),
            'contribution_mark_received' => array(
                'object' => 'Exposure\Controller\ContributionPostActions',
                'method' => 'markReceived',
                'manual_route' => true,
            ),


            /*
              subscription
            */

            'billing_address_save' => array(
                'object' => 'Exposure\Controller\SubscriptionPostActions',
                'method' => 'billingAddressSave',
                'routes' => array(
                    PostActions::INVALID_DATA => '/billing-address-edit',
                    PostActions::SUCCESS => '/subscription-order',
                ),
            ),
            'subscription_pay' => array(
                'object' => 'Exposure\Controller\SubscriptionPostActions',
                'method' => 'subscriptionPay',
                'routes' => array(
                    PostActions::SUCCESS => '/subscription-order-confirmed',
                ),
            ),


            /*
              default
            */

            'default' => array(
                'routes' => array(
                    PostActions::INVALID_POST => '/',
                    PostActions::NOT_SIGNED_IN => '/',
                    PostActions::NOT_AUTHORISED => '/',
                    PostActions::MAINTENANCE => '/maintenance.html',
                )
            )
        );
    }
}

PostController::initActions();


