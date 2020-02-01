<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Test\Baseline;

use Os2Display\CoreBundle\Entity\Channel;
use Os2Display\CoreBundle\Entity\ChannelScreenRegion;
use Os2Display\CoreBundle\Entity\Screen;
use Os2Display\CoreBundle\Entity\ScreenTemplate;
use Os2Display\ScreenBundle\Entity\PublicScreen;

class BaselineScreen extends BaselineEntity
{

  protected function getNewActivationCode()
  {
    do {
      // Pick a random activation code between 0 and 100000000.
      $code = rand(0, 100000000);

      // Test if the activation code already exists in the db.
      $screen = $this->entityManager
        ->getRepository('Os2DisplayCoreBundle:Screen')
        ->findByActivationCode($code);
    } while ($screen != null);

    return $code;
  }

  public function create(string $title, string $url, ScreenTemplate $template, array $channels): Screen
  {
    $screen = new Screen();
    $screen->setCreatedAt(time());
    $screen->setUser($this->adminUser->getId());
    $screen->setTitle($title);
    $screen->setDescription('Baseline testing');
    $screen->setModifiedAt(time());

    $screen->setActivationCode($this->getNewActivationCode());
    $screen->setToken('');
    $screen->setTemplate($template);

    $this->entityManager->persist($screen);
    $this->entityManager->flush();

    $regionCount = 1;
    foreach ($channels as $channel) {
      $csr = new ChannelScreenRegion();
      $csr->setChannel($channel);
      $csr->setScreen($screen);
      $csr->setRegion($regionCount++);
      $this->entityManager->persist($csr);
      $this->entityManager->flush();
    }




    $now = new \DateTime();


    $publicScreen = new PublicScreen();
    $publicScreen->setScreen($screen);
    $publicScreen->setPublicUrl($url);
    $publicScreen->setCreatedAt($now);
    $publicScreen->setUser($this->adminUser);
    $publicScreen->setCreatedBy($this->adminUser);

    $this->entityManager->persist($publicScreen);

    $publicScreen->setUpdatedAt($now);
    $publicScreen->setEnabled(true);
    $publicScreen->setUpdatedBy($this->adminUser->getId());
    $this->entityManager->persist($screen);

    $this->entityManager->flush();

    return $screen;
  }

}
