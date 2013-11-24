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

use Exposure\Model\Theme,
    Exposure\ODM\ObjectDocumentMapper;

use \Doctrine\ODM\MongoDB\DocumentManager;

class ThemeViews extends View {
    public function theme() {
        if (count($this->request) > 1) {
            $themeData = FormHelpers::getFormDataForThemeLabel($this->config->getDocumentManager(), $this->request[1]);
        }
        else {
            $themeData = FormHelpers::getFormDataForRootTheme($this->config->getDocumentManager());
        }
    	$this->loadTemplate('theme.inc.tpl.html');
        $this->displayTemplate(array(
            
            'themetree' => $themeData,
        ));
    }
}

