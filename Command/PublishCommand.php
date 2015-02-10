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
    protected function configure()
    {
        $this
            ->setName('publish')
            ->setDescription('Publish to production. Production being whatever is aliased as zgphp in .ssh/config')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->getApplication()->getKernel()->getEnvironment() !== 'prod') {
            throw new \ErrorException('Run in production mode!');
        }

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

        $shellCommand = new \mikehaertl\shellcommand\Command('rsync -rv ' . $outputDir . DIRECTORY_SEPARATOR . '* zgphp:www/');
        if ($shellCommand->execute()) {
            $output->writeln($shellCommand->getOutput());
        } else {
            $output->writeln($shellCommand->getError());
            $output->writeln($shellCommand->getCommand());
        }
    }
}