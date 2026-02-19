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
        Schema::create('blogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('author_id');
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->mediumText('description');
            $table->string('image', 255)->nullable();
            $table->unsignedTinyInteger('read_time')->default(1);
            $table->unsignedBigInteger('views')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->json('metatags')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('admins')->onDelete('cascade');

            $table->index('author_id');
            $table->index('status');
            $table->index('deleted_at');
            $table->index(['deleted_at', 'created_at']);
            $table->index(['deleted_at', 'slug']);
            $table->index(['deleted_at', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
