<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The package ships this as a publishable .stub; the Laravel migrator only runs
// .php, so it is materialised here for the served workbench app.
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
            $table->timestampTz('resolved_at')->nullable();
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
