<?php

namespace App\Console\Commands;

use App\Helpers\TelegramBot\TelegramBotApi;
use App\Jobs\CreateFakeUsersJob;
use App\Mail\EmailVerifyMail;
use App\Mail\WithdrawSubmitSuccessEmail;
use App\Models\Reports;
use App\Models\Users;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use LaravelCommon\App\Exceptions\Err;
use Symfony\Component\Console\Command\Command as CommandAlias;

class TestCommand extends Command
{
    protected $signature = 'TestCommand';
    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle(): int
    {
//        dump('TestCommand');
//        Log::debug('TestCommand');

//        Mail::to('vnusdc@gmail.com')->send(new EmailVerifyMail('1234'));

//        TelegramBotApi::SendText("测试一下发送信息1");
        dump([
            'today' => Reports::where('day', now()->toDateString())->first()->toArray(),
            'all' => Cache::tags(['reports'])->get('all')
        ]);
        return CommandAlias::SUCCESS;
    }
}
