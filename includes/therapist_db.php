<?php
// Minimal PDO helper for therapist_directory database

class TherapistDB {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function pdo(): PDO {
        return $this->db->getPdo();
    }

    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }

    public function fetchAll($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }

    public function fetchOne($sql, $params = []) {
        return $this->db->fetchOne($sql, $params);
    }

    public function insert($table, $data) {
        return $this->db->insert($table, $data);
    }

    public function update($table, $data, $where, $whereParams = []) {
        return $this->db->update($table, $data, $where, $whereParams);
    }

    public function delete($table, $where, $params = []) {
        return $this->db->delete($table, $where, $params);
    }

    public function tableExists($table) {
        return $this->db->tableExists($table);
    }

    public function selectValue($sql, $params = []) {
        return $this->db->selectValue($sql, $params);
    }

    public function selectOne($sql, $params = []) {
        return $this->db->selectOne($sql, $params);
    }

    public function select($sql, $params = []) {
        return $this->db->select($sql, $params);
    }
}