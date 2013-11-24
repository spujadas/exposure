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

class ContentViews extends View {
    protected $content = null;

    protected function indexPreRoute() {
        // no parameter => go home
        if (!isset($this->request[1])) {
            return self::INVALID_PARAMS;
        }

        try {
            $this->content = $this->config->getTwig()->loadTemplate('content/'.$this->request[1].'.tpl.html');
        }
        catch (\Exception $e) {
            return self::INVALID_PARAMS;
            exit;
        }
    }

    public function index() {
        if ($preRouting = $this->indexPreRoute()) {
            return $preRouting;
        }

        $this->loadTemplate('content.tpl.html');
        $this->displayTemplate(array(
            'content' => $this->content,
        ));
    }
}


