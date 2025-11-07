<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupAllDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:all-databases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup all MySQL databases (superadmin + tenants)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupPath = storage_path('backups/' . now()->format('Y-m-d_H-i-s'));
        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $mysqlUser = env('DB_USERNAME', 'root');
        $mysqlPass = env('DB_PASSWORD', '');
        $mysqlHost = env('DB_HOST', '127.0.0.1');
        $superAdminDb = env('DB_DATABASE', 'serp_db'); // serp_db

        // ✅ Step 1: Backup Super Admin DB
        $this->backupDatabase($mysqlHost, $mysqlUser, $mysqlPass, $superAdminDb, $backupPath);

        // ✅ Step 2: Get tenants from super admin DB
        $tenants = DB::connection('mysql')->table('tenants')->pluck('id');

        $prefix = config('tenancy.database.prefix', 'serp_');
        $suffix = config('tenancy.database.suffix', '_db');

        foreach ($tenants as $tenantId) {
            $tenantDb = $prefix . $tenantId . $suffix;
            $this->backupDatabase($mysqlHost, $mysqlUser, $mysqlPass, $tenantDb, $backupPath);
        }

        $this->info("\n🎉 All backups completed successfully!");
        $this->info("🗂️ Files stored in: {$backupPath}");
    }

    private function backupDatabase($host, $user, $pass, $db, $path)
    {
        $filePath = "{$path}/{$db}.sql";
        $command = sprintf(
            'mysqldump -h %s -u %s --password="%s" %s > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($db),
            escapeshellarg($filePath)
        );

        system($command);
        $this->info("✅ Backed up {$db}");
    }
}
