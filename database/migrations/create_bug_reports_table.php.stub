<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->json('steps')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('app_version');
            $table->string('role')->default('');
            $table->string('priority')->nullable();
            $table->timestampTz('validated_at')->nullable();
            $table->string('github_issue_url')->nullable();
            $table->unsignedBigInteger('github_issue_number')->nullable();
            // Set from the GitHub issue's closed_at once it is closed/finished.
            $table->timestampTz('resolved_at')->nullable();
            // Not a hard FK so the package works against any users table.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->index('user_id');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};
