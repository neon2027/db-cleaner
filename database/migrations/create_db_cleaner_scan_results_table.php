<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('db_cleaner_scan_results', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('column_name')->nullable();
            $table->float('quality_score')->default(100);
            $table->char('grade', 1)->default('A');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('total_issues')->default(0);
            $table->json('issue_breakdown')->nullable(); // {duplicates: n, whitespace: n, ...}
            $table->json('column_scores')->nullable();   // {col: score, ...}
            $table->json('raw_analysis')->nullable();    // full TableAnalysis payload
            $table->string('connection')->default('default');
            $table->timestamps();

            $table->index(['table_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('db_cleaner_scan_results');
    }
};
