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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('public_id', 12)->unique();
            $table->string('user_name', 15)->nullable()->default('名無しのユーザー');
            $table->string('device_id')->nullable()->index();
            $table->string('email', 255)->nullable()->unique();
            // 👇 ユーザー区分（課金ランク）
            $table->tinyInteger('plan_type')->default(0);
            $table->timestamp('plan_expires_at')
                ->nullable();
            // 0: 無料
            // 1: 有料
            // 2: プレミアム
            // 3: 管理者（とかでもOK）
            $table->string('icon_type')->nullable()->default('system');
            $table->string('icon_name')->nullable()->default('person.circle');
            $table->text('about_self', 60)->nullable();
            // 👇 論理削除
            $table->boolean('is_deleted')->default(false);
            // 👇 BAN関連 
            $table->timestamp('banned_at')->nullable();
            // 👇 招待
            $table->string('invite_code', 12)->unique();
            $table->uuid('invited_by')->nullable();
            $table->foreign('invited_by')->references('id')->on('users')->nullOnDelete();
            // 👇 メール認証関連
            $table->string('email_verify_code')
                ->nullable();
            $table->timestamp('email_verified_at')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
