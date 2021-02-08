<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\ExternalData;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

/**
 * Class MultisiteCrawler.
 */
class MultisiteCrawler {

  /**
   * Get the value of an attribute from a CSS selector.
   *
   * @param string $html
   *   HTML to search for images in.
   * @param string $selector
   *   A CSS selector.
   * @param string $attribute
   *   An HTML element attribute.
   *
   * @return array
   *   Zero or more attribute values.
   */
  public function getAttributeValues($html, $selector, $attribute) {
    $crawler = new Crawler($html);

    $urls = $crawler->filter($selector)
      ->each(static function (Crawler $node, $i) use ($attribute) {
        return $node->attr($attribute) ?: '';
      });

    return array_filter($urls);
  }

  /**
   * Find urls to images.
   *
   * @param string $html
   *   HTML to search for images in.
   * @param string $selector
   *   A CSS selector.
   *
   * @return array
   *   Zero or more image urls.
   */
  public function getImageUrls($html, $selector) {
    return $this->getAttributeValues($html, $selector, 'src');
  }

  /**
   * Extract urls from anchor tags.
   *
   * @param string $html
   *   HTML to search for links in.
   * @param string $selector
   *   A CSS selector.
   *
   * @return array
   *   Zero or more urls.
   */
  public function getLinkHrefs($html, $selector) {
    return $this->getAttributeValues($html, $selector, 'href');
  }

  /**
   * Resolves a list of nodes to their text() equivalents.
   */
  public function getNodeTexts($html, $selector) {
    $crawler = new Crawler($html);

    $texts = $crawler->filter($selector)
      ->each(static function (Crawler $node, $i) {
        return trim($node->text());
      });

    return array_filter($texts);
  }

  /**
   * Get a list of node texts that validates as urls.
   *
   * Will return a list of node texts, filtering away anything that does not
   * validate as a URL.
   */
  public function getNodeUrls($html, $selector) {
    $validator = Validation::createValidator();

    // Use our basic nodeText function, but further filter for valid urls.
    return array_filter(
      $this->getNodeTexts($html, $selector),
      static function ($url) use ($validator) {
        $violations = $validator->validate($url, new Url());

        return $violations->count() === 0;
      }
    );
  }

}
