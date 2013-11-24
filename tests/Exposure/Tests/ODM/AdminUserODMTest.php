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

use Exposure\Model\AdminUser,
    Exposure\Model\AdminRights,
    Sociable\Model\PasswordAuthenticator,
    Sociable\ODM\ObjectDocumentMapper;

class AdminUserODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $adminUser;

    const NAME = 'Foo';
    const SURNAME = 'Bar';
    const EMAIL = 'zzzzzz@zzzzzz.com';
    protected $authenticator;
    protected $adminRights;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->authenticator = new PasswordAuthenticator();
        $this->authenticator->setParams(array('password' => '1234'));
        
        $this->adminRights = new AdminRights();

        $this->adminUser = new AdminUser();
        $this->adminUser->setName(self::NAME);
        $this->adminUser->setSurname(self::SURNAME);
        $this->adminUser->setEmail(self::EMAIL);
        $this->adminUser->setAuthenticator($this->authenticator);
        $this->adminUser->setAdminRights($this->adminRights);
        $this->adminUser->validate();

        self::$dm->persist($this->adminUser);
        self::$dm->flush();

        self::$dm->clear();
    }

    public function testFound() {
        $this->adminUser = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\AdminUser', self::EMAIL);
        $this->assertNotNull($this->adminUser);
    }
    
    public function testIsValid() {
        $this->adminUser = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\AdminUser', self::EMAIL);
        $this->adminUser->validate();
    }
   
    public function testIsEqual() {
        $adminUser = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\AdminUser', self::EMAIL);
        $this->assertEquals($this->adminUser, $adminUser);
    }
   
    public function testRemove() {
        $this->adminUser = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\AdminUser', self::EMAIL);
        self::$dm->remove($this->adminUser);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\AdminUser', self::EMAIL));
    }
    
    public function testDuplicate() {
        $this->authenticator = new PasswordAuthenticator();
        $this->authenticator->setParams(array('password' => '1234'));

        $this->adminUser = new AdminUser();
        $this->adminUser->setName(self::NAME);
        $this->adminUser->setSurname(self::SURNAME);
        $this->adminUser->setEmail(self::EMAIL);
        $this->adminUser->setAuthenticator($this->authenticator);
        $this->adminUser->setAdminRights($this->adminRights);
        $this->adminUser->validate();

        self::$dm->persist($this->adminUser);

        $this->setExpectedException('MongoCursorException');
        self::$dm->flush();
    }

    public function tearDown() {
        self::cleanUp();
    }
    
    public static function tearDownAfterClass() {
        self::cleanUp();
    }

    public static function cleanUp() {
        self::$dm->clear();
        $adminUser = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\AdminUser', self::EMAIL);
        if(!is_null($adminUser)) {
            self::$dm->remove($adminUser);
            self::$dm->flush();
        }
    }
}

?>
