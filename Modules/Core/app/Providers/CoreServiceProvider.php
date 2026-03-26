<?php

namespace Modules\Core\Providers;

use Livewire\Livewire;
use Modules\Core\Livewire\Dashboard;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CoreServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Core';
    protected string $nameLower = 'core';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Livewire::component('core::dashboard', Dashboard::class);
    }
}