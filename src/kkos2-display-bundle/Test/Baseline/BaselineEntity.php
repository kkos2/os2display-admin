<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Test\Baseline;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class BaselineEntity
{
  protected $entityManager;

  protected $userManager;

  protected $adminUser;

  public function __construct(
    EntityManagerInterface $entityManager,
    UserManagerInterface $userManager
  ) {
    $this->entityManager = $entityManager;
    $this->userManager = $userManager;
    $this->adminUser = $this->userManager->findUserByUsername('admin');
  }

}
