<?php

namespace Zgphp\Sculpin\Bundle\ZgphpSculpinAdditionsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class PublishCommand extends Command
{
    protected $configuration;

    protected function configure()
    {
        $this
            ->setName('publish')
            ->setDescription('Publish to production');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->getApplication()->getKernel()->getEnvironment() !== 'prod') {
            throw new \ErrorException('Run in production mode!');
        }

        /**
         * when Sculpin updates to Symfony 2.4 do
         * http://symfony.com/doc/current/cookbook/console/commands_as_services.html
         */
        $this->configuration = $this->getApplication()->getKernel()->getContainer()->get('sculpin.site_configuration');

        $projectDir = $this->getApplication()->getKernel()->getContainer()->getParameter('sculpin.project_dir');

        $outputDir = $projectDir . DIRECTORY_SEPARATOR . 'output_prod';

        $shellCommand = new \mikehaertl\shellcommand\Command('rm -rf ' . $outputDir);
        if ($shellCommand->execute()) {
            $output->writeln($shellCommand->getOutput());
        } else {
            $output->writeln($shellCommand->getError());
        }

        $command = $this->getApplication()->find('generate');
        $input = new ArrayInput(['command' => 'generate', '-e' => 'prod']);

        $command->run($input, $output);

        $destinationServer = $this->configuration->get('destination_server') ? $this->configuration->get('destination_server') : getenv('DESTINATION_SERVER');
        $destinationDirectory = $this->configuration->get('destination_directory') ? $this->configuration->get('destination_directory') : getenv('DESTINATION_DIRECTORY');

        $shellCommand = new \mikehaertl\shellcommand\Command(
            'rsync -rv ' . $outputDir . DIRECTORY_SEPARATOR . '* ' . $destinationServer . ':' . $destinationDirectory
        );

        if ($shellCommand->execute()) {
            $output->writeln($shellCommand->getOutput());
        } else {
            $output->writeln($shellCommand->getError());
            $output->writeln($shellCommand->getCommand());
        }
    }
}