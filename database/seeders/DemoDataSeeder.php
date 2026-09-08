<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DemoDataSeeder extends Seeder
{
    /**
     * Demo/local-only password for every seeded account. Never used
     * outside this seeder, which should never run against production.
     */
    private const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        // The real service-type catalog is permanent, production data
        // seeded by ServiceTypeSeeder (which runs before this in
        // DatabaseSeeder) - demo clients just get assigned real plans.
        $serviceTypes = ServiceType::query()->get()->keyBy('category');

        $superAdmin = User::factory()->create([
            'name' => 'Ilias (Super Admin)',
            'email' => 'iliaspisanos.dev@gmail.com',
            'password' => self::DEMO_PASSWORD,
            'locale' => 'el',
        ]);
        $superAdmin->assignRole('super_admin');

        $technician = User::factory()->create([
            'name' => 'Technician Demo',
            'email' => 'tech@weberry.local',
            'password' => self::DEMO_PASSWORD,
            'locale' => 'el',
        ]);
        $technician->assignRole('staff');

        $this->seedAbcFishing($serviceTypes);
        $this->seedSecondaryClient($serviceTypes);
    }

    /**
     * @param  Collection<string, ServiceType>  $serviceTypes
     */
    private function seedAbcFishing($serviceTypes): void
    {
        $client = Client::create([
            'company_name' => 'ABC Fishing Ltd.',
            'contact_name' => 'Andreas Papadopoulos',
            'vat_number' => 'EL123456789',
            'tax_office' => 'ΔΟΥ Πειραιά',
            'email' => 'info@aboutfishing.gr',
            'phone' => '+30 210 1234567',
            'address' => 'Ακτή Μιαούλη 12, Πειραιάς',
            'billing_email' => 'accounting@aboutfishing.gr',
            'technical_email' => 'info@aboutfishing.gr',
            'status' => 'active',
            'priority' => 'high',
            'start_date' => Carbon::parse('2025-01-15'),
            'internal_notes' => 'Πιθανό redesign αίτημα τον Νοέμβριο. Πολύ καλός πελάτης, πληρώνει πάντα εγκαίρως.',
            'client_visible_notes' => null,
            'last_contact_at' => Carbon::parse('2026-08-28 11:00:00'),
            'next_action' => 'Επιβεβαίωση ανανέωσης hosting πριν τις 20/09',
        ]);

        $owner = User::factory()->create([
            'name' => 'Andreas Papadopoulos',
            'email' => 'owner@aboutfishing.gr',
            'password' => self::DEMO_PASSWORD,
            'locale' => 'el',
            'client_id' => $client->id,
        ]);
        $owner->assignRole('client');
        ClientContact::create([
            'client_id' => $client->id,
            'user_id' => $owner->id,
            'contact_title' => 'owner',
            'is_primary' => true,
            'can_view_billing' => true,
            'can_view_invoices' => true,
            'can_view_requests' => true,
            'can_view_documents' => true,
            'can_create_requests' => true,
            'can_manage_account' => true,
        ]);

        $accountant = User::factory()->create([
            'name' => 'Eleni Accountant',
            'email' => 'accounting@aboutfishing.gr',
            'password' => self::DEMO_PASSWORD,
            'locale' => 'el',
            'client_id' => $client->id,
        ]);
        $accountant->assignRole('client_contact');
        ClientContact::create([
            'client_id' => $client->id,
            'user_id' => $accountant->id,
            'contact_title' => 'accountant',
            'is_primary' => false,
            'can_view_billing' => true,
            'can_view_invoices' => true,
            'can_view_requests' => false,
            'can_view_documents' => true,
            'can_create_requests' => false,
            'can_manage_account' => false,
        ]);

        $marketing = User::factory()->create([
            'name' => 'Maria Marketing',
            'email' => 'marketing@aboutfishing.gr',
            'password' => self::DEMO_PASSWORD,
            'locale' => 'el',
            'client_id' => $client->id,
        ]);
        $marketing->assignRole('client_contact');
        ClientContact::create([
            'client_id' => $client->id,
            'user_id' => $marketing->id,
            'contact_title' => 'marketing',
            'is_primary' => false,
            'can_view_billing' => false,
            'can_view_invoices' => false,
            'can_view_requests' => true,
            'can_view_documents' => true,
            'can_create_requests' => true,
            'can_manage_account' => false,
        ]);

        $website = Website::create([
            'client_id' => $client->id,
            'name' => 'About Fishing',
            'domain' => 'aboutfishing.gr',
            'production_url' => 'https://aboutfishing.gr',
            'staging_url' => 'https://staging.aboutfishing.gr',
            'cms' => 'wordpress',
            'wp_version' => '6.7.1',
            'woo_version' => '9.4.2',
            'theme' => 'Rey Theme',
            'hosting_provider' => 'Weberry Hosting',
            'hosting_package' => 'WooCommerce Pro',
            'server' => 'srv-gr-04',
            'server_ip' => '185.203.114.42',
            'php_version' => '8.3',
            'database_name' => 'aboutfishing_prod',
            'cdn_provider' => 'Cloudflare',
            'cloudflare_enabled' => true,
            'ssl_provider' => "Let's Encrypt",
            'ssl_expires_at' => Carbon::parse('2026-11-28'),
            'analytics_id' => 'G-ABF123456',
            'search_console' => 'verified',
            'smtp_provider' => 'SendGrid',
            'backup_provider' => 'UpdraftPlus + Cloudflare R2',
            'status' => 'active',
            'internal_notes' => 'Custom checkout plugin - ελέγχεται πριν από κάθε update.',
            'client_visible_notes' => null,
        ]);

        $support = Service::create([
            'client_id' => $client->id,
            'website_id' => $website->id,
            'service_type_id' => $serviceTypes['total_support_woocommerce']->id,
            'name' => 'Ολοκληρωμένη Υποστήριξη WooCommerce',
            'provider' => 'Weberry',
            'start_date' => Carbon::parse('2025-09-20'),
            'expiration_date' => Carbon::parse('2026-09-20'),
            'renewal_date' => Carbon::parse('2026-09-20'),
            'billing_cycle' => 'yearly',
            'internal_cost' => 80.00,
            'client_price' => 150.00,
            'auto_renewal' => true,
            'status' => 'active',
            'notes_client' => 'Περιλαμβάνει hosting, καθημερινά backups, ενημερώσεις, ασφάλεια και υποστήριξη καταστήματος.',
        ]);
        $support->maintenanceDetail()->create([
            'plan' => 'Ολοκληρωμένη Υποστήριξη WooCommerce',
            'included_hours' => 4,
            'used_hours' => 1,
            'sla' => 'Απάντηση εντός 1 εργάσιμης ημέρας',
        ]);
    }

    /**
     * A second, unrelated client - exists so client-isolation checks
     * (Policies, tests) have a real "someone else's data" to fail against.
     *
     * @param  Collection<string, ServiceType>  $serviceTypes
     */
    private function seedSecondaryClient($serviceTypes): void
    {
        $client = Client::create([
            'company_name' => 'Nikos Taverna',
            'contact_name' => 'Nikos Georgiou',
            'vat_number' => 'EL987654321',
            'email' => 'info@nikostaverna.gr',
            'phone' => '+30 210 9876543',
            'status' => 'active',
            'priority' => 'normal',
            'start_date' => Carbon::parse('2025-06-01'),
            'last_contact_at' => Carbon::parse('2026-08-20 10:00:00'),
        ]);

        $owner = User::factory()->create([
            'name' => 'Nikos Georgiou',
            'email' => 'owner@nikostaverna.gr',
            'password' => self::DEMO_PASSWORD,
            'locale' => 'el',
            'client_id' => $client->id,
        ]);
        $owner->assignRole('client');
        ClientContact::create([
            'client_id' => $client->id,
            'user_id' => $owner->id,
            'contact_title' => 'owner',
            'is_primary' => true,
            'can_view_billing' => true,
            'can_view_invoices' => true,
            'can_view_requests' => true,
            'can_view_documents' => true,
            'can_create_requests' => true,
            'can_manage_account' => true,
        ]);

        $website = Website::create([
            'client_id' => $client->id,
            'name' => 'Nikos Taverna',
            'domain' => 'nikostaverna.gr',
            'production_url' => 'https://nikostaverna.gr',
            'cms' => 'wordpress',
            'wp_version' => '6.7.1',
            'theme' => 'Astra',
            'hosting_provider' => 'Weberry Hosting',
            'php_version' => '8.3',
            'status' => 'active',
        ]);

        $support = Service::create([
            'client_id' => $client->id,
            'website_id' => $website->id,
            'service_type_id' => $serviceTypes['total_support_wordpress']->id,
            'name' => 'Ολοκληρωμένη Υποστήριξη WordPress',
            'provider' => 'Weberry',
            'start_date' => Carbon::parse('2025-06-01'),
            'expiration_date' => Carbon::parse('2026-09-15'),
            'renewal_date' => Carbon::parse('2026-09-15'),
            'billing_cycle' => 'yearly',
            'internal_cost' => 40.00,
            'client_price' => 90.00,
            'auto_renewal' => false,
            'status' => 'active',
        ]);
        $support->maintenanceDetail()->create([
            'plan' => 'Ολοκληρωμένη Υποστήριξη WordPress',
            'included_hours' => 2,
            'used_hours' => 0,
            'sla' => 'Απάντηση εντός 2 εργάσιμων ημερών',
        ]);
    }
}
