<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

#[Signature('admin:create {--email= : Admin email address} {--name= : Admin display name} {--password= : Admin password}', aliases: ['admin:upsert'])]
#[Description('Create an admin account or reset an existing admin password')]
class UpsertAdmin extends Command
{
    public function handle(): int
    {
        $email = Str::lower((string) ($this->option('email') ?: $this->ask('Email address')));
        $name = (string) ($this->option('name') ?: $this->ask('Display name'));
        $password = (string) ($this->option('password') ?: $this->secret('Password'));

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $admin = Admin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password_hash' => Hash::make($password),
            ],
        );

        $this->info($admin->wasRecentlyCreated ? 'Admin account created.' : 'Admin account updated.');
        $this->line("Email: {$admin->email}");

        return self::SUCCESS;
    }
}
