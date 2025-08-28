<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--tables=* : Specific tables to backup}';
    protected $description = 'Backup database PostgreSQL ke folder storage/app/backups';

    public function handle()
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 5432);
        
        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        
        // Buat folder backup jika belum ada
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }
        
        $tables = $this->option('tables');
        $tableOption = '';
        
        if ($tables !== '*') {
            // Untuk PostgreSQL, gunakan -t untuk setiap tabel
            $tableList = explode(',', $tables);
            foreach ($tableList as $table) {
                $tableOption .= " -t " . trim($table);
            }
        }
        
        // Set environment variable untuk password PostgreSQL
        putenv("PGPASSWORD={$password}");
        
        // Command untuk backup PostgreSQL
        $command = sprintf(
            'pg_dump -h %s -p %s -U %s -d %s %s --no-password --verbose --no-owner --no-acl > %s 2>&1',
            $host,
            $port,
            $username,
            $database,
            $tableOption,
            $path
        );
        
        $output = [];
        exec($command, $output, $return);
        
        // Clear password dari environment
        putenv("PGPASSWORD");
        
        if ($return === 0) {
            $this->info('✅ Backup PostgreSQL berhasil: ' . $filename);
            $this->info('📁 Lokasi: ' . $path);
            
            // Tampilkan ukuran file
            $size = $this->formatBytes(filesize($path));
            $this->info('📊 Ukuran: ' . $size);
            
            // Hapus backup lama (lebih dari 30 hari)
            $this->cleanOldBackups();
        } else {
            $this->error('❌ Backup gagal!');
            $this->error('Output: ' . implode("\n", $output));
        }
    }
    
    private function cleanOldBackups()
    {
        $files = glob(storage_path('app/backups/*.sql'));
        $now = time();
        
        foreach ($files as $file) {
            if ($now - filemtime($file) >= 30 * 24 * 60 * 60) { // 30 hari
                unlink($file);
                $this->info('🗑️ Backup lama dihapus: ' . basename($file));
            }
        }
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}