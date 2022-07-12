<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\Helper;

use Ryudith\MezzioResponseCache\CacheHandler\CacheHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CliHandlerCache extends Command
{
    private const DELETE_OPERATION = 'DELETE';
    private const CLEAR_OPERATION = 'CLEAR';

    public function __construct (
        private CacheHandlerInterface $cacheHandler
    ) {
        parent::__construct();
    }

    protected function configure () : void 
    {
        $this->addOption('operation', 'o', InputOption::VALUE_REQUIRED, 'Operation to run (clear or delete).');
        $this->addOption('key', 'k', InputOption::VALUE_OPTIONAL, 'Sha1 string cache key.');
        $this->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'URI path cache.');
        $this->addOption('description', 'd', InputOption::VALUE_NONE, 'Print app help description.');
    }

    public function execute (InputInterface $input, OutputInterface $output) : int 
    {
        if ($input->getOption('description'))
        {
            return $this->description($output);
        }

        $operation = $input->getOption('operation');
        if ($operation === null)
        {
            $output->writeln('Option --operation is not specify, try with option --description or -d to description help.');
            return Command::FAILURE;
        }

        $key = $input->getOption('key');
        $path = $input->getOption('path');
        $operation = \strtoupper($operation);
        if ($operation == self::DELETE_OPERATION && ($key !== null || $path !== null))
        {
            if (($key === null && $path === null) || ($key === '' && $path === ''))
            {
                $output->writeln('Operation need either --key or --path as data key, empty or not specify is given.');
                return Command::FAILURE;
            }

            if ($key === null)
            {
                $key = \sha1($path);
            }

            $isSuccess = $this->cacheHandler->deleteCache($key);
            $message = ($isSuccess ? 'Sucess' : 'Failed').' delete cache '.$key;
            $output->writeln($message);

            return $isSuccess ? Command::SUCCESS : Command::FAILURE;
        }
        else if ($operation == self::CLEAR_OPERATION)
        {
            $isSuccess = $this->cacheHandler->clearCache();
            $message = ($isSuccess ? 'Success' : 'Failed').' clear cache';
            $output->writeln($message);

            return $isSuccess ? Command::SUCCESS : Command::FAILURE;
        }

        return $this->description($output);
    }

    private function description (OutputInterface $output) : int
    {
        $output->writeln("Usage : \n\t[command] [options]\n".
            "Where : \n".
            "[command]\n\tCommand that you register on mezzio.\n\n".
            "[options]\n".
            "\t--operation | -o \t : Helper operation to run.\n".
            "\t--key | -k \t\t : String key from php Sha1\n". 
            "\t--path | -p \t\t : String URI path, if --key or -k parameter specify this option will not use.");

        return Command::SUCCESS;
    }
}