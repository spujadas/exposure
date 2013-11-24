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

class UserViews extends View {
    /**************************
        First time dashboard
    */

    protected function firstPreRoute() {
        return $this->isSignedInOrRedirect();
    }

	public function first() {
        if ($preRouting = $this->firstPreRoute()) {
            return $preRouting;
        }
        
        $this->loadTemplate('dashboard-project-owner-first-time.tpl.html');

        // render
        $this->displayTemplate(array(
            'user' => $this->signedInUser,
        ));
    }


    /***********
        Index
    */

    protected function indexPreRoute() {
        return $this->isSignedInOrRedirect();
    }

    public function index() {
        if ($preRouting = $this->indexPreRoute()) {
            return $preRouting;
        }

        switch ($this->signedInUser->getType()) {
        case User::TYPE_PROJECT_OWNER:
            $this->loadTemplate('dashboard-project-owner.tpl.html');
            break;
        case User::TYPE_SPONSOR:
            $this->loadTemplate('dashboard-sponsor.tpl.html');
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
