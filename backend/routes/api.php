<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\RankingItemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\PersonalRankingController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\ReportController;

Route::middleware('auth:sanctum')->group(function () {
   Route::middleware('throttle:60,1')->group(function () {
      Route::post('/items/consume/ticket', [ItemController::class, 'consume_ticket']);
      Route::post('/items/consume', [ItemController::class, 'consume']);
   });

   Route::middleware('throttle:10,1')->group(function () {
      Route::post('/blocks', [BlockController::class, 'store']);
      Route::delete('/blocks/unblock', [BlockController::class, 'destroy']);
      Route::post('/likes/toggle', [LikeController::class, 'toggle']);
      Route::post('/comments', [CommentController::class, 'store']);
      Route::post('/currency/change', [CurrencyController::class, 'change']);
      Route::post('user/invite', [UserController::class, 'applyInvite']);
      Route::post('user/email', [UserController::class, 'updateEmail']);
      Route::post('/user/email/send-code', [UserController::class, 'sendVerifyCode']);
      Route::post('/user/email/verify', [UserController::class, 'verifyEmail']);
      Route::post('/user/transfer', [UserController::class, 'transferAccount']);
      Route::post('/user/transfer/send-code', [UserController::class, 'sendTransferCode']);
      Route::post('/rankings', [RankingController::class, 'store']);
   });
   Route::middleware('throttle:2,1')->group(function () {
      Route::post('/reports', [ReportController::class, 'store']);
      Route::put('/personal-ranking/update', [PersonalRankingController::class, 'update']);

      Route::put('/users/update', [UserController::class, 'update']);
   });
   Route::middleware('throttle:30,1')->group(function () {
      Route::post('/vote', [VoteController::class, 'vote']);
      Route::post('/gifts/receive', [GiftController::class, 'receive']);
   });

   Route::get('/blocks/status', [BlockController::class, 'status']);
   Route::get('/blocks', [BlockController::class, 'index']);
   Route::get('/comments/{ranking_id}', [CommentController::class, 'index']);
   Route::get('/currencies', [CurrencyController::class, 'index']);
   Route::get('/gifts', [GiftController::class, 'index']);
   Route::get('/items/my-icons', [ItemController::class, 'myIcons']);
   Route::get('/items/my-items', [ItemController::class, 'myItems']);
   Route::get('/ranking/row/{id}', [RankingController::class, 'rowShow']);
   Route::get('/rankings', [RankingController::class, 'index']);
   Route::post('/follow', [FollowController::class, 'follow']);
   Route::get('/ranking/invite/{inviteCode}', [RankingController::class, 'showByInviteCode']);
});

//sanctum認証不要なルート
Route::middleware('throttle:10,1')->group(function () {
   Route::post('/items', [RankingItemController::class, 'store']);
   Route::post('/items/{id}/alias', [RankingItemController::class, 'addAlias']);
   Route::delete('/items/{id}/alias/{alias}', [RankingItemController::class, 'deleteAlias']);
   Route::post('/auth/guest', [AuthController::class, 'guestLogin']);
   Route::get('/app/status', [AppController::class, 'status']);
   Route::get('/users/public/{publicId}', [UserController::class, 'findByPublicId']);
});
Route::get('/personal-ranking/{userId}', [PersonalRankingController::class, 'show']);
Route::get('/users/{userId}/voted-rankings', [VoteController::class, 'votedRankings']);
Route::get('/rankings/official-latest', [RankingController::class, 'officialLatest']);
Route::get('/ranking/{id}', [RankingController::class, 'show']);
Route::get('/users/{device_id}', [UserController::class, 'show']);
Route::get('/random-rankings', [RankingController::class, 'random']);
Route::get('/follow/counts/{userId}', [FollowController::class, 'counts']);
Route::get('/follow/followings/{userId}', [FollowController::class, 'followings']);
Route::get('/follow/followers/{userId}', [FollowController::class, 'followers']);
Route::get('/game/session', [GameController::class, 'getSession']);
Route::get('/rankings/user/{userId}', [RankingController::class, 'getByUser']);
Route::get('/tags', [TagController::class, 'index']);
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);
Route::get('/items/{id}', [RankingItemController::class, 'show']);
