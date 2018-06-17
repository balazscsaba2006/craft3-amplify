<?php

namespace humandirect\amplify\twig;

use craft\base\Model;
use Codeception\Test\Unit;
use humandirect\amplify\Amplify;

/**
 * Class TwigExtensionsTest
 *
 * @package humandirect\amplify\twig
 */
class TwigExtensionsTest extends Unit
{
    /**
     * @var TwigExtensions
     */
    private $twigExtension;

    /**
     * Set the mapper.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->twigExtension = new TwigExtensions();
    }

    /**
     * @dataProvider provideSimpleHtml
     *
     * @param string $html
     * @param string $expected
     */
    public function testSimpleHtmlWithoutImagesOrIframes(string $html, string $expected)
    {
        $actual = $this->twigExtension->amplifyFilter($html);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function provideSimpleHtml(): array
    {
        return [
            [
                '',
                ''
            ]
        ];
    }
}
