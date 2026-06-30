<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        $usedUsernames = [];

        DB::table('users')
            ->select('id', 'email', 'name')
            ->orderBy('id')
            ->get()
            ->each(function ($user) use (&$usedUsernames) {
                $email = (string) ($user->email ?? '');
                $name = (string) ($user->name ?? '');

                $baseUsername = '';
                if ($email !== '' && strpos($email, '@') !== false) {
                    $baseUsername = Str::lower((string) Str::before($email, '@'));
                }

                if ($baseUsername === '') {
                    $baseUsername = Str::lower(Str::slug($name, '_'));
                }

                if ($baseUsername === '') {
                    $baseUsername = 'user_' . $user->id;
                }

                $username = $baseUsername;
                $counter = 1;

                while (in_array($username, $usedUsernames, true) || DB::table('users')->where('username', $username)->exists()) {
                    $username = $baseUsername . '_' . $counter;
                    $counter++;
                }

                $usedUsernames[] = $username;

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $username]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
