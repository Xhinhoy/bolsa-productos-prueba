<?php

use App\Enums\DocumentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('original_filename');
            $table->string('stored_path');
            $table->unsignedBigInteger('original_size_bytes');
            $table->unsignedBigInteger('stored_size_bytes');
            $table->unsignedInteger('page_count')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('status')->default(DocumentStatus::Processed->value);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
