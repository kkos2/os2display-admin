<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Cron;


use Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\EventfeedHelper;
use Kkos2\KkOs2DisplayIntegrationBundle\ExternalData\JsonFetcher;
use Kkos2\KkOs2DisplayIntegrationBundle\Slides\Mock\MockEventplakatData;
use Kkos2\KkOs2DisplayIntegrationBundle\Slides\PlakatEventFeedData;
use Psr\Log\LoggerInterface;
use Reload\Os2DisplaySlideTools\Events\SlidesInSlideEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BrugByenSisCron implements EventSubscriberInterface {

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
      'os2displayslidetools.sis_cron.kk_brugbyen_sis_cron' => [
        ['getSlideData'],
      ],
    ];
  }

  public function getSlideData(SlidesInSlideEvent $event) {
    $this->logger->info("Fetching BrugByen");
    $slide = $event->getSlidesInSlide();

    // Make sure that only one subslide pr. slide is set. The value is
    // for the user, but the BrugByen slides don't support more than one, so
    // enforce it here.
    $slide->setOption('sis_items_pr_slide', 1);

    // Clear errors before run.
    $slide->setOption('cronfetch_error', '');

    $events = [];
    $cronErrors = [];
    $this->eventfeedHelper->setSlide($slide, 'kk-brugbyen');
    $data = $this->eventfeedHelper->fetchData();

    $data = $this->eventfeedHelper->sliceData($data);
    foreach($data as $eventData) {
      try {
        $events[] = $this->processEvents($eventData);
      } catch (\Exception $e) {
        $this->logger->error("Error while fetching: " . $e->getMessage());
        $cronErrors[] = $e->getMessage();
      }
    }

    $slide->setOption('cronfetch_error', implode("\n", $cronErrors));
    $this->logger->info("Setting BrugByen slide data" . print_r($events, TRUE));
    $slide->setSubslides($events);
  }

  public function processEvents($data) {
    $this->logger->info("Processing BrugByen data " . print_r($data, TRUE));
    $expectedFields = [
      'startdate',
      'title',
      'field_display_institution',
      'billede',
      'time',
    ];

    $missingFields = $this->eventfeedHelper->getMissingFieldKeys($expectedFields, $data);
    if (!empty($missingFields)) {
      throw new \Exception('There were missing fields in feed: ' . $missingFields);
    }

    $event = [
      'title' => html_entity_decode($data['title']),
      'teaser' => html_entity_decode($data['field_teaser']),
      // When "unset" field_display_institution will be an empty array. So, only
      // use it when it is a string.
      'institution' => is_string($data['field_display_institution']) ? html_entity_decode($data['field_display_institution']) : '',
      'image' => $this->eventfeedHelper->processImage($data['billede']),
      'date' => $this->eventfeedHelper->processDate($data['startdate']),
      'time' => str_replace(":", ".", current($data['time'])),
    ];

    return array_map('trim', $event);
  }

}
