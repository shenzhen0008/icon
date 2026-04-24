<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_env_decision_logs', function (Blueprint $table): void {
            $table->id();
            $table->char('request_id', 36);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->char('ip_hash', 64);
            $table->char('fingerprint_hash', 64);
            $table->enum('decision', ['allow', 'deny']);
            $table->string('reason_code', 64);
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->string('route_key', 128);
            $table->string('rule_version', 32);
            $table->timestamp('created_at')->useCurrent();

            $table->unique('request_id', 'uniq_client_env_decision_logs_request_id');
            $table->index(['decision', 'created_at'], 'idx_client_env_decision_created_at');
            $table->index(['fingerprint_hash', 'created_at'], 'idx_client_env_fingerprint_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_env_decision_logs');
    }
};

