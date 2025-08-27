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
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('title')->comment('عنوان');
            $table->foreignId('paper_type_id')->nullable()->comment('نوع مقاله')->constrained();
            $table->foreignId('country_id')->comment('کشور')->nullable()->constrained();
            $table->string('title_url')->comment('عنوان مقاله در URL سایت')->nullable();
            $table->string('priority')->comment('ترتیب نمایش مقاله')->nullable();
            $table->string('image_path1',1024)->comment('تصویر شماره 1')->nullable();          
            $table->string('image_path2',1024)->comment('تصویر شماره 2')->nullable();        
            $table->string('paper_file',1024)->comment('فایل مقاله')->nullable();            
            $table->string('paper_word_file',1024)->comment('فایل مقاله - word')->nullable();            
            $table->string('paper_date', 10)->comment('تاریخ مقاله')->nullable();
            $table->string('doi')->comment('DOI')->nullable();
            $table->string('count_page')->comment('تعداد صفحات')->nullable();
            $table->string('refrence_link')->comment('لینک ارجاع دهی')->nullable();
            $table->boolean('is_accepted')->nullable();
            $table->boolean('is_visible')->nullable();
            $table->boolean('is_archived')->nullable();
            $table->text('abstract')->comment('چکیده مقاله')->nullable();
            $table->longText('description')->nullable()->comment('متن مقاله');
            $table->unsignedBigInteger('insert_user_id')->comment('ثبت کننده مقاله')->nullable();
            $table->foreign('insert_user_id', 'insert_user_id590_FK')->references('id')->on('users');
            $table->unsignedBigInteger('edit_user_id')->comment('آخرین ویراستار')->nullable();
            $table->foreign('edit_user_id', 'edit_user_id157_FK')->references('id')->on('users');

			$table->index('insert_user_id');
			$table->index('edit_user_id');
			$table->index('paper_type_id');
			$table->index('country_id');
			$table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papers');
    }
};
