<?php

/*
 * The Routing Model
 * Link http://github.com/typefo/lamb
 * Copyright (C) typefo <typefo@qq.com>
 */

use Tool\Filter;

class RoutingModel {
    public $db = null;
    private $table = 'routing';
    private $column = ['rexp', 'target', 'description'];
    
    public function __construct() {
        $this->db = Yaf\Registry::get('db');
    }

    public function get($gid = null) {
        $group = [];
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE id = ' . intval($gid);
        $sth = $this->db->query($sql);
        if ($sth) {
            $group = $sth->fetch();
        }

        return $group;
    }
    
    public function getAll() {
        $groups = [];
        $sql = 'SELECT * FROM ' . $this->table . ' ORDER BY id';
        $sth = $this->db->query($sql);
        if ($sth) {
            $groups = $sth->fetchAll();
        }

        return $groups;
    }

    public function create(array $data = null) {
        $data = $this->checkArgs($data);

        $sql = 'INSERT INTO ' . $this->table . '(rexp, target, description) VALUES(:rexp, :target, :description)';
        $sth = $this->db->prepare($sql);

        if (count($data) > 1) {
            $sth->bindValue(':rexp', $data['rexp'],PDO::PARAM_STR);
            $sth->bindValue(':target', $data['target'],PDO::PARAM_INT);
            $sth->bindValue(':description', $data['description'], PDO::PARAM_STR);
            if ($sth->execute()) {
                return true;
            }
        }

        return false;
    }

    public function delete($id = null) {
        $id = intval($id);

        $sql = 'SELECT target as gid FROM routing WHERE id = ' . $id;
        $sth = $this->db->query($sql);
        
        if ($sth === false) {
            return false;
        }
        
        $reply = $sth->fetch();
        if (isset($reply['gid'])) {
            $sql = 'DELETE FROM channels WHERE gid = ' . intval($reply['gid']);
            if ($this->db->query($sql)) {
                $sql = 'DELETE FROM routing WHERE id = ' . $id;
                if ($this->db->query($sql)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function change($id = null, array $data = null) {
        $id = intval($id);
        $data = $this->checkArgs($data);
        unset($data['rexp'], $data['target']);
        $column = $this->keyAssembly($data);

        if (count($data) > 0) {
            $sql = 'UPDATE ' . $this->table . ' SET ' . $column . ' WHERE id = :id';
            $sth = $this->db->prepare($sql);
            $sth->bindValue(':id', $id, PDO::PARAM_INT);

            foreach ($data as $key => $val) {
                if ($val !== null) {
                    $sth->bindValue(':' . $key, $data[$key], is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
            }

            if ($sth->execute()) {
                return true;
            }
        }

        return false;
    }

    public function total($gid = null) {
        $count = 0;
        $gid = intval($gid);
        $sql = 'SELECT count(id) FROM channels WHERE rid = :gid';
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':gid', $gid, PDO::PARAM_INT);

        if ($sth->execute()) {
            $count = intval($sth->fetchColumn());
        }

        return $count;
    }

    public function isExist($gid = null) {
        $gid = intval($gid);
        $sql = 'SELECT count(id) FROM ' . $this->table . ' WHERE id = :gid';
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':gid', $gid, PDO::PARAM_INT);

        if ($sth->execute()) {
            $count = $sth->fetchColumn();
            if ($count > 0) {
                return true;
            }
        }

        return false;
    }
    
    public function checkArgs(array $data) {
        $res = array();
        $data = array_intersect_key($data, array_flip($this->column));

        foreach ($data as $key => $val) {
            switch ($key) {
            case 'rexp':
                $res['rexp'] = Filter::string($val, null, 1, 127);
                break;
            case 'target':
                $res['target'] = Filter::number($val, null, 1);
                break;
            case 'description':
                $res['description'] = Filter::string($val, 'no description', 1, 64);
                break;
            }
        }

        return $res;
    }
    
    public function keyAssembly(array $data) {
    	$text = '';
        $append = false;
        foreach ($data as $key => $val) {
            if ($val !== null) {
                if ($text != '' && $append) {
                    $text .= ", $key = :$key";
                } else {
                    $append = true;
                    $text .= "$key = :$key";
                }
            }
        }
        return $text;
    }
}
