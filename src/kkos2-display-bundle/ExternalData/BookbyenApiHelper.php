<?php


namespace Kkos2\KkOs2DisplayIntegrationBundle\ExternalData;


use DateTime;
use DateTimeZone;
use Psr\Log\LoggerInterface;

class BookbyenApiHelper {

  /**
   * @var \Psr\Log\LoggerInterface $logger
   */
  private $logger;

  /**
   * BookbyenApiHelper constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * @param string $url Url to API
   *
   * @return array|mixed
   */
  public function fetchData($url) {
    return JsonFetcher::fetch($url);
  }

  public function processTime($start, $end) {
    $timeZone = new DateTimeZone('Europe/Copenhagen');
    $startDate = new DateTime($start, $timeZone);
    $endDate = new DateTime($end, $timeZone);
    return $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
  }

  public function filter($data, $filterSettings) {

    // "Område" in settings interface.
    if (!empty($filterSettings['area'])) {
      $data = array_filter($data, function ($booking) use ($filterSettings) {
        $area = empty($booking['facility']['area']['name']) ? '' : trim($booking['facility']['area']['name']);
        return $area === $filterSettings['area'];
      });
    }

    // "Facilitet" in settings interface.
    if (!empty($filterSettings['facility'])) {
      $data = array_filter($data, function ($booking) use ($filterSettings) {
        $facility = empty($booking['facility']['name']) ? '' : trim($booking['facility']['name']);
        return $facility === $filterSettings['facility'];
      });
    }
    return $data;
  }

  public function getPlaceName($data) {
    return !empty($data[0]['facility']['area']['location']['name']) ? trim($data[0]['facility']['area']['location']['name']) : '';
  }

}
