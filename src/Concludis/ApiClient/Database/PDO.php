<?php
/**
 * Created by PhpStorm.
 * User: tmaass
 * Date: 03.04.2018
 * Time: 12:05
 */

namespace Concludis\ApiClient\Database;


use Concludis\ApiClient\Config\Baseconfig;
use Exception;
use PDOStatement;
use RuntimeException;
use function is_array;
use PDOException;

class PDO extends \PDO {

    public const DEADLOCK_MAX_RETRIES = 3;

    public const RETRY_SLEEP_US = 300000; // 300000 = 0.3 Seconds
    /**
     * Singleton instance of this class.
     * @var PDO|null
     */
    private static ?PDO $instance = null;

    protected int $transactionCounter = 0;

    public static bool $query_logging_enabled = false;

    private static array $query_log = [];

    /**
     * @return PDO
     * @throws PDOException
     */
    public static function getInstance(): PDO {

        if (self::$instance !== null) {
            return self::$instance;
        }

        $dsn = 'mysql:host=' . Baseconfig::$db_host . ';dbname=' . Baseconfig::$db_name . ';charset=utf8mb4';

        $opt = [
            self::ATTR_ERRMODE => self::ERRMODE_EXCEPTION,
            self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_ASSOC,
            self::ATTR_EMULATE_PREPARES => false,
        ];

        self::$instance = new self($dsn, Baseconfig::$db_user, Baseconfig::$db_pass, $opt);

        return self::$instance;
    }

    /**
     * @return bool
     * @throws PDOException
     */
    public function beginTransaction(): bool {

        if($this->transactionCounter === 0) {
            $success = parent::beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL$this->transactionCounter");
            $success = true;
        }

        $this->transactionCounter++;
        return $success;
    }

    /**
     * @return bool
     * @throws PDOException
     */
    public function commit(): bool {

        if ($this->transactionCounter === 0) {
            throw new PDOException('Rollback error : There is no transaction started');
        }

        $this->transactionCounter--;

        if($this->transactionCounter === 0) {
            try {
                $success = parent::commit();
            } catch (PDOException $e) {
                $this->transactionCounter++;
                throw $e;
            }
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL$this->transactionCounter");
            $success = true;
        }

        return $success;
    }

    /**
     * @return bool
     * @throws PDOException
     */
    public function rollback(): bool {

        if ($this->transactionCounter === 0) {
            throw new PDOException('Rollback error : There is no transaction started');
        }

        $this->transactionCounter--;

        if($this->transactionCounter === 0) {
            $success = parent::rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL$this->transactionCounter");
            $success = true;
        }

        return $success;
    }

    /**
     * @param $tablename
     * @param $database
     * @return bool
     * @throws Exception
     */
    public function tableExists($tablename, $database=null): bool {

        if($database === null) {
            $sql = 'SHOW TABLES LIKE "'.$tablename.'"';
        } else {
            $sql = 'SHOW TABLES IN `'.$database.'` LIKE "'.$tablename.'"';
        }

        $stmt = $this->query($sql);

        if($stmt === false) {
            throw new RuntimeException('Show tables failed');
        }

        return $stmt->rowCount() > 0;

    }

    /**
     * @param string $tablename
     * @return PDOStatement
     * @throws Exception
     */
    public function optimizeTable(string $tablename): PDOStatement {

        $sql = 'OPTIMIZE TABLE `'.$tablename.'`';

        $stmt = $this->query($sql);

        if($stmt === false) {
            throw new RuntimeException('Optimize table failed');
        }

        return $stmt;
    }

    /**
     * @param string $sql
     * @param array $placeholders
     * @return array
     * @throws Exception
     */
    public function select(string $sql, array $placeholders = array()): array {

//        static $count = 0;
//
//        try {

        $start_time = microtime(true);

        if (!empty($placeholders)) {

            $ph = $this->preparePlaceholders($sql, $placeholders);

            $stmt = $this->prepare($sql);

            $stmt->execute($ph);

        } else {

            $stmt = $this->query($sql);
        }

        $res = $stmt->fetchAll();

        if(self::$query_logging_enabled) {
            self::$query_log[] = [
                'qy' => $sql,
                'ph' => $placeholders,
                'ms' => microtime(true) - $start_time
            ];
        }

        return $res;

//        } catch (PDOException $e) {
//            throw $e;

//            // Handling Deadlocks
//            if ($count < self::DEADLOCK_MAX_RETRIES && $e->getCode() === 40001) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                $count++;
//                $res = $this->select($sql, $placeholders);
//                $count--;
//                return $res;
//            }
//
//            // Handling Mysql Server Has Gone Away
//            if($e->getCode() === 2006) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                self::$instance = null;
//                self::getInstance();
//                return self::$instance->select($sql, $placeholders);
//            }
//
//            return array();
//        }
    }

