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

  public function getSlideData(SlidesInSlideEvent $event) {
    $slide = $event->getSlidesInSlide();
    $numItems = $slide->getOption('sis_total_items', 12);
    $url = $slide->getOption('datafeed_url', '');
    $query = [];
    $filterDisplay = $slide->getOption('datafeed_display', '');
    if (!empty($filterDisplay)) {
      $query = [
        'display' => $filterDisplay,
      ];
    }

    $data = $this->eventfeedHelper->fetchData($url, $numItems, $query);

    $events = array_map([$this, 'processEvents'], $data);

    // TODO. Should filter from/on more item because the fetcher cuts the items. Rewrite that.
    // Also get the place on the events from "field_os2display_institution" or whatever that field
    // is called on the day.
    $filterOnPlace = $slide->getOption('datafeed_filter_place', false);
    if ($filterOnPlace) {
      $events = $this->filterOnPlace($events, $filterOnPlace);
    }

    $slide->setSubslides($events);
  }

  private function processEvents($data) {
    $expectedFields = [
      'startdate',
      'title',
      'field_teaser',
      'field_image',
      'time',
    ];

    if (!$this->eventfeedHelper->hasRequiredFields($expectedFields, $data)) {
      return [];
    }

    $event = [
      'title' => html_entity_decode($data['title']),
      'body' => html_entity_decode($data['field_teaser']),
      'image' => $this->eventfeedHelper->processImage($data['field_image']),
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
   * @param array $events
   *   Events to filter.
   * @param $placeName
   *   The name of the place we want the events for.
   *
   * @return array
   */
  private function filterOnPlace($events, $placeName) {
    $filtered = array_filter($events, function($item) use ($placeName) {
      return !empty($item['place']) && ($item['place'] == $placeName);
    });
    // Return array values to make sure the array is keyed sequentially.
    return array_values($filtered);
  }

}
