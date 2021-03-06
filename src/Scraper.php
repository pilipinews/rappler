<?php

namespace Pilipinews\Website\Rappler;

use Pilipinews\Common\Article;
use Pilipinews\Common\Interfaces\ScraperInterface;
use Pilipinews\Common\Scraper as AbstractScraper;
use Pilipinews\Common\Crawler as DomCrawler;

/**
 * Rappler News Scraper
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Scraper extends AbstractScraper implements ScraperInterface
{
    /**
     * @var string[]
     */
    protected $removables = array('.author-box');

    /**
     * @var string[]
     */
    protected $texts = array(
        "What's the weather like in your area? Report the situation through Rappler's Agos (http://agos.rappler.com/) or tweet us at @rapplerdotcom (https://twitter.com/rapplerdotcom).",
        "Not on the list? Help us crowdsource class suspensions by posting in the comments section or tweeting @rapplerdotcom (https://twitter.com/rapplerdotcom).\n\nFor more information:  (https://www.facebook.com/gov.abet/posts/10152811185356858)When are classes cancelled or suspended? (https://www.rappler.com/move-ph/31299-classes-cancelled-suspended)",
        "\n\nPlease refresh this page for updates."
    );

    /**
     * Returns the contents of an article.
     *
     * @param  string $link
     * @return \Pilipinews\Common\Article
     */
    public function scrape($link)
    {
        $this->prepare(mb_strtolower($link));

        $title = $this->title('h1');

        $this->remove((array) $this->removables);

        $body = $this->body('.ArticleWrapper__ArticleBodyWrapper-sc-36pn73-0');

        $body = $this->image($body);

        $body = $this->scribd($body);

        $body = $this->video($body);

        $body = $this->tweet($body);

        $html = $this->html($body, $this->texts);

        $html = htmlspecialchars_decode($html);

        return new Article($title, $html, $link);
    }

    /**
     * Converts image elements to readable string.
     *
     * @param  \Pilipinews\Common\Crawler $crawler
     * @return \Pilipinews\Common\Crawler
     */
    protected function image(DomCrawler $crawler)
    {
        $callback = function (DomCrawler $crawler, $html)
        {
            $image = $crawler->previousAll()->first();

            $photo = $image->filter('img')->attr('data-original');

            $node = $image->getNode((integer) 0);

            $node->parentNode->removeChild($node);

            if ($text = trim($crawler->first()->text()))
            {
                $text = ' - ' . $text;
            }

            return '<p>PHOTO: ' . $photo . $text . '</p>';
        };

        return $this->replace($crawler, 'p.caption', $callback);
    }

    /**
     * Converts embedded Scribd elements to readable string.
     *
     * @param  \Pilipinews\Common\Crawler $crawler
     * @return \Pilipinews\Common\Crawler
     */
    protected function scribd(DomCrawler $crawler)
    {
        $callback = function (DomCrawler $crawler, $html)
        {
            $title = (string) $crawler->attr('title');

            $link = (string) $crawler->attr('src');

            return '<p>' . $title . ' (' . $link . ')</p>';
        };

        $class = (string) '.scribd_iframe_embed';

        return $this->replace($crawler, $class, $callback);
    }

    /**
     * Converts embedded iframe elements to readable string.
     *
     * @param  \Pilipinews\Common\Crawler $crawler
     * @return \Pilipinews\Common\Crawler
     */
    protected function video(DomCrawler $crawler)
    {
        $callback = function (DomCrawler $crawler, $html)
        {
            return '<p>VIDEO: ' . $crawler->attr('src') . '</p>';
        };

        return $this->replace($crawler, 'iframe', $callback);
    }
}
