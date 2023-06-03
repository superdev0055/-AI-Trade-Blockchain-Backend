<?php

namespace App\Console\Commands;

use App\Models\Users;
use App\NewServices\UserBalanceSnapshotsServices;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class RunCreateUserBalanceSnapshotCommand extends Command
{
    protected $signature = 'RunCreateUserBalanceSnapshotCommand';
    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle(): int
    {
        Users::each(function ($user) {
            try {
                DB::beginTransaction();
                UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);
                $this->line("\t User:::$user->id...DONE");
                DB::commit();
            } catch (Exception $exception) {
                $this->error("\t User:::$user->id...ERROR...{$exception->getMessage()}");
                DB::rollBack();
            }
        });
        return CommandAlias::SUCCESS;
    }
}
