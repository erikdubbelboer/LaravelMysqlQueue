<?php

namespace ErikDubbelboer\LaravelMysqlQueue;


class MysqlJob extends \Illuminate\Queue\Jobs\Job {

  /**
   * The Mysql queue instance.
   *
   * @var MysqlQueue
   */
  protected $mysqlQueue;


  /**
   * @var string
   */
  protected $payload;


  public function __construct(\Illuminate\Container\Container $container, $mysqlQueue, $payload, $queue) {
    $this->container  = $container;
    $this->mysqlQueue = $mysqlQueue;
    $this->payload    = $payload;
    $this->queue      = $queue;
  }

  /**
   * @inheritdoc
   */
  public function fire() {
    $this->resolveAndFire(json_decode($this->payload, true));
  }

  /**
   * @inheritdoc
   */
  public function getRawBody() {
    return $this->payload;
  }

  /**
   * Delete the job from the queue.
   *
   * @return void
   */
  public function delete()
  {
    parent::delete();

    $this->mysqlQueue->delete($this->payload);
  }

  /**
   * Release the job back into the queue.
   *
   * @param  int  $delay
   * @return void
   */
  public function release($delay = 0) {
    $this->mysqlQueue->release($this->queue, $this->payload, $delay, $this->attempts() + 1);
  }

  /**
   * Get the number of times the job has been attempted.
   *
   * @return int
   */
  public function attempts() {
    return array_get(json_decode($this->payload, true), 'attempts');
  }

  /**
   * Get the job identifier.
   *
   * @return int
   */
  public function getJobId() {
    return array_get(json_decode($this->payload, true), 'id');
  }

  /**
   * Get the IoC container instance.
   *
   * @return \Illuminate\Container\Container
   */
  public function getContainer() {
    return $this->container;
  }

}
