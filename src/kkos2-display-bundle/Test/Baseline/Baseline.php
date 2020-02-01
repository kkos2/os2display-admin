<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Test\Baseline;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Os2Display\CoreBundle\Entity\Channel;
use Os2Display\CoreBundle\Entity\Screen;
use Os2Display\CoreBundle\Entity\ScreenTemplate;
use Os2Display\CoreBundle\Entity\Slide;

class Baseline
{
  private $entityManager;

  private $userManager;

  private $slides = [];
  private $channels = [];
  private $screens = [];

  public function __construct(
    EntityManagerInterface $entityManager,
    UserManagerInterface $userManager
  ) {
    $this->entityManager = $entityManager;
    $this->userManager = $userManager;
  }

  public function getTemplateByName(string $name)
  {
    return $this->entityManager->getRepository(
      'Os2DisplayCoreBundle:ScreenTemplate'
    )->findOneBy(['id' => $name]);
  }

  public function slideExistsByTitle($title) :bool
  {
    $slide = $this->entityManager->getRepository(
      'Os2DisplayCoreBundle:Slide'
    )->findOneBy(['title' => $title]);
    return !empty($slide);
  }

  public function createSlide(string $machineName, string $title, string $type, array $options) :Slide
  {
    $bsSlide = new BaselineSlide($this->entityManager, $this->userManager);
    $slide = $bsSlide->create($title, $type, $options);
    $this->slides[$machineName] = $slide;
    return $slide;
  }


  public function createChannel(string $machineName, string $title, string $url, array $slides) :Channel
  {
    $bsChannel = new BaselineChannel($this->entityManager, $this->userManager);
    $channel = $bsChannel->create($title, $url, $slides);
    $this->channels[$machineName] = $channel;
    return $channel;
  }


  public function createScreen(string $machineName, string $title, string $url, ScreenTemplate $template, array $channels) :Screen
  {
    $bsScreen = new BaselineScreen($this->entityManager, $this->userManager);
    $screen = $bsScreen->create($title, $url, $template, $channels);
    $this->screens[$machineName] = $screen;
    return $screen;
  }

}
