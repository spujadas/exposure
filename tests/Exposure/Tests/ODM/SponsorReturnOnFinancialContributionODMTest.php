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

use Exposure\Model\SponsorReturnOnFinancialContribution,
    Exposure\Model\Theme,
    Exposure\Model\SponsorReturnType,
    Sociable\ODM\ObjectDocumentMapper,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\MultiCurrencyValue;

class SponsorReturnOnFinancialContributionODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $sponsorReturnOnFinancialContribution;
    protected static $id = null;
    
    protected $theme;
    const THEME_LABEL = 'ZZZZZ';
    const THEME_NAME_STRING = 'ZZZZZ';
    const THEME_NAME_LANGUAGE = 'fr';
    protected $themeName;
    
    protected $amount;
    
    protected $description;
    const DESCRIPTION_STRING = 'description';
    const DESCRIPTION_LANGUAGE = 'fr';
    
    protected $type;
    const TYPE_LABEL = 'ZZZZZZ_type_label';
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->sponsorReturnOnFinancialContribution = new SponsorReturnOnFinancialContribution;
        
        $this->theme = new Theme;
        $this->theme->setLabel(self::THEME_LABEL);
        $this->themeName = new MultiLanguageString(self::THEME_NAME_STRING, 
                self::THEME_NAME_LANGUAGE);
        $this->theme->setName($this->themeName);
        
        $this->amount = new MultiCurrencyValue(10, 'EUR');
        
        $this->description = new MultiLanguageString(self::DESCRIPTION_STRING,
                self::DESCRIPTION_LANGUAGE);
        
        $this->type = new SponsorReturnType;
        $this->type->setLabel(self::TYPE_LABEL);
        
        $this->sponsorReturnOnFinancialContribution->setTheme($this->theme);
        $this->sponsorReturnOnFinancialContribution->setAmount($this->amount);
        $this->sponsorReturnOnFinancialContribution->setDescription($this->description);
        $this->sponsorReturnOnFinancialContribution->setType($this->type);
        $this->sponsorReturnOnFinancialContribution->validate();

        self::$dm->persist($this->sponsorReturnOnFinancialContribution);
        self::$dm->persist($this->theme);
        self::$dm->persist($this->type);
        self::$dm->flush();
        
        self::$id = $this->sponsorReturnOnFinancialContribution->getId();
    
        self::$dm->clear();
    }

    public function testFound() {
        $this->sponsorReturnOnFinancialContribution = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnOnFinancialContribution', self::$id);
        $this->assertNotNull($this->sponsorReturnOnFinancialContribution);
        $this->assertInstanceOf('Exposure\Model\SponsorReturnOnFinancialContribution', $this->sponsorReturnOnFinancialContribution);
    }
    
    public function testIsValid() {
        $this->sponsorReturnOnFinancialContribution = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnOnFinancialContribution', self::$id);
        $this->sponsorReturnOnFinancialContribution->validate();
    }
   
    public function testIsEqual() {
        $sponsorReturnOnFinancialContribution = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnOnFinancialContribution', self::$id);
        $this->assertEquals(self::THEME_LABEL, $sponsorReturnOnFinancialContribution->getTheme()->getLabel());
        $this->assertEquals($this->theme->getName()
                ->getStringByLanguageCode(self::THEME_NAME_LANGUAGE), 
                $sponsorReturnOnFinancialContribution->getTheme()->getName()
                ->getStringByLanguageCode(self::THEME_NAME_LANGUAGE)
                );
        $this->assertEquals($this->sponsorReturnOnFinancialContribution->getAmount()->getValueByCurrencyCode('EUR'), 
                $sponsorReturnOnFinancialContribution->getAmount()->getValueByCurrencyCode('EUR'));
        $this->assertEquals(self::DESCRIPTION_STRING, $sponsorReturnOnFinancialContribution
                ->getDescription()->getStringByLanguageCode(self::DESCRIPTION_LANGUAGE));
        $this->assertEquals(self::TYPE_LABEL, $sponsorReturnOnFinancialContribution->getType()->getLabel());
        $this->assertEquals(self::$id, $sponsorReturnOnFinancialContribution
                ->getTheme()->getSponsorReturnsOnFinancialContribution()[0]->getId());
    }
   
    public function testRemove() {
        $this->sponsorReturnOnFinancialContribution = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnOnFinancialContribution', self::$id);
        self::$dm->remove($this->sponsorReturnOnFinancialContribution);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnOnFinancialContribution', self::$id));
    }
    
    public function tearDown() {
        self::cleanUp();
    }
    
    public static function tearDownAfterClass() {
        self::cleanUp();
    }

    public static function cleanUp() {
        self::$dm->clear();
        if (!is_null(self::$id)) {
            $sponsorReturnOnFinancialContribution = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnOnFinancialContribution', self::$id);
            if(!is_null($sponsorReturnOnFinancialContribution)) {
                self::$dm->remove($sponsorReturnOnFinancialContribution);
            }
        }
        $theme = ObjectDocumentMapper::getByLabel(self::$dm, 
            'Exposure\Model\Theme', self::THEME_LABEL);
        if(!is_null($theme)) {
            self::$dm->remove($theme);
        }
        $type = ObjectDocumentMapper::getByLabel(self::$dm, 'Exposure\Model\SponsorReturnType', self::TYPE_LABEL);
        if(!is_null($type)) {
            self::$dm->remove($type);
        }
        self::$dm->flush();
    }
}

?>
