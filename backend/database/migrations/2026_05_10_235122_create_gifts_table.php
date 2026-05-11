<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gifts', function (Blueprint $table) {

    $table->id();

    $table->string('title');

    $table->text('body');

    $table->tinyInteger('case'); // 1,2,3(1はユーザー登録日に限らず全員配布。2はユーザー登録日が配布日以降の全員配布、3は特定ユーザーへの配布)
$table->string('user_id')->nullable();// caseが3のときに配布対象ユーザーを指定
$table->string('reward_type')->index();// リワードの種類 (例: "currency", "item")
$table->string('reward_code')->nullable();// リワードのid (例: アイテムIDや通貨ID)
$table->integer('reward_amount')->nullable();// リワードの量 (例: 1000コインやアイテム3個)
$table->dateTime('expires_at')->nullable();// 有効期限
$table->dateTime('from_date')->nullable();

     $table->dateTime('send_at')->nullable()->index();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
