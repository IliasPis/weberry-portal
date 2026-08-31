<?php

namespace App\Services;

use App\Models\Service;

/**
 * A Service's type-specific extension table (hosting/domain/maintenance/
 * license) is decided by its ServiceType's category, not by anything the
 * client picks directly - this keeps that mapping in one place instead of
 * scattered across controllers.
 */
class ServiceDetailSynchronizer
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function sync(Service $service, array $data, string $category): void
    {
        match ($category) {
            'hosting' => $service->hostingDetail()->updateOrCreate([], [
                'package' => $data['hosting_package'] ?? null,
                'server' => $data['hosting_server'] ?? null,
                'ip' => $data['hosting_ip'] ?? null,
                'storage' => $data['hosting_storage'] ?? null,
            ]),
            'domain' => $service->domainDetail()->updateOrCreate([], [
                'registrar' => $data['domain_registrar'] ?? null,
                'nameservers' => $data['domain_nameservers'] ?? null,
                'dns_provider' => $data['domain_dns_provider'] ?? null,
            ]),
            'maintenance', 'woocommerce_maintenance' => $service->maintenanceDetail()->updateOrCreate([], [
                'plan' => $data['maintenance_plan'] ?? null,
                'included_hours' => $data['maintenance_included_hours'] ?? 0,
                'used_hours' => $data['maintenance_used_hours'] ?? 0,
                'sla' => $data['maintenance_sla'] ?? null,
            ]),
            'plugin_license', 'theme_license' => $service->licenseDetail()->updateOrCreate([], [
                'license_type' => $data['license_type'] ?? null,
                'purchased_by' => $data['license_purchased_by'] ?? 'us',
                'license_status' => $data['license_status'] ?? 'active',
            ]),
            default => null,
        };
    }
}
