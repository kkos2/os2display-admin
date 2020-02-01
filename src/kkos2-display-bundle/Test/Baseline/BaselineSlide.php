<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Test\Baseline;

use Os2Display\CoreBundle\Entity\Slide;

class BaselineSlide extends BaselineEntity
{

  public function create(string $title, string $type, array $options): Slide
  {
    $template = $this->entityManager->getRepository(
      'Os2DisplayCoreBundle:SlideTemplate'
    )
      ->findOneBy(['id' => $type]);


    $slide = new Slide();
    $slide->setCreatedAt(time());
    $slide->setUser($this->adminUser->getId());

    $mergedOptions = array_replace_recursive($template->getEmptyOptions(), $options);

    $slide->setSlideType($template->getSlideType());
    $slide->setTitle($title);
    $slide->setOrientation($template->getOrientation());
    $slide->setTemplate($template->getId());
    $slide->setOptions($mergedOptions);
    $slide->setMediaType($template->getMediaType());
    $slide->setPublished(true);
    $slide->setModifiedAt(time());
    $this->entityManager->persist($slide);
    $this->entityManager->flush();

    return $slide;
  }

}
