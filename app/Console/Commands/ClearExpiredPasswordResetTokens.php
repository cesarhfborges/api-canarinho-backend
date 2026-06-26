<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PasswordReset;
use Carbon\Carbon;

class ClearExpiredPasswordResetTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:clear-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete expired password reset tokens';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $count = PasswordReset::where('expires_at', '<', Carbon::now())->count();
        
        if ($count > 0) {
            PasswordReset::where('expires_at', '<', Carbon::now())->delete();
            $this->info("Cleared {$count} expired password reset token(s).");
        } else {
            $this->info("No expired tokens to clear.");
        }
    }
}
