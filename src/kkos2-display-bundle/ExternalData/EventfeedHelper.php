<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\ExternalData;

use DateTime;
use Kkos2\KkOs2DisplayIntegrationBundle\Slides\DateTrait;
use Psr\Log\LoggerInterface;
use Reload\Os2DisplaySlideTools\Slides\SlidesInSlide;

/**
 * Class EventfeedHelper
 *
 * @package Kkos2\KkOs2DisplayIntegrationBundle\ExternalData
 */
class EventfeedHelper {

  use DateTrait;

  /**
   * @var \Psr\Log\LoggerInterface $logger
   */
  private $logger;

  /**
   * @var \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\MultisiteCrawler
   */
  private $crawler;

  /**
   * @var \Reload\Os2DisplaySlideTools\Slides\SlidesInSlide
   */
  private $slide;

  /**
   * @var string
   */
  private $slideType;

  /**
   * EventfeedHelper constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\MultisiteCrawler $crawler
   */
  public function __construct(LoggerInterface $logger, MultisiteCrawler $crawler) {
    $this->logger = $logger;
    $this->crawler = $crawler;
  }

  public function setSlide(SlidesInSlide $slide, string $slideType) {
    $this->slide = $slide;
    $this->slideType = $slideType;
  }

  /**
   * Fetch data from feeds.
   *
   * Feeds are provieded via the "datafeed_url" option on the slide, it contains
   * zero or more feed urls separated by newlines.
   *
   * @return array
   *   An array of json objects.
   */
  public function fetchData() {
    $datafeed_urls = $this->slide->getOption('datafeed_url', '');
    if (empty($datafeed_urls)) {
      return [];
    }

    // Split the feeds by newline.
    $urls = explode("\n", $datafeed_urls);
    // Get rid of any whitespace surrounding the feed (eg.\r).
    $urls = array_map("trim", $urls);
    // Ensure we don't get any empty feeds, array_filter defaults to removing
    // empty() entries.
    $urls = array_filter($urls);

    // Go trough reach feed, fetch the contents and merge into a common
    // response.
    $feed_data = [];
    foreach ($urls as $feed_url) {
      if (!$this->validateFeedUrl($feed_url)) {
        // Report invalid feed url and continue to the next feed.
        $this->logger->error("$feed_url is not a valid {$this->slideType} url.");
        continue;
      }
      $query = [];
      // Only fetch feed items for the specified display (if specified).
      $filterDisplay = $this->slide->getOption('datafeed_display', '');
      if (!empty($filterDisplay) && is_string($filterDisplay)) {
        $query = [
          'display' => $filterDisplay,
        ];
      }
      if ($this->slideType !== "kk-brugbyen") {
        return [];
      }
      $this->logger->info("Fetching Event feed " . print_r($feed_url, TRUE) . " with query " . print_r($query, TRUE));
      $feed_data = array_merge($feed_data, JsonFetcher::fetch($feed_url, $query));
    }

    return $feed_data;
  }

  /**
   * Split slide data into slices that fits with the requested total items.
   */
  public function sliceData($data) {
    return array_slice($data, 0, $this->slide->getOption('sis_total_items', 12));
  }

  /**
   * Verify that the url we've been passed is for the correct slide type.
   */
  private function validateFeedUrl($url) {
    $endsWith = '';
    switch ($this->slideType) {
      case 'kk-events':
        $endsWith = 'os2display-events';
        break;
        case 'kk-eventplakat':
          $endsWith = 'os2display-posters';
          break;
        case 'kk-brugbyen':
          $endsWith = 'os2display-posters';
          break;
          }
    if (empty($endsWith)) {
      return FALSE;
    }
    return preg_match("@{$endsWith}[?#]?@", $url);
  }

  /**
   * @param mixed $image image data - can be array or string
   *
   * @return string
   */
  public function processImage($image) {
    $image = is_array($image) ? current($image) : $image;
    if (strpos($image, '<img') !== FALSE) {
      $imgUrls = $this->crawler->getImageUrls($image, 'img');
      if (!empty($imgUrls[0])) {
        $image = $imgUrls[0];
      }
    }
    return $image;
  }

  /**
   * Get at formatted date from the date in the feed.
   *
   * @param array $startDate the date from the feed
   *
   * @return string
   */
  public function processDate($startDate) {
    $date = DateTime::createFromFormat('d.m.Y', current($startDate));
    if (!$date) {
      return '';
    }
    return $this->getDayName($date) . ' d. ' . $date->format('j') . '. ' . $this->getMonthName($date);
  }

  /**
   * Get missing field keys if any.
   *
   * @param array $expectedFields fields we want
   * @param array $data array to check for field keys in
   *
   * @return string
   */
  public function getMissingFieldKeys($expectedFields, $data) {
    $missing = array_diff($expectedFields, array_keys($data));
    return implode(', ', $missing);
  }

}
