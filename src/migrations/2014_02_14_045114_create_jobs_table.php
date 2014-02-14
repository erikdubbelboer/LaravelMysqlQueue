<?php

use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration {

  protected $table;

  public function __construct()
  {
    $this->table = \Config::get('queue.connections.' . \Config::get('queue.default') . '.table');
  }

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create($this->table, function($table)
    {
      $table->engine = 'InnoDB';

      $table->increments('id');
      $table->string('queue');
      $table->text('payload');
      $table->integer('created_at')->unsigned();
      $table->integer('updated_at')->unsigned();
      $table->integer('run_after')->unsigned();
      $table->enum('status', array('WAITING', 'RUNNING'));

      $table->index(array('queue', 'run_after', 'status'));
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop($this->table);
  }

}