    /**
     * @param string $sql
     * @param array $placeholders
     * @return false|array
     * @throws Exception
     */
    public function selectOne(string $sql, array $placeholders = array()): bool|array {

//        static $count = 0;
//
//        try {
        $start_time = microtime(true);

        if (!empty($placeholders)) {

            $ph = $this->preparePlaceholders($sql, $placeholders);

            $stmt = $this->prepare($sql);

            $stmt->execute($ph);
        } else {

            $stmt = $this->query($sql);
        }

        $res = false;
        if ($stmt->rowCount() > 0) {
            $res = (array)$stmt->fetch();
        }


        if(self::$query_logging_enabled) {
            self::$query_log[] = [
                'qy' => $sql,
                'ph' => $placeholders,
                'ms' => microtime(true) - $start_time
            ];
        }
        return $res;

//        } catch (PDOException $e) {
//            throw $e;

//            // Handling Deadlocks
//            if ($count < self::DEADLOCK_MAX_RETRIES && $e->getCode() === 40001) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                $count++;
//                $res = $this->selectOne($sql, $placeholders);
//                $count--;
//                return $res;
//            }
//
//            // Handling Mysql Server Has Gone Away
//            if($e->getCode() === 2006) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                self::$instance = null;
//                self::getInstance();
//                return self::$instance->selectOne($sql, $placeholders);
//            }
//
//            return false;
//        }
    }

    /**
     * @param string $sql
     * @param array $placeholders
     * @param $last_insert_id
     * @return bool
     * @throws Exception
     */
    public function insert(string $sql, array $placeholders = array(), &$last_insert_id = null): bool {

//        static $count = 0;
//
//        try {
        if (!empty($placeholders)) {

            $placeholders = $this->preparePlaceholders($sql, $placeholders);

            $stmt = $this->prepare($sql);

            if ($stmt->execute($placeholders)) {
                $last_insert_id = $this->lastInsertId();
                return true;
            }
        } else if ($this->exec($sql) !== false) {
            $last_insert_id = $this->lastInsertId();
            return true;
        }
//        } catch (PDOException $e) {
//            throw $e;

//            // Handling Deadlocks
//            if ($count < self::DEADLOCK_MAX_RETRIES && $e->getCode() === 40001) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                $count++;
//                $res = $this->insert($sql, $placeholders, $last_insert_id);
//                $count--;
//                return $res;
//            }
//
//            // Handling Mysql Server Has Gone Away
//            if($e->getCode() === 2006) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                self::$instance = null;
//                self::getInstance();
//                return self::$instance->insert($sql, $placeholders, $last_insert_id);
//            }

//        }
        return false;
    }

    /**
     * @param string $sql
     * @param array $placeholders
     * @param int|null $rows_affected
     * @return bool
     * @throws Exception
     */
    public function update(string $sql, array $placeholders = array(), ?int &$rows_affected = 0): bool {

//        static $count = 0;

//        try {

        $placeholders = $this->preparePlaceholders($sql, $placeholders);

        $stmt = $this->prepare($sql);

        if ($stmt->execute($placeholders)) {
            $rows_affected = $stmt->rowCount();
            return true;
        }

//        } catch (PDOException $e) {
//            throw $e;
//
//            // Handling Deadlocks
//            if ($count < self::DEADLOCK_MAX_RETRIES && $e->getCode() === 40001) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                $count++;
//                $res = $this->update($sql, $placeholders, $rows_affected);
//                $count--;
//                return $res;
//            }
//
//            // Handling Mysql Server Has Gone Away
//            if($e->getCode() === 2006) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                self::$instance = null;
//                self::getInstance();
//                return self::$instance->update($sql, $placeholders, $rows_affected);
//            }
//
//        }
        return false;
    }

    /**
     * @param string $sql
     * @param array $placeholders
     * @param int|null $rows_affected
     * @return bool
     * @throws Exception
     */
    public function delete(string $sql, array $placeholders = array(), ?int &$rows_affected = 0): bool {

//        static $count = 0;
//
//        try {
        $rows_affected = 0;

        $placeholders = $this->preparePlaceholders($sql, $placeholders);

        $stmt = $this->prepare($sql);

        if ($stmt->execute($placeholders)) {
            $rows_affected = $stmt->rowCount();
            return true;
        }

//        } catch (PDOException $e) {
//            throw $e;

//            // Handling Deadlocks
//            if ($count < self::DEADLOCK_MAX_RETRIES && $e->getCode() === 40001) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                $count++;
//                $res = $this->delete($sql, $placeholders, $rows_affected);
//                $count--;
//                return $res;
//            }
//
//            // Handling Mysql Server Has Gone Away
//            if($e->getCode() === 2006) {
//                usleep(self::RETRY_SLEEP_US * ($count + 1));
//                self::$instance = null;
//                self::getInstance();
//                return self::$instance->delete($sql, $placeholders, $rows_affected);
//            }

//        }
        return false;
    }

    public function getQueryLog(): array {
        return self::$query_log;
    }

    /**
     * @param string $sql
     * @param array $ph
     * @return array
     */
    private function preparePlaceholders(string &$sql, array $ph): array {

        $placeholders = [];
        $trans = [];

        foreach($ph as $k => $v) {
            if(is_array($v)) {
                $i = 0;
                $tmp_keys = [];
                foreach($v as $av) {
                    $ak = $k . '_' . $i;
                    $tmp_keys[] = $ak;
                    $placeholders[$ak] = $av;
                    $i++;
                }
                $trans[$k] = implode(',', $tmp_keys);
                continue;
            }
            $placeholders[$k] = $v;
        }

        if(!empty($trans)) {
            $sql = strtr($sql, $trans);
        }

        return $placeholders;
    }


}