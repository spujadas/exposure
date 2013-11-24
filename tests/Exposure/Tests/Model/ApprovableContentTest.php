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

use Exposure\Model\ApprovableContent,
    Exposure\Model\ModerationStatus;

class ApprovableContentTest extends \PHPUnit_Framework_TestCase {

    protected $approvableContent;
    const TYPE = ApprovableContent::TYPE_LABELLED_IMAGE;
    const TYPE_INVALID = 'rubbish';
    protected $moderationStatus;

    public function setUp() {
        $this->approvableContent = $this->getMockForAbstractClass('Exposure\Model\ApprovableContent');
        
        $this->moderationStatus = new ModerationStatus();
        $this->moderationStatus->setReasonCode('code');
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);

    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ApprovableContent', $this->approvableContent);
    }
    
    public function testSetGetStatus() {
        $this->assertEquals($this->moderationStatus, $this->approvableContent->setModerationStatus($this->moderationStatus));
        $this->assertEquals($this->moderationStatus, $this->approvableContent->getModerationStatus());
    }

    public function tearDown() {
        unset($this->approvableContent);
    }

}

?>
