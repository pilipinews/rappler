<?php

namespace Pilipinews\Website\Rappler;

/**
 * Scraper Test
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class ScraperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pilipinews\Common\Interfaces\ScraperInterface
     */
    protected $scraper;

    /**
     * Sets up the scraper instance.
     *
     * @return void
     */
    public function setUp()
    {
        $this->scraper = new Scraper;
    }

    /**
     * Returns an array of content with their URLs.
     *
     * @return string[][]
     */
    public function items()
    {
        $files = glob(__DIR__ . '/Articles/*.txt');

        $items = array();

        foreach ((array) $files as $file)
        {
            $expected = $this->expected($file);

            $url = $this->link((string) $file);

            $items[] = array($expected, $url);
        }

        return (array) $items;
    }

    /**
     * Tests ScraperInterface::scrape.
     *
     * @dataProvider items
     * @param        string $expected
     * @param        string $url
     * @return       void
     */
    public function testScrapeMethod($expected, $url)
    {
        $article = $this->scraper->scrape((string) $url);

        $post = (string) $article->post();

        $result = preg_replace("/[\r\n]+/", "\n", $post);

        $this->assertEquals($expected, (string) $result);
    }

    /**
     * Returns the expected content.
     *
     * @param  string $file
     * @return string
     */
    protected function expected($file)
    {
        $lines = (array) file((string) $file);

        $regex = "/[\r\n]+/";

        array_shift($lines) && array_shift($lines);

        $text = implode("", (array) $lines);

        return preg_replace($regex, "\n", $text);
    }

    /**
     * Converts the filename into a valid URL.
     *
     * @param  string $filename
     * @return string
     */
    protected function link($filename)
    {
        $lines = file($filename);

        return trim($lines[0]);
    }
}
