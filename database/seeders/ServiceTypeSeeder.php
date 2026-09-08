<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

/**
 * The permanent, production service-type catalog: exactly the three
 * flagship support plans the business actually sells, plus the ticket
 * categories offered per plan. Unlike DemoDataSeeder (fake clients for
 * local testing), this is real reference data and runs from
 * DatabaseSeeder alongside RolePermissionSeeder in every environment,
 * production included.
 */
class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = $this->seedServiceTypes();
        $this->seedGlobalTicketCategories();
        $this->seedTicketCategoriesFor($types);
    }

    /**
     * @return array<string, ServiceType>
     */
    private function seedServiceTypes(): array
    {
        $catalog = [
            ['name' => 'Total Support WordPress', 'category' => 'total_support_wordpress'],
            ['name' => 'Total Support WooCommerce', 'category' => 'total_support_woocommerce'],
            ['name' => 'Total Support B2B', 'category' => 'total_support_b2b'],
        ];

        $types = [];
        foreach ($catalog as $row) {
            $types[$row['category']] = ServiceType::query()->updateOrCreate(
                ['category' => $row['category']],
                $row,
            );
        }

        return $types;
    }

    private function seedGlobalTicketCategories(): void
    {
        $global = [
            ['name' => 'Γενική ερώτηση', 'creates_task' => false],
            ['name' => 'Πληρωμή / Τιμολόγιο', 'creates_task' => false],
            ['name' => 'Άλλο', 'creates_task' => false],
        ];

        foreach ($global as $row) {
            TicketCategory::firstOrCreate(['service_type_id' => null, 'name' => $row['name']], $row);
        }
    }

    /**
     * @param  array<string, ServiceType>  $serviceTypes
     */
    private function seedTicketCategoriesFor(array $serviceTypes): void
    {
        $perType = [
            'total_support_wordpress' => [
                ['name' => 'Ενημερώσεις πυρήνα / plugins / theme', 'creates_task' => true],
                ['name' => 'Σφάλμα στη σελίδα (bug)', 'creates_task' => true],
                ['name' => 'Νέα λειτουργία / αλλαγή περιεχομένου', 'creates_task' => true],
                ['name' => 'Ασφάλεια / hacked site', 'creates_task' => true],
                ['name' => 'Απόδοση / ταχύτητα', 'creates_task' => true],
                ['name' => 'Backup / επαναφορά', 'creates_task' => true],
                ['name' => 'Γενική ερώτηση υποστήριξης', 'creates_task' => false],
            ],
            'total_support_woocommerce' => [
                ['name' => 'Πρόβλημα checkout / πληρωμών', 'creates_task' => true],
                ['name' => 'Πρόβλημα αποστολών', 'creates_task' => true],
                ['name' => 'Διαχείριση προϊόντων / κατηγοριών', 'creates_task' => true],
                ['name' => 'Ενσωματώσεις (πληρωμές, μεταφορικά, ERP)', 'creates_task' => true],
                ['name' => 'Σφάλμα καταστήματος (bug)', 'creates_task' => true],
                ['name' => 'Απόδοση καταστήματος', 'creates_task' => true],
                ['name' => 'Γενική ερώτηση υποστήριξης', 'creates_task' => false],
            ],
            'total_support_b2b' => [
                ['name' => 'Διαχείριση λογαριασμών χονδρικής', 'creates_task' => true],
                ['name' => 'Τιμολόγηση ανά πελάτη / ειδικές τιμές', 'creates_task' => true],
                ['name' => 'Ελάχιστες ποσότητες παραγγελίας', 'creates_task' => true],
                ['name' => 'Έγκριση λογαριασμού πελάτη', 'creates_task' => true],
                ['name' => 'Ενσωμάτωση ERP / παραγγελιοληψίας', 'creates_task' => true],
                ['name' => 'Γενική ερώτηση υποστήριξης', 'creates_task' => false],
            ],
        ];

        foreach ($perType as $category => $rows) {
            $serviceType = $serviceTypes[$category] ?? null;
            if (! $serviceType) {
                continue;
            }
            foreach ($rows as $sort => $row) {
                TicketCategory::firstOrCreate(
                    ['service_type_id' => $serviceType->id, 'name' => $row['name']],
                    [...$row, 'sort_order' => $sort],
                );
            }
        }
    }
}
