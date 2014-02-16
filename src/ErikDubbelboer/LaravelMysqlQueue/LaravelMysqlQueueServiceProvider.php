<?php

namespace ErikDubbelboer\LaravelMysqlQueue;


class LaravelMysqlQueueServiceProvider extends \Illuminate\Support\ServiceProvider {

  /**
   * Indicates if loading of the provider is deferred.
   *
   * @var bool
   */
  protected $defer = true;

  /**
   * @inheritdoc
   */
  public function boot() {
    $this->app->resolving('queue', function($manager) {
      $manager->addConnector('mysql', function() {
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

}
