<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Reminder;
use App\Models\Service;
use App\Models\User;
use App\Services\NotificationDispatcher;
use App\Support\PortalRoles;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Runs daily (see routes/console.php) and flags services whose expiration
 * date is exactly 30, 14, 7, or 1 day(s) away with an admin Reminder + an
 * in-app/email notification, so renewals never get missed silently. Fixed
 * thresholds (rather than "anything in the next 30 days") are what keep
 * this idempotent day-to-day without a separate "already reminded" flag.
 */
class CheckRenewals extends Command
{
    protected $signature = 'renewals:check';

    protected $description = 'Create admin reminders and notifications for services expiring soon';

    private const THRESHOLDS_DAYS = [30, 14, 7, 1];

    public function handle(NotificationDispatcher $notifications): int
    {
        $systemUser = User::role(PortalRoles::ADMIN_SIDE)->orderBy('id')->first();

        if (! $systemUser) {
            $this->warn('No admin user found - skipping (reminders need a created_by_user_id).');

            return self::SUCCESS;
        }

        $created = 0;

        foreach (self::THRESHOLDS_DAYS as $days) {
            $targetDate = Carbon::today()->addDays($days)->toDateString();

            $services = Service::query()
                ->where('status', 'active')
                ->whereDate('expiration_date', $targetDate)
                ->with('client')
                ->get();

            foreach ($services as $service) {
                /** @var Client $client */
                $client = $service->client;

                $alreadyReminded = Reminder::query()
                    ->where('service_id', $service->id)
                    ->whereDate('remind_at', Carbon::today())
                    ->exists();

                if ($alreadyReminded) {
                    continue;
                }

                Reminder::create([
                    'created_by_user_id' => $systemUser->id,
                    'owner_type' => 'admin',
                    'client_id' => $client->id,
                    'website_id' => $service->website_id,
                    'service_id' => $service->id,
                    'title' => "Ανανέωση: {$service->name} ({$client->company_name})",
                    'description' => "Λήγει σε {$days} ".($days === 1 ? 'ημέρα' : 'ημέρες')." ({$service->expiration_date->toDateString()}).",
                    'remind_at' => Carbon::today(),
                    'priority' => $days <= 7 ? 'high' : 'normal',
                ]);

                $notifications->notifyAdmins(
                    'renewal.upcoming',
                    "Ανανέωση σε {$days} ".($days === 1 ? 'ημέρα' : 'ημέρες').": {$service->name}",
                    "{$client->company_name} — λήγει {$service->expiration_date->toDateString()}",
                    route('admin.clients.show', $client->id),
                );

                $created++;
            }
        }

        $this->info("Created {$created} renewal reminder(s).");

        return self::SUCCESS;
    }
}
