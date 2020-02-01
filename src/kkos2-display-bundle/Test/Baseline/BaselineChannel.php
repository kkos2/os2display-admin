<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Test\Baseline;


use Os2Display\CoreBundle\Entity\Channel;
use Os2Display\CoreBundle\Entity\ChannelSlideOrder;
use Os2Display\ScreenBundle\Entity\PublicChannel;

class BaselineChannel extends BaselineEntity
{

  public function create(string $title, string $url, array $slides): Channel
  {
    // Create the channel.
    $channel = new Channel();
    $channel->setCreatedAt(time());
    $channel->setUser($this->adminUser->getId());
    $channel->setTitle($title);
    $channel->setModifiedAt(time());
    $this->entityManager->persist($channel);
    $this->entityManager->flush();

    // Set the channel as public to get a url.
    $publicChannel = new PublicChannel();
    $publicChannel->setChannel($channel);
    $publicChannel->setPublicUrl($url);
    $publicChannel->setCreatedAt(new \DateTime());
    $publicChannel->setUser($this->adminUser);
    $publicChannel->setCreatedBy($this->adminUser);

    $this->entityManager->persist($publicChannel);

    $publicChannel->setUpdatedAt(new \DateTime());
    $publicChannel->setEnabled(true);
    $publicChannel->setUpdatedBy($this->adminUser);


    $channel_slide_order = new ChannelSlideOrder();
    $channel_slide_order->setChannel($channel);
    foreach ($slides as $slide) {
      $channel_slide_order->setSlide($slide);
    }
    $this->entityManager->persist($channel_slide_order);


    $channel->addChannelSlideOrder($channel_slide_order);


    $channel_slide_order->setSortOrder(0); // TODO increment for more slides

    $this->entityManager->flush();

    return $channel;
  }

}
