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

use Exposure\Model\User;

class NotificationViews extends View {
    protected $user = null;

    protected function notificationsPreRoute() {
        return $this->isSignedInOrRedirect();
    }

    public function notifications() {
        if ($preRouting = $this->notificationsPreRoute()) {
            return $preRouting;
        }

        switch ($this->signedInUser->getType()) {
        case User::TYPE_PROJECT_OWNER:
            $this->loadTemplate('notifications-project-owner.tpl.html');
            break;
        case User::TYPE_SPONSOR:
            $this->loadTemplate('notifications-sponsor.tpl.html');
            break;
        default:
            return;
        }

        // render
        $this->displayTemplate(array(
            'user' => $this->signedInUser,
        ));
    }
}