<?php
namespace Git2WP;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cs278\Mktemp;

class SyncCommand extends Command
{
    /**
     * Configure command
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('sync')
            ->setDescription('Sync tags between Git and SVN');
    }
    /**
     * Execute command
     * @param  InputInterface  $input  Input
     * @param  OutputInterface $output Output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tempdir = Mktemp\temporaryDir();

        //Using exec() until we find a good SVN library
        exec('svn checkout https://plugins.svn.wordpress.org/events-manager-osm/ '.$tempdir.'/wp-svn/');

        dump($tempdir);
    }
}
