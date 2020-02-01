<?php

namespace Kkos2\KkOs2DisplayIntegrationBundle\Command;

use Kkos2\KkOs2DisplayIntegrationBundle\Test\Baseline\Baseline;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;


/*TODO
// Get data with internal names on cron:
// http://admin-nginx/bundles/kkos2displayintegration/templates/slides/kk-articles/preview.png

*/


class SetupBaselineChannelsCommand extends Command
{

  protected static $defaultName = 'kff:baseline-channels';

  private $baseline;

  public function __construct(
    Baseline $baseline
  ) {
    $this->baseline = $baseline;

    parent::__construct();
  }

  protected function configure()
  {
    //    $this
    //      ->setName('kff:baseline-channels')
    //      ->setDescription('Create baseline channels for dev work');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $baselineData = $this->getBaselineData();

    $fullScreenTemplate = $this->baseline->getTemplateByName('full-screen');
    $twoColumnsScreenTemplate = $this->baseline->getTemplateByName('two-columns');
    $twoRowsPortraitScreenTemplate = $this->baseline->getTemplateByName('two-rows-portrait');


    foreach ($baselineData['slides'] as $name => $data) {
      $fullScreenName = "sc-$name-fs";
      $twocolName = "sc-$name-tc";
      $twoRowsName = "sc-$name-tr";

      if (!$this->baseline->slideExistsByTitle($data['title'])) {

        $slide = $this->baseline->createSlide($name, $data['title'], $data['type'], ($data['options'] ?? []));
        $channel = $this->baseline->createChannel($name, "Baseline: $name", "ch-$name", [$slide]);

        $this->baseline->createScreen($fullScreenName, "Baseline full screeen: {$slide->getTitle()}", $fullScreenName, $fullScreenTemplate, [$channel]);
        $this->baseline->createScreen($twocolName, "Baseline two columns: {$slide->getTitle()}", $twocolName,  $twoColumnsScreenTemplate, [$channel, $channel]);
        $this->baseline->createScreen($twoRowsName, "Baseline two rows portrait: {$slide->getTitle()}", $twoRowsName,  $twoRowsPortraitScreenTemplate, [$channel, $channel]);

        $output->writeln("<info>Created '{$data['title']}'</info>");
      }
      else {
        $output->writeln("<comment>Not creating {$data['title']} - it already exists.</comment>");
      }
      $output->writeln("https://admin.kff-os2display.docker/screen/public/$fullScreenName");
      $output->writeln("https://admin.kff-os2display.docker/screen/public/$twocolName");
      $output->writeln("https://admin.kff-os2display.docker/screen/public/$twoRowsName");
    }

 }

  private function getBaselineData()  {
    $baselineDataFile = dirname(__DIR__, 1) . '/Test/Baseline/baseline.yml';
    return Yaml::parseFile($baselineDataFile);
  }

}
