<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Get the migration connection name.
     *
     * @return string|null
     */
    public function getConnection(): ?string
    {
        return config('tokenizer.connection');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('tokenizer.table', 'auth_tokens'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('scene')->default('default')->index('idx_scene_type')->comment('Scene Type');
            $table->morphs('tokenable', 'idx_tokenable');
            $table->string('name')->index('idx_name')->comment('Token Name');
            $table->text('access_token')->fulltext('fti_access_token')->nullable()->comment('Access Token');
            $table->text('refresh_token')->fulltext('fti_refresh_token')->nullable()->comment('Refresh Token');
            $table->longText('scopes')->comment('Token Scopes');
            $table->timestamp('access_token_expire_at')->index('idx_access_token_expire_at')->comment('Token Expiration Time');
            $table->timestamp('refresh_token_available_at')->index('idx_refresh_token_available_at')->comment('Refresh Token Available Time');
            $table->timestamp('refresh_token_expire_at')->index('idx_refresh_token_expire_at')->comment('Refresh Token Expiration Time');
            $table->timestamp('last_used_at')->nullable()->comment('Last Used Time');
            $table->timestamp('created_at')->nullable()->comment('Created Time');
            $table->timestamp('updated_at')->nullable()->comment('Updated Time');
            $table->timestamp('deleted_at')->index('idx_deleted_at')->nullable()->comment('Deleted Time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tokenizer.table', 'auth_tokens'));
    }
};
