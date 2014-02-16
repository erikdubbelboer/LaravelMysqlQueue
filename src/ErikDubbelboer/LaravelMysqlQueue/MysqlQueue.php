<?php

namespace ErikDubbelboer\LaravelMysqlQueue;


use \Illuminate\Database\Connection;


class MysqlQueue extends \Illuminate\Queue\Queue implements \Illuminate\Queue\QueueInterface {

  /**
   * The connection name.
   *
   * @var Connection
   */
  protected $connection;

  /**
   * Table name.
   *
   * @var string
   */
  protected $table;

  /**
   * The name of the default queue.
   *
   * @var string
   */
  protected $default = 'default';


  public function __construct(Connection $connection, $table) {
    $this->connection = $connection;
    $this->table      = $table;
  }

  /**
   * @inheritdoc
   */
  public function push($job, $data = '', $queue = null) {
    if (is_null($queue)) {
      $queue = $this->default;
    }

    return $this->pushRaw($this->createPayload($job, $data, $queue), $queue);
  }

  /**
   * @inheritdoc
   */
  public function pushRaw($payload, $queue = null, array $options = array()) {
    if (is_null($queue)) {
      $queue = $this->default;
    }

    $this->connection->insert('
      INSERT INTO ' . $this->table . '
      SET queue      = ?,
          payload    = ?,
          created_at = UNIX_TIMESTAMP(),
          updated_at = 0,
          run_after  = UNIX_TIMESTAMP(),
          status     = "WAITING"
    ', array(
      $queue,
      $payload
    ));

    return true;
  }

  /**
   * @inheritdoc
   */
  public function later($delay, $job, $data = '', $queue = null) {
    if (is_null($queue)) {
      $queue = $this->default;
    }

    return $this->connection->insert('
      INSERT INTO ' . $this->table . '
      SET queue      = ?,
          payload    = ?,
          created_at = UNIX_TIMESTAMP(),
          updated_at = 0,
          run_after  = ?,
          status     = "WAITING"
    ', array(
      $queue,
      json_encode(array(
        'class' => $job,
        'data'  => $data
      )),
      $this->getTime() + $delay
    ));
  }

  protected function getMeta($payload, $key) {
    $payload = json_decode($payload, true);

    return $payload[$key];
  }

  /**
   * @inheritdoc
   */
  public function release($queue, $payload, $delay, $attempts) {
    if (is_null($queue)) {
      $queue = $this->default;
    }

    $payload = $this->setMeta($payload, 'attempts', $attempts);

    $id = $this->getMeta($payload, 'id');

    $this->connection->update('
      UPDATE ' . $this->table . '
      SET queue      = ?,
          updated_at = UNIX_TIMESTAMP(),
          run_after  = ?,
          status     = "WAITING"
      WHERE id = ?
    ', array(
      $queue,
      $this->getTime() + $delay,
      $id
    ));
  }

  /**
   * @inheritdoc
   */
  public function pop($queue = null) {
    if (is_null($queue)) {
      $queue = $this->default;
    }

    $this->connection->beginTransaction();

    $job = $this->connection->select('
      SELECT id, payload
      FROM ' . $this->table . '
      WHERE queue = ?
        AND run_after <= UNIX_TIMESTAMP()
        AND status = "WAITING"
      ORDER BY run_after ASC
      LIMIT 1
      FOR UPDATE
    ', array(
      $queue
    ));

    if (empty($job)) {
      $this->connection->commit();

      return null;
    }

    $id      = $job[0]->id;
    $payload = $this->setMeta($job[0]->payload, 'id', $id);

    $this->connection->update('
      UPDATE ' . $this->table . '
      SET updated_at = UNIX_TIMESTAMP(),
          status     = "RUNNING"
      WHERE id = ?
    ', array(
      $id
    ));

    $this->connection->commit();

    return new MysqlJob($this->container, $this, $payload, $queue);
  }

  /**
   * Delete a job.
   *
   * @param string $payload
   */
  public function delete($payload) {
    $id = $this->getMeta($payload, 'id');

    /*$this->connection->delete('
      DELETE FROM ' . $this->table . '
      WHERE id = ?
    ', array(
      $id
    ));*/

    $this->connection->update('
      UPDATE ' . $this->table . '
      SET updated_at = UNIX_TIMESTAMP(),
          status     = "DONE"
      WHERE id = ?
    ', array(
      $id
    ));
  }

}
