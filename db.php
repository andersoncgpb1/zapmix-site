<?php
// db.php - Database connection and utilities

class Database {
    private static $pdo = null;
    
    public static function connect() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            self::$pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            return self::$pdo;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            die('Erro ao conectar ao banco de dados');
        }
    }
    
    public static function query($sql, $params = []) {
        $pdo = self::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public static function fetchAll($sql, $params = []) {
        return self::query($sql, $params)->fetchAll();
    }
    
    public static function fetchOne($sql, $params = []) {
        return self::query($sql, $params)->fetch();
    }
    
    public static function lastInsertId() {
        return self::connect()->lastInsertId();
    }
    
    public static function rowCount($stmt = null) {
        if ($stmt === null) {
            return null;
        }
        return $stmt->rowCount();
    }
}
?>
