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
        Schema::create('person_education', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('grade_id')->comment('مقطع')->nullable()->constrained();
            $table->foreignId('field_id')->comment('رشته')->nullable()->constrained();
            $table->unsignedInteger('start_year')->comment('سال شروع')->nullable();
            $table->unsignedInteger('end_year')->comment('سال پایان')->nullable();
            $table->foreignId('center_id')->comment('مرکز آموزشی')->nullable()->constrained();
            $table->foreignId('person_id')->comment('سوابق تحصیلی - افراد')->nullable()->constrained()->onDelete('cascade');
            $table->string('other_center')->nullable();
            $table->tinyInteger('not_in_list')->default(0);

			$table->index('grade_id');
			$table->index('field_id');
			$table->index('center_id');
			$table->index('person_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_education');
    }
};
