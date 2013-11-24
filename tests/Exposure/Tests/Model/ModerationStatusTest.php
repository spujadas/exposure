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

use Exposure\Model\ModerationStatus,
    Exposure\Model\ModerationReason,
    Sociable\Utility\StringValidator;

class ModerationStatusTest extends \PHPUnit_Framework_TestCase {

    protected $moderationStatus;
    const STATUS = ModerationStatus::STATUS_APPROVED;

    const REASON_CODE = 's';
    
    protected $comment = 'comment';
    
    public function setUp() {
        $this->moderationStatus = new ModerationStatus();
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ModerationStatus', $this->moderationStatus);
    }

    public function testSetReasonCode_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->moderationStatus->setReasonCode(array());
    }
    
    public function testGetReasonCode_notastring() {
        try {
            $this->moderationStatus->setReasonCode(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationStatus->getReasonCode());
    }
    
    public function testSetReasonCode_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->moderationStatus->setReasonCode('');
    }
    
    public function testGetReasonCode_empty() {
        try {
            $this->moderationStatus->setReasonCode('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationStatus->getReasonCode());
    }
    
    public function testSetReasonCode_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->moderationStatus->setReasonCode(str_repeat('a', ModerationReason::CODE_MAX_LENGTH + 1));
    }
    
    public function testGetReasonCode_toolong() {
        try {
            $this->moderationStatus->setReasonCode(str_repeat('a', ModerationReason::CODE_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationStatus->getReasonCode());
    }

    public function testSetGetReasonCode() {
        $this->assertNull($this->moderationStatus->setReasonCode(null));
        $this->assertNull($this->moderationStatus->getReasonCode());
        $this->assertEquals(self::REASON_CODE, $this->moderationStatus->setReasonCode(self::REASON_CODE));
        $this->assertEquals(self::REASON_CODE, $this->moderationStatus->getReasonCode());
    }
    
    public function testSetComment_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->moderationStatus->setComment(str_repeat('a', ModerationStatus::COMMENT_MAX_LENGTH + 1));
    }
    
    public function testGetComment_toolong() {
        try {
            $this->moderationStatus->setComment(str_repeat('a', ModerationStatus::COMMENT_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationStatus->getComment());
    }
    
    public function testSetGetComment() {
        $this->assertNull($this->moderationStatus->setComment(null));
        $this->assertNull($this->moderationStatus->getComment());
        $this->assertEquals($this->comment, 
                $this->moderationStatus->setComment($this->comment));
        $this->assertEquals($this->comment, $this->moderationStatus->getComment());
    }
    
    public function testSetStatus_invalidstatus() {
        $this->setExpectedException('Exposure\Model\ModerationStatusException', 
            ModerationStatus::EXCEPTION_INVALID_STATUS);
        $this->moderationStatus->setStatus(null);
    }
    
    public function testGetStatus_invalidstatus() {
        try {
            $this->moderationStatus->setStatus(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->moderationStatus->getStatus());
    }
    
    public function testSetGetStatus() {
        $this->assertEquals(self::STATUS, $this->moderationStatus->setStatus(self::STATUS));
        $this->assertEquals(self::STATUS, $this->moderationStatus->getStatus());
    }
    
    public function testValidate_uninitialised() {
        $this->setExpectedException('Exposure\Model\ModerationStatusException', 
            ModerationStatus::EXCEPTION_INVALID_STATUS);
        $this->moderationStatus->validate();
    }
    
    public function testValidate() {
        $this->assertEquals(self::REASON_CODE, $this->moderationStatus->setReasonCode(self::REASON_CODE));
        $this->assertEquals(self::STATUS, $this->moderationStatus->setStatus(self::STATUS));
        $this->moderationStatus->validate();
        $this->assertEquals($this->comment, 
                $this->moderationStatus->setComment($this->comment));
        $this->moderationStatus->validate();
    }

    public function tearDown() {
        unset($this->moderationStatus);
    }

}

?>
