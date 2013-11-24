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

use Exposure\Model\ApprovableMultiLanguageString,
    Exposure\Model\ApprovableContent,
    Exposure\Model\ModerationStatus,
    Sociable\Model\MultiLanguageString;

class ApprovableMultiLanguageStringTest extends \PHPUnit_Framework_TestCase {

    protected $approvableMultiLanguageString;
    protected $moderationStatus;
    protected $current;
    protected $previous;
    
    const CURRENT_STRING = 'current';
    const PREVIOUS_STRING = 'previous';
    const LANGUAGE_CODE = 'fr';

    public function setUp() {
        $this->approvableMultiLanguageString = new ApprovableMultiLanguageString();
        
        $this->moderationStatus = new ModerationStatus();
        $this->moderationStatus->setReasonCode('code');
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);

        $this->current = new MultiLanguageString(self::CURRENT_STRING, self::LANGUAGE_CODE);
        
        $this->previous = new MultiLanguageString(self::PREVIOUS_STRING, self::LANGUAGE_CODE);
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ApprovableContent', 
                $this->approvableMultiLanguageString);
        $this->assertEquals(ApprovableContent::TYPE_MULTI_LANGUAGE_STRING, 
                $this->approvableMultiLanguageString->getType());
        
        $this->approvableMultiLanguageString = 
            new ApprovableMultiLanguageString(self::CURRENT_STRING, self::LANGUAGE_CODE);
        $multiLanguageString = $this->approvableMultiLanguageString->getCurrent();
        $this->assertEquals(self::LANGUAGE_CODE, $multiLanguageString->getDefaultLanguageCode());
        $this->assertEquals(self::CURRENT_STRING, $multiLanguageString->getStringByLanguageCode(self::LANGUAGE_CODE));
    }
    
    public function testSetGetCurrent() {
        $this->assertEquals($this->current, 
                $this->approvableMultiLanguageString->setCurrent($this->current));
        $this->assertEquals($this->current, 
                $this->approvableMultiLanguageString->getCurrent());
    }

    public function testSetGetPrevious() {
        $this->assertNull($this->approvableMultiLanguageString->setPrevious(null));
        $this->assertNull($this->approvableMultiLanguageString->getPrevious());
        $this->assertEquals($this->previous, 
                $this->approvableMultiLanguageString->setPrevious($this->previous));
        $this->assertEquals($this->previous, 
                $this->approvableMultiLanguageString->getPrevious());
    }
    
    public function testValidate_missingmoderationStatus() {
        $this->approvableMultiLanguageString->setCurrent($this->current);
        $this->setExpectedException('Exposure\Model\ApprovableContentException', 
            ApprovableContent::EXCEPTION_MISSING_STATUS);
        $this->approvableMultiLanguageString->validate();
    }

    public function testValidate_missingcurrent() {
        $this->approvableMultiLanguageString->setModerationStatus($this->moderationStatus);
        $this->setExpectedException('Exposure\Model\ApprovableContentException', 
            ApprovableContent::EXCEPTION_MISSING_CURRENT);
        $this->approvableMultiLanguageString->validate();
    }
    
    public function testValidate() {
        $this->approvableMultiLanguageString->setModerationStatus($this->moderationStatus);
        $this->approvableMultiLanguageString->setCurrent($this->current);
        $this->approvableMultiLanguageString->validate();
        $this->approvableMultiLanguageString->setPrevious($this->previous);
        $this->approvableMultiLanguageString->validate();
    }
    
    public function tearDown() {
        unset($this->approvableMultiLanguageString);
    }

}

?>
