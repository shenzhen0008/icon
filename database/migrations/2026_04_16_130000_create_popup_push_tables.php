<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->text('content');
            $table->string('level', 20)->default('info');
            $table->boolean('requires_ack')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'starts_at', 'ends_at'], 'idx_popup_campaigns_status_time');
            $table->index(['created_by'], 'idx_popup_campaigns_created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('popup_campaign_user', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('user_id');
            $table->string('delivery_status', 20)->default('pending');
            $table->dateTime('pushed_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'user_id'], 'uk_campaign_user');
            $table->index(['user_id', 'delivery_status'], 'idx_popup_target_user_status');
            $table->foreign('campaign_id')->references('id')->on('popup_campaigns')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('popup_receipts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('shown_at')->nullable();
            $table->dateTime('dismissed_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'user_id'], 'uk_popup_receipt');
            $table->index(['user_id'], 'idx_popup_receipt_user');
            $table->foreign('campaign_id')->references('id')->on('popup_campaigns')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_receipts');
        Schema::dropIfExists('popup_campaign_user');
        Schema::dropIfExists('popup_campaigns');
    }
};
