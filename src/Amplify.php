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

        // registerTwigExtension(), not view->twig->addExtension(). Reading `view->twig` builds the
        // Twig environment here, and plugins are loaded during application bootstrap, so it built
        // Twig before Craft had finished initialising. Craft logs a warning every time that
        // happens, on every web request and every console command: about 3,400 lines a day on a
        // site serving 2,000 pageviews a month, which buried real errors in the log.
        //
        // registerTwigExtension() only stores the extension and hands it to Twig when Twig is
        // actually created, so nothing is built early and the behaviour is identical. It exists in
        // Craft 3, 4 and 5, so this needs no change to the version constraint.
        self::$plugin->view->registerTwigExtension(new TwigExtensions());

        \Craft::info(
            \Craft::t('amplify', '{name} plugin loaded', [
                'name' => $this->name
            ]),
            __METHOD__
        );
    }
}
