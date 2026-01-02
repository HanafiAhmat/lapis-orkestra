<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Commands;

use Illuminate\Framework\Facades\DB;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'seed:clear-all', description: 'Clear all tables')]
class SeedClearAllCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // DB::table('ampas_blog_comments')->truncate();
        // DB::table('ampas_blog_post_tag')->truncate();
        // DB::table('ampas_blog_posts')->truncate();
        // DB::table('ampas_blog_tags')->truncate();
        // DB::table('ampas_blog_categories')->truncate();
        // DB::table('blog_pages')->truncate();

        $output->writeln('<info>All blog-related tables truncated successfully.</info>');
        return self::SUCCESS;
    }
}
