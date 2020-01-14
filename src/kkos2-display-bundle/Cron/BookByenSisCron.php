<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Cron;

use GuzzleHttp\Exception\GuzzleException;
use Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\DataFetcher;
use Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\MultisiteCrawler;
use Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\MultisiteHelper;
use Psr\Log\LoggerInterface;
use Reload\Os2DisplaySlideTools\Events\SlidesInSlideEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BookByenSisCron
 *
 * @package Kkos2\KkOs2DisplayIntegrationBundle\Cron
 */
class BookByenSisCron implements EventSubscriberInterface {

  /**
   * @var \Symfony\Bridge\Monolog\Logger $logger
   */
  private $logger;

  /**
   * @var \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\DataFetcher
   */
  private $fetcher;

  /**
   * BookByenSisCron constructor.
   *
   * @param \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\DataFetcher $fetcher
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function __construct(DataFetcher $fetcher, LoggerInterface $logger) {
    $this->fetcher = $fetcher;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'os2displayslidetools.sis_cron.kk_bookbyen_sis_cron' => [
        ['getSlideData'],
      ],
    ];
  }

  /**
   * Get data for event.
   *
   * @param \Reload\Os2DisplaySlideTools\Events\SlidesInSlideEvent $event
   */
  public function getSlideData(SlidesInSlideEvent $event) {
    $slide = $event->getSlidesInSlide();
    // Enforce only one slide pr. subslide.
    $slide->setOption('sis_items_pr_slide', 1);
    $url = $slide->getOption('datafeed_url', '');
    $numItems = $slide->getOption('sis_total_items', 12);

    // Note that the names of the fields here should match the fields in useFIelds. TODO.
    $dummy = array_fill(0, 12, [
      'time' => '10:00 - 11:00',
      'username' => 'Jens Jensen',
      'facility' => 'Badminton Bane 2',
      'activity' => 'Badminton',
      'note' => 'Inges pensionisthold',
      'team' => 'Begynder',
      'teamleaders' => 'holdleder/team',
    ]);
    $slide->setSubslides([$dummy, $dummy]);
  }

}
