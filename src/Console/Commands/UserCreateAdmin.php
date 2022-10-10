<?php

namespace Twoavy\EvaluationTool\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UserCreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an admin user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return bool|int
     */
    public function handle()
    {
        $email    = $this->ask('What is your email?', env('ADMIN_EMAIL'));
        $name     = $this->ask('What is your name?', env('ADMIN_NAME'));
        $password = $this->ask('Please set your password', env('ADMIN_PASSWORD'));

        if (User::where("email", $email)->first()) {
            $this->error("user with email " . $email . " already exists");
            return false;
        }

        $user                    = new User();
        $user->email             = $email;
        $user->name              = $name;
        $user->password          = $password;
        $user->email_verified_at = Carbon::now();

        $user->save();

        $user->attachRole("admin");

        $this->info("user successfully created");

        return true;
    }
}
