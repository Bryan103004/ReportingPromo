<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Sesi/token CSRF yang kadaluarsa (419) biasanya kejadian pas user ninggalin
     * tab lama kebuka (sesi abis idle) terus balik lagi nyoba submit form -- defaultnya
     * Laravel nampilin halaman error teknis yang bikin bingung. Di sini di-redirect
     * balik ke halaman sebelumnya (atau login kalau gak ada) dengan pesan yang jelas,
     * gak usah tampilin error mentah ke user.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            return redirect()
                ->guest(route('login'))
                ->with('status', 'Sesi Anda sudah berakhir karena terlalu lama tidak aktif. Silakan login kembali.');
        }

        return parent::render($request, $e);
    }
}
