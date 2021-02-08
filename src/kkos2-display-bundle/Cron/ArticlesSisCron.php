<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Cron;

use Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\DataFetcher;
use Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\MultisiteCrawler;
use Kkos2\KkOs2DisplayIntegrationBundle\Slides\DateTrait;
use Psr\Log\LoggerInterface;
use Reload\Os2DisplaySlideTools\Events\SlidesInSlideEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BookByenSisCron.
 */
class ArticlesSisCron implements EventSubscriberInterface {
  use DateTrait;

  /**
   * Multisite specific functions for extracting data.
   *
   * @var \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\MultisiteCrawler
   */
  private $crawler;

  /**
   * Data fetcher used for getting website data.
   *
   * @var \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\DataFetcher
   */
  private $fetcher;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * ArticlesSisCron constructor.
   *
   * @param \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\DataFetcher $fetcher
   *   Injected fetcher.
   * @param \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\MultisiteCrawler $crawler
   *   Injected crawler.
   * @param \Psr\Log\LoggerInterface $logger
   *   Injected logger.
   */
  public function __construct(DataFetcher $fetcher, MultisiteCrawler $crawler, LoggerInterface $logger) {
    $this->fetcher = $fetcher;
    $this->crawler = $crawler;
    $this->logger = $logger;
  }

  /**
   * Get data for event.
   *
   * @param \Reload\Os2DisplaySlideTools\Events\SlidesInSlideEvent $event
   *   The event that contains data about our slide.
   */
  public function getSlideData(SlidesInSlideEvent $event) {
    $slide = $event->getSlidesInSlide();
    $url = $slide->getOption('url', '');

    // Fetch the aggregation node.
    $articleLinks = [];

    try {
      $articleLinks = $this->fetchArticleLinks($url);
    }
    catch (\Exception $e) {
      $this->logger->error('Unable to fetch article links: ' . $e->getMessage);
      $slide->setOption('cronfetch_error', $e->getMessage());

      return;
    }

    $subslideData = [];

    foreach ($articleLinks as $articleLink) {
      // TODO: skip if data is invalid.
      $fetchedArticle = $this->fetchArticle($articleLink);

      if ($fetchedArticle) {
        $subslideData[] = $fetchedArticle;
      }
    }

    $slide->setSubslides($subslideData);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'os2displayslidetools.sis_cron.kk_articles_sis_cron' => [
        ['getSlideData'],
      ],
    ];
  }

  /**
   * Fetch an article.
   */
  private function fetchArticle($articleLink) {
    $html = $this->fetcher->getBody($articleLink);
    // Pick out some very specific pieces of content, if we can't find the
    // content throw.
    $titles = $this->crawler->getNodeTexts($html, '.title-page h1');
    $images = $this->crawler->getImageUrls($html, '.field-name-field-image img');
    $manchets = $this->crawler->getNodeTexts($html, '.field-name-field-teaser');

    if (empty($titles) || empty($manchets) || empty($images)) {
      $this->logger->warning("Skipping article {$articleLink} as it could not be parsed as a valid multisite article");

      return FALSE;
    }

    return [
      'title' => !empty($titles) ? $titles[0] : '',
      'manchet' => !empty($manchets) ? $manchets[0] : '',
      'image' => !empty($images) ? $images[0] : '',
    ];
  }

  /**
   * Get a list of article links from a table node.
   */
  private function fetchArticleLinks($url) {
    $html = $this->fetcher->getBody($url);

    return $this->crawler->getNodeUrls($html, '.tablefield tr');
  }

}
