<?php

namespace App\Console\Commands;

use App\Models\FakeUsers;
use App\Models\Users;
use Illuminate\Console\Command;

class RefreshIsCoolUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RefreshIsCoolUserCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $usersIds = FakeUsers::whereNotNull('users_id')->pluck('users_id')->toArray();
        Users::whereIn('id', $usersIds)->update([
            'is_cool_user' => true
        ]);
        return Command::SUCCESS;
    }
}
