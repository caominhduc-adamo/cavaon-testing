<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tour_id');
            $table->string('reference')->unique();
            $table->enum('status', ['Submitted', 'Confirmed', 'Cancelled'])->default('Submitted');
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();

            $table->foreign('tour_id')
                ->references('id')
                ->on('tours')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['tour_id']);
        });

        Schema::dropIfExists('bookings');
    }
}
