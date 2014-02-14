<?php

namespace ErikDubbelboer\LaravelMysqlQueue;


class LaravelMysqlQueueServiceProvider extends \Illuminate\Support\ServiceProvider {

  /**
   * @inheritdoc
   */
  public function boot() {
    $this->package('ErikDubbelboer/LaravelMysqlQueue');

    $this->app->extend('queue', function($manager, $app) {
      $manager->addConnector('mysql', function() use ($app) {
        return new MysqlConnector();
      });

      return $manager;
    });
  }

  /**
   * @inheritdoc
   */
  public function register() {
    // Do nothing.
  }

  /**
   * @inheritdoc
   */
  public function provides() {
    return array();
  }

}
