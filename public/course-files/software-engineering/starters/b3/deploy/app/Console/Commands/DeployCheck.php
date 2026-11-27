<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// A production checklist you can run: php artisan nkwen:deploy-check
// FAIL lines stop the server from starting (docker/start.sh). WARN lines are advice.
class DeployCheck extends Command
{
    protected $signature = 'nkwen:deploy-check';

    protected $description = 'Check the production settings before the API starts';

    private int $failures = 0;

    public function handle(): int
    {
        $this->check(config('app.key') !== null && config('app.key') !== '', 'APP_KEY is set', 'APP_KEY is empty: run php artisan key:generate --show and set it on the host.');
        $this->check(config('app.debug') === false, 'APP_DEBUG is false', 'APP_DEBUG is true: error pages would show your code and settings to anyone.');
        $this->check(app()->environment('production'), 'APP_ENV is production', 'APP_ENV is '.app()->environment().': set APP_ENV=production.', critical: false);
        $this->check(str_starts_with((string) config('app.url'), 'https://'), 'APP_URL uses https', 'APP_URL should be your https:// address.', critical: false);
        $this->check(in_array(config('database.default'), ['mysql', 'mariadb']), 'The database is MySQL or MariaDB', 'DB_CONNECTION is '.config('database.default').': set DB_CONNECTION=mysql.');

        try {
            DB::connection()->getPdo();
            $this->check(true, 'The database answers', '');
        } catch (\Throwable $e) {
            $this->check(false, '', 'Cannot reach the database: '.$e->getMessage());
        }

        $secret = (string) config('services.momo.webhook_secret');
        $this->check(strlen($secret) >= 32, 'MOMO_WEBHOOK_SECRET is set (32+ characters)', 'MOMO_WEBHOOK_SECRET is missing or shorter than 32 characters.');
        $this->check(config('logging.default') !== 'single' || ! app()->environment('production'), 'Logs go to the host (stderr or daily)', 'LOG_CHANNEL=single writes to a file the host may delete: use stderr.', critical: false);
        $this->check(config('logging.channels.'.config('logging.default').'.level', 'debug') !== 'debug' || ! app()->environment('production'), 'LOG_LEVEL is not debug', 'LOG_LEVEL=debug writes too much in production: use info or warning.', critical: false);

        if ($this->failures > 0) {
            $this->error("{$this->failures} problem(s) must be fixed before going live.");

            return self::FAILURE;
        }
        $this->info('Ready for production.');

        return self::SUCCESS;
    }

    private function check(bool $ok, string $pass, string $fix, bool $critical = true): void
    {
        if ($ok) {
            $this->line("  PASS  {$pass}");
        } elseif ($critical) {
            $this->failures++;
            $this->line("  FAIL  {$fix}");
        } else {
            $this->line("  WARN  {$fix}");
        }
    }
}
