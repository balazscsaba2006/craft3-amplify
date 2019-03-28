<?php

namespace humandirect\amplify\twig;

use FasterImage\FasterImage;

/**
 * TwigExtensions class
 *
 * @author    Balazs Csaba <csaba.balazs@humandirect.eu>
 * @copyright 2018 Human Direct
 */
class TwigExtensions extends \Twig_Extension
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Amplify';
    }

    /**
     * Makes the filters available to the template context
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('amplify', [$this, 'amplifyFilter']),
        ];
    }

    /**
     * @param string $html
     * @param bool   $smartImages
     *
     * @return mixed|null|string|string[]
     */
    public function amplifyFilter(string $html, bool $smartImages = true)
    {
        $html = str_ireplace(
            ['<video', '/video>', '<audio', '/audio>', '<iframe', '/iframe>'],
            ['<amp-video', '/amp-video>', '<amp-audio', '/amp-audio>', '<amp-iframe', '/amp-iframe>'],
            $html
        );

        $html = $this->amplifyImages($html, $smartImages);
        $html = $this->amplifyIframes($html);

        // Whitelist of HTML tags allowed by AMP
        $html = strip_tags($html, '<h1><h2><h3><h4><h5><h6><a><p><ul><ol><li><blockquote><q><cite><ins><del><strong><em><code><pre><svg><table><thead><tbody><tfoot><th><tr><td><dl><dt><dd><article><section><header><footer><aside><figure><time><abbr><div><span><hr><small><br><amp-img><amp-audio><amp-video><amp-iframe><amp-ad><amp-anim><amp-carousel><amp-fit-rext><amp-image-lightbox><amp-instagram><amp-lightbox><amp-twitter><amp-youtube>');

        // strips out stuff in brackets
        $html = preg_replace('#\s*\[.+\]\s*#U', ' ', $html);

        // removes empty paragraphs
        $pattern = "/<p[^>]*><\\/p[^>]*>/";
        $html = preg_replace($pattern, '', $html);

        return $html;
    }

    /**
     * @param string $html
     *
     * @return mixed|null|string|string[]
     */
    protected function amplifyIframes(string $html)
    {
        // adds layout responsive to iframes that are inline
        $html = preg_replace('/(<amp-iframe\b[^><]*)>/i', '$1 layout="responsive" sandbox="allow-scripts allow-same-origin allow-popups">', $html);

        preg_match('/(<amp-iframe\b[^><]*)>/i', $html, $matches);
        if (!$matches) {
            return $html;
        }

        $match = $matches[1];

        if (preg_match('/src=[\'|"]([^\"]*)[\'|"]/i', $match, $source)) {
            $tmpSrc = $src = $source[1];

            if ($src && $parsed = parse_url($src)) {
                if (array_key_exists('scheme', $parsed) && 'https' !== $parsed['scheme']) {
                    $tmpSrc = str_replace($parsed['scheme'], 'https://', $tmpSrc);
                } else {
                    $tmpSrc = sprintf('https://%s', implode('', $parsed));
                }
                $html = str_replace($src, $tmpSrc, $html);
            }
        }

        if (!preg_match('/width=[\'|"]([^\"]*)[\'|"]/i', $match, $matches)) {
            $html = preg_replace('/(<amp-iframe\b[^><]*)>/i', '$1 width="500">', $html);
        }

        if (!preg_match('/height=[\'|"]([^\"]*)[\'|"]/i', $match, $matches)) {
            $html = preg_replace('/(<amp-iframe\b[^><]*)>/i', '$1 height="281">', $html);
        }

        return $html;
    }

    /**
     * @param string $html
     * @param bool $smartImages
     *
     * @return mixed|null|string|string[]
     */
    protected function amplifyImages(string $html, bool $smartImages)
    {
        $cropKeys = [];
        if ($smartImages) {
            $dom = str_get_html($html);

            foreach ($dom->find('img') as $key => $img) {
                $src = $img->src;
                // remove auto-generated asset transform param
                $src = preg_replace('~(\?|&)x=[^&]*~', '$1', $src);

                // try dimensions from cache
                $cacheKey = hash('crc32', $src);
                if (\Craft::$app->cache->offsetGet($cacheKey)) {
                    $size = \Craft::$app->cache->get($cacheKey);
                    if (!$size) {
                        continue;
                    }

                    [$width, $height] = $size;

                    // no dimensions should remove this image from DOM permanently
                    if (!$width || !$height) {
                        $cropKey = $cacheKey;
                        $img->outertext = 'CROPSTART' . $cropKey . $img->outertext . 'CROPEND' . $cropKey;
                        $cropKeys[] = $cropKey;
                        continue;
                    }

                    $this->setImageSize($img, $width, $height, false);
                    continue;
                }

                // read dimensions from image element
                $displaySize = $img->get_display_size();
                $width = $displaySize['width'];
                $height = $displaySize['height'];

                if ($width > 0 && $height > 0) {
                    $this->setImageSize($img, $width, $height);
                    continue;
                }

                // read dimensions from image resource
                if ($size = $this->readImageSize($src)) {
                    [$width, $height] = $size;

                    $this->setImageSize($img, $width, $height);
                    continue;
                }

                $cropKey = $cacheKey;
                $img->outertext = 'CROPSTART' . $cropKey . $img->outertext . 'CROPEND' . $cropKey;
                $cropKeys[] = $cropKey;

                // cache image dimensions to null, so it will be removed permanently from the DOM
                $this->cacheImageSize($src, null, null);
            }

            $html = $dom->save();
        }

        // delete cropable elements
        foreach ($cropKeys as $cropKey) {
            $html = preg_replace('/CROPSTART' . $cropKey . '[\s\S]+CROPEND' . $cropKey . '/', '', $html);
        }

        // Transform img to amp-img and add closing tags to amp-img custom element
        $html = preg_replace('/<img(.*?)>/', '<amp-img$1></amp-img>', $html);
        $html = str_replace('/></amp-img>', '></amp-img>', $html);

        if (!$smartImages) {
            // adds layout responsive to images that are inline
            $html = preg_replace('/(<amp-img\b[^><]*)>/i', '$1 layout="responsive">', $html);
        }

        return $html;
    }

    /**
     * @param object $img
     * @param int    $width
     * @param int    $height
     * @param bool   $setCache
     *
     * @return object
     */
    private function setImageSize($img, $width, $height, $setCache = true)
    {
        $layout = ($width < 200) ? 'fixed' : 'responsive';
        $img->width = $width;
        $img->height = $height;
        $img->layout = $layout;

        if ($setCache) {
            $this->cacheImageSize($img->src, $width, $height);
        }

        return $img;
    }

    /**
     * @param string $key
     * @param int    $width
     * @param int    $height
     * @param int    $expire | Defaults to 604800 (1 week)
     */
    private function cacheImageSize($key, $width, $height, $expire = 604800): void
    {
        \Craft::$app->cache->set(hash('crc32', $key), [$width, $height], $expire);
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    private function readImageSize(string $url)
    {
        $client = new FasterImage();

        try {
            $result = $client->batch([$url]);
        } catch (\Exception $e) {
            return null;
        }

        return reset($result)['size'];
    }
}
