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
        $customPaginate = function ($default = 10) {
            $perPage = request()->input('number', $default);

            if ($perPage == -1) {
                $total = (clone $this)->count();
                $perPage = $total > 0 ? $total : $default;
            }

            // Cukup panggil paginate standar tanpa memaksa input page manual
            return $this->paginate($perPage)->withQueryString();
        };

        \Illuminate\Database\Query\Builder::macro('customPaginate', $customPaginate);

        // Also register on Eloquent\Builder directly (not just Query\Builder): when
        // customPaginate() is called on a Model query (Rafaksi::..., Jsm::..., etc.),
        // Eloquent\Builder::__call() forwards unknown methods to the underlying
        // Query\Builder but discards the return value and returns $this instead —
        // so without this, callers got back the Eloquent Builder, not a paginator,
        // and ->appends()/->withQueryString() blew up with a BadMethodCallException.
        \Illuminate\Database\Eloquent\Builder::macro('customPaginate', $customPaginate);
    }
}
