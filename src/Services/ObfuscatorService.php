<?php

namespace Escarter\LaravelObfuscator\Services;

use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, PrettyPrinter\Standard};
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ObfuscatorService
{
    protected array $config;
    protected ParserFactory $parserFactory;
    protected Standard $printer;
    protected array $nameMap = [];
    protected string $encryptionKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->parserFactory = new ParserFactory();
        $this->printer = new Standard();
        $this->encryptionKey = bin2hex(random_bytes($config['encryption']['key_length']));
    }

    /**
     * Run the obfuscation process
     */
    public function obfuscate(string $basePath, callable $callback = null): array
    {
        $stats = [
            'files_processed' => 0,
            'files_skipped' => 0,
            'views_cleaned' => 0,
            'backup_path' => null,
            'encryption_key' => $this->encryptionKey,
        ];

        // Create backup
        if ($this->config['backup']['enabled']) {
            $stats['backup_path'] = $this->createBackup($basePath);
            if ($callback) $callback('backup', $stats['backup_path']);
        }

        // Obfuscate PHP files
        foreach ($this->config['paths'] as $path) {
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $path;
            if (!is_dir($fullPath)) continue;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;

                if ($this->shouldSkipFile($file->getPathname())) {
                    $stats['files_skipped']++;
                    if ($callback) $callback('skip', $file->getPathname());
                    continue;
                }

                $this->obfuscateFile($file->getPathname());
                $stats['files_processed']++;
                
                if ($callback && $stats['files_processed'] % $this->config['output']['progress_interval'] === 0) {
                    $callback('progress', $stats['files_processed']);
                }
            }
        }

        // Clean Blade views
        if ($this->config['clean_blade_views']) {
            $stats['views_cleaned'] = $this->cleanBladeViews($basePath);
            if ($callback) $callback('views', $stats['views_cleaned']);
        }

        $stats['variables_obfuscated'] = count($this->nameMap);

        return $stats;
    }

    /**
     * Create a backup of specified directories
     */
    protected function createBackup(string $basePath): string
    {
        $backupDir = $basePath . DIRECTORY_SEPARATOR . 
                     $this->config['backup']['prefix'] . 
                     date($this->config['backup']['timestamp_format']);

        mkdir($backupDir, 0755, true);

        foreach ($this->config['backup']['paths'] as $path) {
            $source = $basePath . DIRECTORY_SEPARATOR . $path;
            $destination = $backupDir . DIRECTORY_SEPARATOR . $path;
            
            if (is_dir($source)) {
                shell_exec("cp -R " . escapeshellarg($source) . " " . escapeshellarg($destination));
            }
        }

        return $backupDir;
    }

    /**
     * Check if a file should be skipped
     */
    protected function shouldSkipFile(string $filepath): bool
    {
        $basename = basename($filepath);
        
        foreach ($this->config['excluded_files'] as $excluded) {
            if (str_contains($basename, $excluded)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obfuscate a single PHP file
     */
    protected function obfuscateFile(string $filepath): void
    {
        $code = file_get_contents($filepath);
        $obfuscated = $this->obfuscateCode($code);
        file_put_contents($filepath, $obfuscated);
    }

    /**
     * Obfuscate PHP code
     */
    protected function obfuscateCode(string $code): string
    {
        $code = $this->stripComments($code);

        try {
            $parser = $this->parserFactory->createForNewestSupportedVersion();
            $ast = $parser->parse($code);
            
            if (!$ast) return $code;

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ObfuscatorVisitor(
                $this->nameMap,
                $this->config
            ));
            
            $obfuscatedAst = $traverser->traverse($ast);
            $result = $this->printer->prettyPrint($obfuscatedAst);
            
            return $this->encryptCode($result);
            
        } catch (\Exception $e) {
            return $code;
        }
    }

    /**
     * Strip comments from PHP code
     */
    protected function stripComments(string $code): string
    {
        $tokens = token_get_all($code);
        $output = '';
        
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] !== T_COMMENT && $token[0] !== T_DOC_COMMENT) {
                    $output .= $token[1];
                }
            } else {
                $output .= $token;
            }
        }
        
        return $output;
    }

    /**
     * Encrypt code using XOR encryption
     */
    protected function encryptCode(string $code): string
    {
        $encrypted = '';
        $keyLength = strlen($this->encryptionKey);
        
        for ($i = 0; $i < strlen($code); $i++) {
            $encrypted .= chr(ord($code[$i]) ^ ord($this->encryptionKey[$i % $keyLength]));
        }
        
        $encoded = base64_encode($encrypted);
        
        return "<?php \$_k=\"{$this->encryptionKey}\";\$_d=base64_decode('{$encoded}');\$_r='';for(\$_i=0;\$_i<strlen(\$_d);\$_i++)\$_r.=chr(ord(\$_d[\$_i])^ord(\$_k[\$_i%strlen(\$_k)]));eval(\$_r);";
    }

    /**
     * Clean Blade view files
     */
    protected function cleanBladeViews(string $basePath): int
    {
        $count = 0;
        $viewsPath = $basePath . DIRECTORY_SEPARATOR . $this->config['views_path'];
        
        if (!is_dir($viewsPath)) return 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($viewsPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (str_contains($file->getPathname(), '.blade.php')) {
                $content = file_get_contents($file->getPathname());
                
                // Remove HTML comments
                $content = preg_replace('/<!--[\s\S]*?-->/', '', $content);
                
                // Remove Blade comments
                $content = preg_replace('/\{\{--[\s\S]*?--\}\}/', '', $content);
                
                file_put_contents($file->getPathname(), $content);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get the encryption key
     */
    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }

    /**
     * Get the name map
     */
    public function getNameMap(): array
    {
        return $this->nameMap;
    }
}

