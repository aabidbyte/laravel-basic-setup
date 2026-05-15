<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use App\Support\Tenancy\TenantRuntime;
use Illuminate\Bus\BatchRepository;
use Illuminate\Bus\DatabaseBatchRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

final class TenantRuntimeBootstrapper implements TenancyBootstrapper
{
    /**
     * @var array<string, mixed>
     */
    private array $originalConfig = [];

    public function __construct(
        private readonly ConfigRepository $config,
        private readonly Application $app,
    ) {}

    public function bootstrap(Tenant $tenant): void
    {
        $this->rememberOriginalConfig();
        $this->applyTenantConfig();
        $this->forgetRuntimeRepositories();
    }

    public function revert(): void
    {
        $this->restoreOriginalConfig();
        $this->forgetRuntimeRepositories();
    }

    private function rememberOriginalConfig(): void
    {
        if ($this->originalConfig !== []) {
            return;
        }

        foreach ($this->configKeys() as $key) {
            $this->originalConfig[$key] = $this->config->get($key);
        }
    }

    private function applyTenantConfig(): void
    {
        foreach ($this->tenantConfig() as $key => $value) {
            $this->config->set($key, $value);
        }
    }

    private function restoreOriginalConfig(): void
    {
        foreach ($this->originalConfig as $key => $value) {
            $this->config->set($key, $value);
        }

        $this->originalConfig = [];
    }

    /**
     * @return array<int, string>
     */
    private function configKeys(): array
    {
        return \array_keys($this->tenantConfig());
    }

    /**
     * @return array<string, mixed>
     */
    private function tenantConfig(): array
    {
        return [
            'session.connection' => TenantRuntime::TENANT_DATABASE_CONNECTION,
            'session.table' => TenantRuntime::SESSIONS_TABLE,
            'queue.connections.database.connection' => TenantRuntime::TENANT_DATABASE_CONNECTION,
            'queue.batching.database' => TenantRuntime::TENANT_DATABASE_CONNECTION,
            'queue.failed.database' => TenantRuntime::TENANT_DATABASE_CONNECTION,
        ];
    }

    private function forgetRuntimeRepositories(): void
    {
        $this->app->forgetInstance('queue.failer');
        $this->app->forgetInstance(BatchRepository::class);
        $this->app->forgetInstance(DatabaseBatchRepository::class);
    }
}
