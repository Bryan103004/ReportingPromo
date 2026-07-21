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
        // Macro kustom untuk handle pagination dinamis
        \Illuminate\Database\Eloquent\Builder::macro('customPaginate', function ($default = 10) {
            $perPage = request()->input('number', $default);
            
            // Jika user memilih nilai maksimal (999999999 / Semua data)
            if ($perPage == 999999999) {
                $total = $this->count();
                // Dipaginate sebanyak total data agar links() di bawah tabel tidak error/hilang
                return $this->paginate($total > 0 ? $total : $default)->withQueryString();
            }

            // .withQueryString() berfungsi menjaga parameter '?number=X' tetap ada saat pindah halaman (page 2, 3, dst)
            return $this->paginate($perPage)->withQueryString();
        });

        \Illuminate\Database\Query\Builder::macro('customPaginate', function ($default = 10) {
            $perPage = request()->input('number', $default);
            
            // Jika user memilih nilai maksimal (999999999 / Semua data)
            if ($perPage == 999999999) {
                $total = $this->count();
                // Dipaginate sebanyak total data agar links() di bawah tabel tidak error/hilang
                return $this->paginate($total > 0 ? $total : $default)->withQueryString();
            }

            // .withQueryString() berfungsi menjaga parameter '?number=X' tetap ada saat pindah halaman (page 2, 3, dst)
            return $this->paginate($perPage)->withQueryString();
        });
    }
}
