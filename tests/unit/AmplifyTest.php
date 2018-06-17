<?php

namespace NerdsAndCompany\Schematic;

use Codeception\Test\Unit;
use humandirect\amplify\Amplify;

/**
 * Class AmplifyTest
 *
 * @package NerdsAndCompany\Schematic
 */
class AmplifyTest extends Unit
{
    /**
     * @var Amplify
     */
    private $module;

    /**
     * Set the mapper.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Amplify('amplify');
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * Tests module/plugin instance
     */
    public function testInstance()
    {
        //$this->assertInstanceOf(Amplify::class, $this->module);
    }
}
