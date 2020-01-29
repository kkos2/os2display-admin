<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Cron;

use Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\EventfeedHelper;
use Psr\Log\LoggerInterface;
use Reload\Os2DisplaySlideTools\Events\SlidesInSlideEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventsSisCron implements EventSubscriberInterface {

  /**
   * @var \Psr\Log\LoggerInterface $logger
   */
  private $logger;

  /**
   * @var \Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\EventfeedHelper
   */
  private $eventfeedHelper;

  public function __construct(LoggerInterface $logger, EventfeedHelper $eventfeedHelper) {
    $this->logger = $logger;
    $this->eventfeedHelper = $eventfeedHelper;
  }

  public static function getSubscribedEvents() {
    return [
      'os2displayslidetools.sis_cron.kk_events_sis_cron' => [
        ['getSlideData'],
      ],
    ];
  }

  // TODO. datafeed_ttl_minutes
  public function getSlideData(SlidesInSlideEvent $event) {
    $slide = $event->getSlidesInSlide();
    $url = $slide->getOption('datafeed_url', '');

    $data_errors = [];

    if (!$this->eventfeedHelper->validateFeedUrl($url, 'os2display-events')) {
      $data_errors['invalid_feed_url'] = true;
      $slide->setOption('data_errors', $data_errors);
      $slide->setSubslides([]);
      return;
    }

    $query = [];
    $filterDisplay = $slide->getOption('datafeed_display', '');
    if (!empty($filterDisplay)) {
      $query = [
        'display' => $filterDisplay,
      ];
    }

    try {
      $data = $this->eventfeedHelper->fetchData($url, $query);
    } catch (\Exception $e) {

    }

    $filterOnPlace = $slide->getOption('datafeed_filter_place', false);
    if ($filterOnPlace) {
      $data = $this->filterOnPlace($data, $filterOnPlace);
    }

    $data = array_slice($data, 0,  $slide->getOption('sis_total_items', 12));

    $events = array_map([$this, 'processEvents'], $data);
    $slide->setSubslides($events);
  }

  private function processEvents($data) {
    $expectedFields = [
      'startdate',
      'title',
      'field_teaser',
      'image',
      'time',
    ];

    if (!$this->eventfeedHelper->hasRequiredFields($expectedFields, $data)) {
      return [];
    }

    $event = [
      'title' => html_entity_decode($data['title']),
      'body' => html_entity_decode($data['field_teaser']),
      'image' => $this->eventfeedHelper->processImage($data['image']),
      'date' => $this->eventfeedHelper->processDate($data['startdate']),
      'time' => current($data['time']),
    ];
    if (!empty($data['field_os2display_free_text_event'])) {
      $event['free_text'] = $data['field_os2display_free_text_event'];
    }

    return array_map('trim', $event);
  }

  /**
   * This is filtering that should have taken place on the feeds end, but we
   * have to do it here.
   *
   * @param array $data
   *   Data to filter.
   * @param $placeName
   *   The name of the place we want the events for.
   *
   * @return array
   */
  public function filterOnPlace($data, $placeName) {
    $filtered = array_filter($data, function($item) use ($placeName) {
      return !empty($item['field_display_institution']) && ($item['field_display_institution'] == $placeName);
    });
    // Return array values to make sure the array is keyed sequentially.
    return array_values($filtered);
  }

}
