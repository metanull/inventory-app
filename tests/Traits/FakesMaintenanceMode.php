<?php

namespace Tests\Traits;

use Illuminate\Contracts\Foundation\MaintenanceMode as MaintenanceModeContract;

/**
 * Keeps `artisan down` / `artisan up` from touching real, shared state.
 *
 * The framework's default driver is FileBasedMaintenanceMode, which stores
 * maintenance state in storage/framework/down. That file is global: under
 * `php artisan test --parallel` every worker shares it, so a test that puts
 * the application down makes unrelated HTTP tests in *other* workers fail
 * with 503 from PreventRequestsDuringMaintenance. Storage::fake() is no help
 * here — it fakes the application's own public lock disk, not the framework's
 * maintenance file.
 *
 * Binding an in-memory driver lets the real down/up commands run end to end
 * while confining maintenance state to the current process.
 */
trait FakesMaintenanceMode
{
    /**
     * Swap the file-based maintenance driver for an in-process one.
     */
    protected function fakeMaintenanceMode(): void
    {
        $this->app->instance(MaintenanceModeContract::class, new class implements MaintenanceModeContract
        {
            /** @var array<string, mixed> */
            private array $payload = [];

            private bool $active = false;

            /** @param array<string, mixed> $payload */
            public function activate(array $payload): void
            {
                $this->payload = $payload;
                $this->active = true;
            }

            public function deactivate(): void
            {
                $this->payload = [];
                $this->active = false;
            }

            public function active(): bool
            {
                return $this->active;
            }

            /** @return array<string, mixed> */
            public function data(): array
            {
                return $this->payload;
            }
        });
    }

    /**
     * Mark the application as down, so `up` exercises its full path.
     */
    protected function enterFakeMaintenanceMode(): void
    {
        $this->app->make(MaintenanceModeContract::class)->activate([
            'except' => [],
            'redirect' => null,
            'retry' => null,
            'refresh' => null,
            'secret' => null,
            'status' => 503,
            'template' => null,
        ]);
    }
}
