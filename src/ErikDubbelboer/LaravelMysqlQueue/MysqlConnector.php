<?php

namespace ErikDubbelboer\LaravelMysqlQueue;


class MysqlConnector implements \Illuminate\Queue\Connectors\ConnectorInterface {

  /**
   * @param array $config
   * @return MysqlQueue
   */
  public function connect(array $config) {
    return new MysqlQueue(\Illuminate\Support\Facades\DB::connection($config['connection']), $config['table']);
  }

}
