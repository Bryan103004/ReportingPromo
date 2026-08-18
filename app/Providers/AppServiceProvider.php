<?php

namespace App\Providers;

// use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() :void
    {
        \Illuminate\Database\Query\Builder::macro('customPaginate', function ($default = 10) {
            $perPage = request()->input('number', $default);
            
            if ($perPage == -1) {
                $total = (clone $this)->count();
                $perPage = $total > 0 ? $total : $default;
            }

            // Cukup panggil paginate standar tanpa memaksa input page manual
            return $this->paginate($perPage)->withQueryString();
        });
    }
}
