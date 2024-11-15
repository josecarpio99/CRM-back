<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Customer;
use App\Policies\DealPolicy;
use App\Policies\LeadPolicy;
use App\Policies\CustomerPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Customer::class => CustomerPolicy::class,
        Lead::class => LeadPolicy::class,
        Deal::class => DealPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
