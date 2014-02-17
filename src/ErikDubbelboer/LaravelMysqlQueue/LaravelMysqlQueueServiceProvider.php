<?php

namespace ErikDubbelboer\LaravelMysqlQueue;


class LaravelMysqlQueueServiceProvider extends \Illuminate\Support\ServiceProvider {

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register() {
    $this->app->resolving('queue', function($manager) {
      $manager->addConnector('mysql', function() {
        return new MysqlConnector();
      });

      return $manager;
    });
  }

}
