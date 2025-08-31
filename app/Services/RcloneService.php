<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class RcloneService
{
    public $remoteName;
    private $remotePath;
    private $configPath;
    public $binaryPath;
    
    public function __construct()
    {
        // Load configuration from config/rclone.php which reads from .env
        $this->remoteName = config('rclone.remote_name');
        $this->remotePath = config('rclone.remote_path');
        $this->configPath = config('rclone.config_path');
        $this->binaryPath = config('rclone.binary_path');
    }
    
    /**
     * Check if rclone is available
     */
    public function isAvailable(): bool
    {
        try {
            $process = new Process([$this->binaryPath, 'version']);
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Rclone not available: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sync local folder to remote (manual sync)
     */
    public function syncToRemote(string $localPath, string $remotePath = null): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'output' => '',
                'error' => 'Rclone is not available or not configured'
            ];
        }
        
        $remote = $remotePath ?? $this->remotePath;
        $fullRemotePath = "{$this->remoteName}:{$remote}";
        
        $command = [
            $this->binaryPath,
            'sync',
            $localPath,
            $fullRemotePath,
            '--config', $this->configPath,
            '--verbose',
            '--stats', '10s',
            '--transfers', '4',
            '--checkers', '8',
            '--contimeout', '60s',
            '--timeout', '300s',
            '--retries', '3',
            '--low-level-retries', '10',
            '--stats-file-name-length', '0'
        ];
        
        // Add exclude patterns for temporary files
        $excludedExtensions = config('rclone.excluded_extensions', []);
        foreach ($excludedExtensions as $ext) {
            $command[] = '--exclude';
            $command[] = '*' . $ext;
        }
        
        $process = new Process($command);
        $process->setTimeout(300); // 5 minutes timeout
        
        try {
            Log::info('Starting rclone sync', [
                'local' => $localPath,
                'remote' => $fullRemotePath
            ]);
            
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            Log::info('Rclone sync completed successfully');
            
            return [
                'success' => true,
                'output' => $process->getOutput(),
                'error' => null
            ];
            
        } catch (ProcessFailedException $exception) {
            Log::error('Rclone sync failed', [
                'command' => implode(' ', $command),
                'error' => $exception->getMessage(),
                'output' => $process->getErrorOutput()
            ]);
            
            return [
                'success' => false,
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput()
            ];
        }
    }
    
    /**
     * Check if file exists in remote
     */
    public function checkRemoteFile(string $remotePath): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}/{$remotePath}";
        
        $command = [
            $this->binaryPath,
            'lsf',
            $fullRemotePath,
            '--config', $this->configPath,
            '--max-depth', '1'
        ];
        
        $process = new Process($command);
        $process->setTimeout(60);
        
        try {
            $process->run();
            return !empty(trim($process->getOutput()));
        } catch (\Exception $e) {
            Log::error('Rclone check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Get remote file info
     */
    public function getRemoteFileInfo(string $remotePath): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }
        
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}/{$remotePath}";
        
        $command = [
            $this->binaryPath,
            'lsjson',
            $fullRemotePath,
            '--config', $this->configPath,
            '--no-modtime',
            '--no-mimetype'
        ];
        
        $process = new Process($command);
        $process->setTimeout(60);
        
        try {
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = json_decode($process->getOutput(), true);
                return $output[0] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Rclone info failed', ['error' => $e->getMessage()]);
        }
        
        return null;
    }
    
    /**
     * Delete remote file
     */
    public function deleteRemoteFile(string $remotePath): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}/{$remotePath}";
        
        $command = [
            $this->binaryPath,
            'delete',
            $fullRemotePath,
            '--config', $this->configPath
        ];
        
        $process = new Process($command);
        $process->setTimeout(60);
        
        try {
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Rclone delete failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * List remote files
     */
    public function listRemoteFiles(string $remotePath = ''): array
    {
        if (!$this->isAvailable()) {
            return [];
        }
        
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}";
        if ($remotePath) {
            $fullRemotePath .= "/{$remotePath}";
        }
        
        $command = [
            $this->binaryPath,
            'lsjson',
            $fullRemotePath,
            '--config', $this->configPath,
            '--recursive'
        ];
        
        $process = new Process($command);
        $process->setTimeout(120);
        
        try {
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = json_decode($process->getOutput(), true);
                return $output ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Rclone list failed', ['error' => $e->getMessage()]);
        }
        
        return [];
    }
    
    /**
     * Get remote storage size
     */
    public function getRemoteSize(string $remotePath = ''): array
    {
        if (!$this->isAvailable()) {
            return ['size' => 0, 'count' => 0];
        }
        
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}";
        if ($remotePath) {
            $fullRemotePath .= "/{$remotePath}";
        }
        
        $command = [
            $this->binaryPath,
            'size',
            $fullRemotePath,
            '--config', $this->configPath,
            '--json'
        ];
        
        $process = new Process($command);
        $process->setTimeout(120);
        
        try {
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = json_decode($process->getOutput(), true);
                return [
                    'size' => $output['bytes'] ?? 0,
                    'count' => $output['count'] ?? 0
                ];
            }
        } catch (\Exception $e) {
            Log::error('Rclone size failed', ['error' => $e->getMessage()]);
        }
        
        return ['size' => 0, 'count' => 0];
    }
    
    /**
     * Test remote connection
     */
    public function testConnection(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        $command = [
            $this->binaryPath,
            'lsd',
            "{$this->remoteName}:",
            '--config', $this->configPath,
            '--max-depth', '1'
        ];
        
        $process = new Process($command);
        $process->setTimeout(30);
        
        try {
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Rclone connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Create remote directory
     */
    public function createRemoteDirectory(string $remotePath): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        $fullRemotePath = "{$this->remoteName}:{$this->remotePath}/{$remotePath}";
        
        $command = [
            $this->binaryPath,
            'mkdir',
            $fullRemotePath,
            '--config', $this->configPath
        ];
        
        $process = new Process($command);
        $process->setTimeout(30);
        
        try {
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Rclone mkdir failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}