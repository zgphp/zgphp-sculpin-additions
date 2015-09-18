<?php

namespace Zgphp\Sculpin\Bundle\ZgphpSculpinAdditionsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateVideoCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create:video')
            ->setDescription('Create new video item in _videos from Vimeo API')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Vimeo video id.'
            )
            ->addOption(
                'date',
                null,
                InputOption::VALUE_OPTIONAL,
                'Create video item on different date.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceDir = $this->getApplication()->getKernel()->getContainer()->getParameter('sculpin.source_dir');
        $slugify = $this->getApplication()->getKernel()->getContainer()->get('slugify');

        $videoId = $input->getArgument('id');

        $url = 'http://vimeo.com/api/v2/video/';
        $url .= $videoId . '.json';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $json = curl_exec($ch);
        curl_close($ch);

        if ($json === false) {
            throw new \ErrorException("Failed fetching data");
        }

        $data = json_decode($json)[0];
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \ErrorException("Failed decoding json data:" . json_last_error_msg());
        }

        if ($input->getOption('date')) {
            $date = new \DateTime($input->getOption('date'));
        } else {
            $date = new \DateTime($data->upload_date);
        }

        $filename = $slugify->slugify($date->format('Y-m-d') . " " . $data->title) . ".md";

        file_put_contents(
            $sourceDir . DIRECTORY_SEPARATOR . "_videos" . DIRECTORY_SEPARATOR . $filename,
            '---' . PHP_EOL .
            'title: "' . $data->title . '"' . PHP_EOL .
            'vimeo_id: ' . $videoId . PHP_EOL .
            'image: ' . $data->thumbnail_large . PHP_EOL .
            '---' . PHP_EOL .
            $data->description . PHP_EOL
        );
    }
}