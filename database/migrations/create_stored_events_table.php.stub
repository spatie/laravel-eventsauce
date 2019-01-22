<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoredEventsTable extends Migration
{
    public function up()
    {
        Schema::create('stored_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_id', 36);
            $table->string('event_type', 100);
            $table->string('aggregate_root_id', 36)->nullable()->index();
            $table->dateTime('recorded_at', 6)->index();
            $table->text('payload');
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_messages');
    }
}
