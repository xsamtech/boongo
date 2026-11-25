<?php

namespace App\Console\Commands;

use App\Services\PremiumService;
use Illuminate\Console\Command;

class ExpirePremiumSubscriptions extends Command
{
    // The name of the artisan command
    protected $signature = 'premium:expire';

    // Command description
    protected $description = 'Expire premium memberships past their expiration date';

    /**
     * Create a command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(PremiumService $premiumService): int
    {
        $this->components->info(__('api.premium.expired_count', ['count' => $premiumService->expire()]));

        return self::SUCCESS;
    }
}
