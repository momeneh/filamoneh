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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name')->comment('نام')->nullable();
            $table->string('family')->comment('نام خانوادگی')->nullable();
            $table->string('email')->comment('ایمیل')->nullable()->unique();
            $table->string('national_code')->comment('کد ملی')->nullable();
            $table->string('shenasname')->comment('شماره شناسنامه')->nullable();
            $table->string('passport_number')->comment('شماره گذرنامه')->nullable();
            $table->string('father_name')->comment('نام پدر')->nullable();
            $table->string('birth_year')->comment('سال تولد')->nullable();
            $table->string('photo')->comment('عکس فرد')->nullable();       
            $table->string('website')->comment('وبسایت')->nullable();
            $table->string('mobile')->comment('تلفن همراه')->nullable();
            $table->string('tel')->comment('تلفن')->nullable();
            $table->string('fax')->comment('فکس')->nullable();
            $table->string('postalcode')->comment('کد پستی')->nullable();
            $table->text('addr')->comment('آدرس')->nullable();
            $table->foreignId('country_id')->comment('کشوز')->nullable()->constrained();
            $table->foreignId('province_id')->comment('استان')->nullable()->constrained();
            $table->foreignId('city_id')->comment('شهر')->nullable()->constrained();
            $table->tinyInteger('gender')->comment('جنسیت')->nullable();

			$table->index('country_id');
			$table->index('province_id');
			$table->index('city_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
