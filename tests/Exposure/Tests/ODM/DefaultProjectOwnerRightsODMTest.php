<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Tests\ODM;

use Exposure\Model\DefaultProjectOwnerRights,
    Exposure\Model\ViewRights,
    Exposure\Model\ProjectRights,
    Sociable\ODM\ObjectDocumentMapper;

class DefaultProjectOwnerRightsODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $defaultProjectOwnerRights;

    const LABEL = 'ZZZZZ';
    protected $projectRights;
    protected $viewRights;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanup();
        
        $this->defaultProjectOwnerRights = new DefaultProjectOwnerRights();
        $this->defaultProjectOwnerRights->setLabel(self::LABEL);
        $this->viewRights = new ViewRights();
        $this->defaultProjectOwnerRights->setViewRights($this->viewRights);
        $this->projectRights = new ProjectRights();
        $this->defaultProjectOwnerRights->setProjectRights($this->projectRights);
        $this->defaultProjectOwnerRights->validate();
        
        self::$dm->persist($this->defaultProjectOwnerRights);
        self::$dm->flush();

        self::$dm->clear();
    }

    public function testFound() {
        $this->defaultProjectOwnerRights = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\DefaultProjectOwnerRights', self::LABEL);
        $this->assertNotNull($this->defaultProjectOwnerRights);
    }
    
    public function testIsValid() {
        $this->defaultProjectOwnerRights = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\DefaultProjectOwnerRights', self::LABEL);
        $this->defaultProjectOwnerRights->validate();
    }
   
    public function testIsEqual() {
        $defaultProjectOwnerRights = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\DefaultProjectOwnerRights', self::LABEL);
        $this->assertEquals($this->defaultProjectOwnerRights->getLabel(), $defaultProjectOwnerRights->getLabel());
        $this->assertEquals($this->projectRights, $this->defaultProjectOwnerRights->getProjectRights());
        $this->assertEquals($this->viewRights, $this->defaultProjectOwnerRights->getViewRights());
    }
   
    public function testRemove() {
        $this->defaultProjectOwnerRights = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\DefaultProjectOwnerRights', self::LABEL);
        self::$dm->remove($this->defaultProjectOwnerRights);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\DefaultProjectOwnerRights', self::LABEL));
    }
    
    public function testDuplicate() {
        $this->defaultProjectOwnerRights = new DefaultProjectOwnerRights();
        $this->defaultProjectOwnerRights->setLabel(self::LABEL);
        $this->defaultProjectOwnerRights->setViewRights($this->viewRights);
        $this->defaultProjectOwnerRights->setProjectRights($this->projectRights);
        $this->defaultProjectOwnerRights->validate();

        self::$dm->persist($this->defaultProjectOwnerRights);
        
        $this->setExpectedException('MongoCursorException');
        self::$dm->flush();
    }

    public function tearDown() {
        self::cleanup();
    }
    
    public static function tearDownAfterClass() {
        self::cleanup();
    }
    
    public static function cleanUp() {
        self::$dm->clear();
        $defaultProjectOwnerRights = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\DefaultProjectOwnerRights', self::LABEL);
        if(!is_null($defaultProjectOwnerRights)) {
            self::$dm->remove($defaultProjectOwnerRights);
            self::$dm->flush();
        }
    }

}

?>
