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

use Exposure\Model\AdminUser,
    Exposure\Model\AdminRights,
    Exposure\Model\AdminUserException,
    Sociable\Model\PasswordAuthenticator,
    Sociable\Utility\StringValidator;

class AdminUserTest extends \PHPUnit_Framework_TestCase {
    protected $adminUser;

    // from \Sociable\Model\Exposure
    const NAME = 'John';
    const SURNAME = 'Smith';
    const EMAIL = 'foo@bar.com';
    protected $authenticator;
    
    protected $adminRights;
    
    public function setUp() {
        $this->adminUser = new AdminUser();
        
        $this->authenticator = new PasswordAuthenticator();
        $this->authenticator->setParams(array('password' => '1234'));
        
        $this->adminRights = new AdminRights();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\AdminUser', $this->adminUser);
    }
    
    public function testSetGetAdminRights() {
        $this->assertEquals($this->adminRights, 
                $this->adminUser->setAdminRights($this->adminRights));
        $this->assertEquals($this->adminRights, 
                $this->adminUser->getAdminRights());
    }
  
    public function testValidate_uninitialised() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->adminUser->validate();
    }

    public function testValidate_missingadminrights() {
        $this->adminUser->setEmail(self::EMAIL);
        $this->adminUser->setAuthenticator($this->authenticator);
        $this->setExpectedException('Exposure\Model\AdminUserException', 
            AdminUser::EXCEPTION_INVALID_ADMIN_RIGHTS);
        $this->adminUser->validate();
    }
    
    public function testValidate() {
        $this->adminUser->setEmail(self::EMAIL);
        $this->adminUser->setAuthenticator($this->authenticator);
        $this->adminUser->setAdminRights($this->adminRights);
        $this->adminUser->validate();

        $this->adminUser->setName(self::NAME);
        $this->adminUser->setSurname(self::SURNAME);
        $this->adminUser->validate();
    }

    public function tearDown() {
        unset($this->adminUser);
    }

}

?>
