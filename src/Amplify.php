<?php

namespace humandirect\amplify;

use craft\base\Plugin;
use humandirect\amplify\twig\TwigExtensions;

/**
 * Amplify class
 *
 * @author    Balazs Csaba <csaba.balazs@humandirect.eu>
 * @copyright 2022 Human Direct
 */
class Amplify extends Plugin
{
    /**
     * @var Amplify
     */
    public static Amplify $plugin;

    /**
     * Initialize plugin.
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        self::$plugin->view->twig->addExtension(new TwigExtensions());

        \Craft::info(
            \Craft::t('amplify', '{name} plugin loaded', [
                'name' => $this->name
            ]),
            __METHOD__
        );
    }
}
