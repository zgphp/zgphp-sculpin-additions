<?php

namespace Zgphp\Sculpin\Bundle\ZgphpSculpinAdditionsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateNewsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create:news')
            ->setDescription('Create new news item in _news ')
            ->addArgument(
                'title',
                InputArgument::REQUIRED,
                'News item title, required'
            )
            ->addOption(
                'date',
                null,
                InputOption::VALUE_OPTIONAL,
                'News item date. Use whatever PHP DateTime parser understands. For more info @see http://php.net/manual/en/datetime.formats.php'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceDir = $this->getApplication()->getKernel()->getContainer()->getParameter('sculpin.source_dir');
        $slugify = $this->getApplication()->getKernel()->getContainer()->get('slugify');

        $title = $input->getArgument('title');

        if ($input->getOption('date')) {
            $date = new \DateTime($input->getOption('date'));
        } else {
            $date = new \DateTime();
        }

        $filename = $slugify->slugify($date->format('Y-m-d') . " " . $title) . ".md";

        file_put_contents(
            $sourceDir . DIRECTORY_SEPARATOR . "_news" . DIRECTORY_SEPARATOR . $filename,
            '---' . PHP_EOL . 'title: ' . $title . PHP_EOL . '---' . PHP_EOL
        );
    }
}