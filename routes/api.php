<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin;
use App\Modules\Customer;
use App\Modules\Agent;
use LaravelCommon\App\Helpers\RouteHelper;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/admin')->name('admin.')->group(function ($router) {
    RouteHelper::New($router, Admin\AuthController::class);
    Route::middleware('auth:sanctum')->group(function ($router) {
        RouteHelper::New($router, Admin\AdminsController::class);
        RouteHelper::New($router, Admin\AssetLogsController::class);
        RouteHelper::New($router, Admin\AssetsController::class);
        RouteHelper::New($router, Admin\BonusController::class);
        RouteHelper::New($router, Admin\CasesController::class);
        RouteHelper::New($router, Admin\CaseDetailsController::class);
        RouteHelper::New($router, Admin\CoinsController::class);
        RouteHelper::New($router, Admin\ConfigsController::class);
        RouteHelper::New($router, Admin\FakeUsersController::class);
        RouteHelper::New($router, Admin\FundsController::class);
        RouteHelper::New($router, Admin\JackpotLogsController::class);
        RouteHelper::New($router, Admin\JackpotsController::class);
        RouteHelper::New($router, Admin\JackpotsHasUsersController::class);
        RouteHelper::New($router, Admin\PledgeProfitsController::class);
        RouteHelper::New($router, Admin\PledgesController::class);
        RouteHelper::New($router, Admin\PledgesHasFundsController::class);
        RouteHelper::New($router, Admin\SubscribesController::class);
        RouteHelper::New($router, Admin\SysPermissionsController::class);
        RouteHelper::New($router, Admin\SysRolesController::class);
        RouteHelper::New($router, Admin\UsersController::class);
        RouteHelper::New($router, Admin\VipsController::class);
        RouteHelper::New($router, Admin\Web3TransactionsController::class);
    });
});

Route::prefix('/agent')->name('agent.')->group(function ($router) {
    RouteHelper::New($router, Agent\AuthController::class);
    Route::middleware('auth:sanctum')->group(function ($router) {
        RouteHelper::New($router, Agent\AssetLogsController::class);
        RouteHelper::New($router, Agent\AssetsController::class);
        RouteHelper::New($router, Agent\UserFollowsController::class);
        RouteHelper::New($router, Agent\UsersController::class);
    });
});

Route::prefix('/customer')->name('customer.')->group(function ($router) {
    RouteHelper::New($router, Customer\AiTradeController::class);
    RouteHelper::New($router, Customer\AssetsController::class);
    RouteHelper::New($router, Customer\AuthController::class);
    RouteHelper::New($router, Customer\BonusController::class);
    RouteHelper::New($router, Customer\CasesController::class);
    RouteHelper::New($router, Customer\CoinsController::class);
    RouteHelper::New($router, Customer\EarnController::class);
    RouteHelper::New($router, Customer\GiftController::class);
    RouteHelper::New($router, Customer\HomeController::class);
    RouteHelper::New($router, Customer\ReferralsController::class);
    RouteHelper::New($router, Customer\SysMessagesController::class);
    RouteHelper::New($router, Customer\TestsController::class);
    RouteHelper::New($router, Customer\TransferController::class);
    RouteHelper::New($router, Customer\UsersController::class);
    RouteHelper::New($router, Customer\VipController::class);
    RouteHelper::New($router, Customer\FriendsController::class);
});
