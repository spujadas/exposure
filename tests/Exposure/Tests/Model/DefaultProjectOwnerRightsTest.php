<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Tests\Model;

use Exposure\Model\DefaultProjectOwnerRights,
    Sociable\Utility\StringValidator,
    Exposure\Model\ProjectRights,
    Exposure\Model\ViewRights;

class DefaultProjectOwnerRightsTest extends \PHPUnit_Framework_TestCase {
    protected $defaultProjectOwnerRights;
    const LABEL = 'ZZZZZ';
    protected $projectRights;
    protected $viewRights;

    public function setUp() {
        $this->defaultProjectOwnerRights = new DefaultProjectOwnerRights();
        
        $this->projectRights = new ProjectRights();
        $this->viewRights = new ViewRights();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\DefaultProjectOwnerRights', $this->defaultProjectOwnerRights);
    }

    public function testSetLabel_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->defaultProjectOwnerRights->setLabel(null);
    }
    
    public function testGetLabel_notastring() {
        try {
            $this->defaultProjectOwnerRights->setLabel(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->defaultProjectOwnerRights->getLabel());
    }
    
    public function testSetLabel_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->defaultProjectOwnerRights->setLabel('');
    }
    
    public function testGetLabel_empty() {
        try {
            $this->defaultProjectOwnerRights->setLabel('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->defaultProjectOwnerRights->getLabel());
    }
    
    public function testSetLabel_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->defaultProjectOwnerRights->setLabel(str_repeat('a', DefaultProjectOwnerRights::LABEL_MAX_LENGTH + 1));
    }
    
    public function testGetLabel_toolong() {
        try {
            $this->defaultProjectOwnerRights->setLabel(str_repeat('a', DefaultProjectOwnerRights::LABEL_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->defaultProjectOwnerRights->getLabel());
    }

    public function testSetGetLabel() {
        $this->assertEquals(self::LABEL, $this->defaultProjectOwnerRights->setLabel(self::LABEL));
        $this->assertEquals(self::LABEL, $this->defaultProjectOwnerRights->getLabel());
    }

    public function testSetGetProjectRights() {
        $this->defaultProjectOwnerRights->setProjectRights($this->projectRights);
        $this->assertEquals($this->projectRights, $this->defaultProjectOwnerRights->getProjectRights());
    }
    
    public function testSetGetViewRights() {
        $this->defaultProjectOwnerRights->setViewRights($this->viewRights);
        $this->assertEquals($this->viewRights, $this->defaultProjectOwnerRights->getViewRights());
    }
    
    public function testValidate_missinglabel() {
        $this->defaultProjectOwnerRights->setViewRights($this->viewRights);
        $this->defaultProjectOwnerRights->setProjectRights($this->projectRights);
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->defaultProjectOwnerRights->validate();
    }
    
    public function testValidate_missingProjectRights() {
        $this->defaultProjectOwnerRights->setLabel(self::LABEL);
        $this->defaultProjectOwnerRights->setViewRights($this->viewRights);
        $this->setExpectedException('Exposure\Model\DefaultProjectOwnerRightsException', 
            DefaultProjectOwnerRights::EXCEPTION_INVALID_PROJECT_RIGHTS);
        $this->defaultProjectOwnerRights->validate();
    }

    public function testValidate_missingViewRights() {
        $this->defaultProjectOwnerRights->setLabel(self::LABEL);
        $this->defaultProjectOwnerRights->setProjectRights($this->projectRights);
        $this->setExpectedException('Exposure\Model\DefaultProjectOwnerRightsException', 
            DefaultProjectOwnerRights::EXCEPTION_INVALID_VIEW_RIGHTS);
        $this->defaultProjectOwnerRights->validate();
    }

    public function testValidate() {
        $this->defaultProjectOwnerRights->setLabel(self::LABEL);
        $this->defaultProjectOwnerRights->setViewRights($this->viewRights);
        $this->defaultProjectOwnerRights->setProjectRights($this->projectRights);
        $this->defaultProjectOwnerRights->validate();
    }

    public function tearDown() {
        unset($this->defaultProjectOwnerRights);
    }

}

?>
