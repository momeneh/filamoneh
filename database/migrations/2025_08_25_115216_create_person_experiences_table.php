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
        Schema::create('person_experiences', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('job_title')->comment('عنوان شغلی')->nullable();
            $table->string('job_start_year')->comment('سال شروع')->nullable();
            $table->string('job_end_date')->comment('سال پایان')->nullable();
            $table->foreignId('center_id')->nullable()->constrained();
            $table->foreignId('person_id')->comment('سوابق شغلی - افراد')->nullable()->constrained()->onDelete('cascade');

			$table->index('center_id');
			$table->index('person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_experiences');
    }
};
