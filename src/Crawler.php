<?php

namespace Pilipinews\Website\Rappler;

use Pilipinews\Common\Client;
use Pilipinews\Common\Crawler as DomCrawler;
use Pilipinews\Common\Interfaces\CrawlerInterface;

/**
 * Rappler News Crawler
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Crawler implements CrawlerInterface
{
    /**
     * @var string[]
     */
    protected $excluded = array('IN PHOTOS', 'LIVE', 'WATCH', 'LOOK', 'Rappler Talk', 'PANOORIN');

    /**
     * @var string
     */
    protected $link = 'https://rappler.com/section/nation';

    /**
     * @var string
     */
    protected $pattern = '.A__DefaultLink-sc-120nwt8-0.eqXhhw';

    /**
     * Returns an array of articles to scrape.
     *
     * @return string[]
     */
    public function crawl()
    {
        $base = 'https://rappler.com';

        $excluded = $this->excluded;

        $excluded = function ($text) use ($excluded)
        {
            preg_match('/(.*):(.*)/i', $text, $matches);

            $keyword = isset($matches[1]) ? $matches[1] : null;

            return in_array($keyword, (array) $excluded);
        };

        $callback = function (DomCrawler $node) use ($base, $excluded)
        {
            $items = explode('/', $link = $node->attr('href'));

            $allowed = $items[1] === 'nation' && ! $excluded($node->text());

            return $allowed ? $base . $node->attr('href') : null;
        };

        $crawler = new DomCrawler(Client::request($this->link));

        $news = $crawler->filter((string) $this->pattern);

        $filtered = array_filter($news->each($callback));

        $reversed = array_reverse($filtered);

        return array_values(array_unique($filtered));
    }
}
