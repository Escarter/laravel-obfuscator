<?php

namespace Escarter\LaravelObfuscator\Commands;

use Illuminate\Console\Command;
use Escarter\LaravelObfuscator\Services\ObfuscatorService;

class ObfuscateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:run 
                            {--dry-run : Run without making changes}
                            {--no-backup : Skip backup creation}
                            {--no-views : Skip Blade view cleaning}
                            {--no-debug-disable : Skip debug disabling features}';

    /**
     * The console command description.
     */
    protected $description = 'Obfuscate Laravel application code';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔒 Laravel Code Obfuscator');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Confirm action
        if (!$this->option('dry-run')) {
            if (!$this->confirm('⚠️  This will obfuscate your code. Continue?', false)) {
                $this->warn('Obfuscation cancelled.');
                return Command::FAILURE;
            }
        }

        // Load configuration
        $config = config('obfuscator');
        
        // Override config based on options
        if ($this->option('no-backup')) {
            $config['backup']['enabled'] = false;
        }
        
        if ($this->option('no-views')) {
            $config['clean_blade_views'] = false;
        }
        
        if ($this->option('no-debug-disable')) {
            $config['debug_disabling']['enabled'] = false;
        }

        // Create obfuscator service
        $obfuscator = new ObfuscatorService($config);
        
        $basePath = base_path();

        if ($this->option('dry-run')) {
            $this->info('🔍 Running in DRY-RUN mode (no changes will be made)');
            $this->newLine();
            return $this->dryRun($config, $basePath);
        }

        // Run obfuscation
        $startTime = microtime(true);
        
        try {
            $stats = $obfuscator->obfuscate($basePath, function ($type, $data) {
                match ($type) {
                    'backup' => $this->info("📦 Backup created: {$data}"),
                    'skip' => $this->line("⏭️  Skipped: {$data}"),
                    'progress' => $this->line("✅ Processed {$data} files..."),
                    'views' => $this->info("🧹 Cleaned {$data} Blade views"),
                    default => null,
                };
            });

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('🏆 OBFUSCATION COMPLETE!');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            // Display statistics
            $statsTable = [
                ['PHP Files Obfuscated', $stats['files_processed']],
                ['Files Skipped', $stats['files_skipped']],
                ['Blade Views Cleaned', $stats['views_cleaned']],
                ['Variables Obfuscated', $stats['variables_obfuscated']],
                ['Duration', "{$duration}s"],
            ];
            
            if ($config['debug_disabling']['enabled']) {
                $statsTable[] = ['Debug Disabling', '✅ Enabled'];
            }
            
            $this->table(['Metric', 'Value'], $statsTable);

            $this->newLine();

            if ($stats['backup_path']) {
                $this->info("✅ Backup: {$stats['backup_path']}");
            }

            if ($config['output']['show_encryption_key']) {
                $this->warn("🔑 Encryption Key: {$stats['encryption_key']}");
                $this->warn("⚠️  SAVE THIS KEY! You may need it for debugging.");
            }

            $this->newLine();
            $this->info('🚀 Your application is now obfuscated!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Obfuscation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Run in dry-run mode
     */
    protected function dryRun(array $config, string $basePath): int
    {
        $totalFiles = 0;
        $skippedFiles = 0;

        foreach ($config['paths'] as $path) {
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $path;
            if (!is_dir($fullPath)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;

                $basename = basename($file->getPathname());
                $shouldSkip = false;
                
                foreach ($config['excluded_files'] as $excluded) {
                    if (str_contains($basename, $excluded)) {
                        $shouldSkip = true;
                        break;
                    }
                }

                if ($shouldSkip) {
                    $this->line("⏭️  Would skip: {$file->getPathname()}");
                    $skippedFiles++;
                } else {
                    $this->line("✅ Would obfuscate: {$file->getPathname()}");
                    $totalFiles++;
                }
            }
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("  - Files to obfuscate: {$totalFiles}");
        $this->info("  - Files to skip: {$skippedFiles}");
        $this->newLine();

        return Command::SUCCESS;
    }
}

