<?php
/**
 * SyncCommand class
 *
 * PHP version 5.6
 *
 * @category Git2WP
 * @package  Git2WP
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL https://www.gnu.org/licenses/gpl.html
 * @link     https://github.com/Rudloff/git2wp
 */
namespace Git2WP;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cs278\Mktemp;
use Gitonomy\Git\Repository;

/**
 * Sync CLI command
 *
 * PHP version 5.6
 *
 * @category Git2WP
 * @package  Git2WP
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL https://www.gnu.org/licenses/gpl.html
 * @link     https://github.com/Rudloff/git2wp
 */
class SyncCommand extends Command
{
    private $output;
    private $tempDir;
    private $repository;
    private $svnDir;

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('sync')
            ->setDescription('Sync tags between Git and SVN')
            ->addArgument(
                'plugin-name',
                InputArgument::REQUIRED,
                'WordPress plugin name'
            );
    }

    private function exportTag($gitTag, $svnTag)
    {
        $this->output->writeln(
            'Exporting <info>'.$gitTag.'</info> to <info>'.$svnTag.'</info>'
        );
        $zipFile = $this->tempDir.'/wp-archive-'.$gitTag.'.zip';
        $this->repository->run('archive', array('-o', $zipFile, $gitTag));
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($this->svnDir.'/'.$svnTag);
            $zip->close();
        }
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->tempDir = Mktemp\temporaryDir();
        $output->writeln('Using temporary folder <info>'.$this->tempDir.'</info>');

        $this->svnDir = $this->tempDir.'/wp-svn/';
        $svnUrl = 'https://plugins.svn.wordpress.org/'.
            $input->getArgument('plugin-name').'/';

        $output->writeln('Checking out <info>'.$svnUrl.'</info>');
        //Using exec() until we find a good SVN library
        exec('svn checkout '.$svnUrl.' '.$this->svnDir);

        $this->repository = new Repository('./');
        $references = $this->repository->getReferences();
        foreach ($references->getTags() as $tag) {
            $tagName = $tag->getName();
            $this->exportTag($tagName, 'tags/'.$tagName);
        }
        $this->exportTag('master', 'trunk');
        $gitUrl = trim(
            $this->repository->run(
                'config',
                array('--get', 'remote.origin.url')
            )
        );

        exec('svn add --force '.$this->svnDir);
        $commitMsg = 'Import from '.$gitUrl;
        $output->writeln('Committing "<info>'.$commitMsg.'</info>"');
        exec('svn commit -m "'.$commitMsg.'" '.$this->svnDir);
    }
}
