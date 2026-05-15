<?php

declare(strict_types=1);

namespace App\Enums\Tenancy;

enum TenantAudienceMode: string
{
    case AllTenantMembers = 'all_tenant_members';
    case AllRecords = 'all_records';
    case SpecificTenant = 'specific_tenant';
    case CentralOnly = 'central_only';
}
