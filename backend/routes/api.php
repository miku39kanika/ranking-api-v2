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
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\SeasonCrownRankingController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AppleNotificationController;

Route::middleware('auth:sanctum')->group(function () {
   Route::post('/items/consume', [ItemController::class, 'consume'])->middleware('throttle:10,1');
   Route::put('/users/update', [UserController::class, 'update'])->middleware('throttle:30,1');
   Route::put('/personal-ranking/update', [PersonalRankingController::class, 'update'])->middleware('throttle:30,1');
   Route::post('/rankings', [RankingController::class, 'store'])->middleware('throttle:10,1');
   Route::post('/blocks', [BlockController::class, 'store'])->middleware('throttle:30,1');
   Route::delete('/blocks/unblock', [BlockController::class, 'destroy'])->middleware('throttle:5,1');
   Route::post('/likes/toggle', [LikeController::class, 'toggle'])->middleware('throttle:30,1');
   Route::post('/comments', [CommentController::class, 'store'])->middleware('throttle:20,1');
   Route::post('/currency/change', [CurrencyController::class, 'change'])->middleware('throttle:30,1');
   Route::post('user/invite', [UserController::class, 'applyInvite'])->middleware('throttle:5,1');
   Route::post('user/email', [UserController::class, 'updateEmail'])->middleware('throttle:3,1');
   Route::post('/user/email/send-code', [UserController::class, 'sendVerifyCode'])->middleware('throttle:3,1');
   Route::post('/user/email/verify', [UserController::class, 'verifyEmail'])->middleware('throttle:3,1');
   Route::post('/user/transfer', [UserController::class, 'transferAccount'])->middleware('throttle:5,1');
   Route::post('/user/transfer/send-code', [UserController::class, 'sendTransferCode'])->middleware('throttle:5,1');;
   Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:20,1');
   Route::post('/feedbacks', [FeedbackController::class, 'store'])->middleware('throttle:20,1');
   Route::post('/users/delete', [UserController::class, 'delete'])->middleware('throttle:2,1');
   Route::post('/vote', [VoteController::class, 'vote'])->middleware('throttle:60,1');
   Route::post('/gifts/receive', [GiftController::class, 'receive'])->middleware('throttle:10,1');
   Route::post('/items', [RankingItemController::class, 'store'])->middleware('throttle:30,1');
   Route::post('/items/{id}/alias', [RankingItemController::class, 'addAlias'])->middleware('throttle:15,1');
   Route::delete('/items/{id}/alias/{alias}', [RankingItemController::class, 'deleteAlias'])->middleware('throttle:30,1');
   Route::post('/purchases', [PurchaseController::class, 'store'])->middleware('throttle:5,1');
   Route::get('/random-rankings', [RankingController::class, 'random']);
   Route::middleware('throttle:1,360')->group(function () {
      Route::post('/game/reward', [GameController::class, 'reward']);
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
   Route::get(
      '/season-crown-rankings',
      [SeasonCrownRankingController::class, 'index']
   );
});




//sanctum認証不要なルート
Route::post('/auth/guest', [AuthController::class, 'guestLogin'])->middleware('throttle:20,1');
Route::get('/app/status', [AppController::class, 'status'])->middleware('throttle:10,1');
Route::get('/users/public/{publicId}', [UserController::class, 'findByPublicId'])->middleware('throttle:20,1');
Route::get('/personal-ranking/{userId}', [PersonalRankingController::class, 'show']);
Route::get('/users/{userId}/voted-rankings', [VoteController::class, 'votedRankings']);
Route::get('/rankings/official-latest', [RankingController::class, 'officialLatest']);
Route::get('/ranking/{id}', [RankingController::class, 'show']);
Route::get('/users/{device_id}', [UserController::class, 'show']);

Route::get('/follow/counts/{userId}', [FollowController::class, 'counts']);
Route::get('/follow/followings/{userId}', [FollowController::class, 'followings']);
Route::get('/follow/followers/{userId}', [FollowController::class, 'followers']);
Route::get('/game/session', [GameController::class, 'getSession']);
Route::get('/rankings/user/{userId}', [RankingController::class, 'getByUser']);
Route::get('/tags', [TagController::class, 'index']);
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);
Route::get('/items/{id}', [RankingItemController::class, 'show']);
Route::post('/apple/app-store-notifications', [AppleNotificationController::class, 'handle']);
