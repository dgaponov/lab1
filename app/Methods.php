<?php

namespace App;

use Core\DB;

class Methods {

    private $db;

    public function __construct()
    {
        $this->db = new DB;
    }

    public function load()
    {
        $count_select = $this->db->queryFetchColAssoc(
            "SELECT COUNT(*) FROM entries"
        );

        $prepared_query = $this->db->prepare(
            "SELECT * FROM entries ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );

        $limit = isset($_POST['limit']) && is_integer($_POST['limit']) && $_POST['limit'] > 0
            && $_POST['limit'] < 50 ? $_POST['limit'] : 20;

        $offset = isset($_POST['offset']) && is_integer($_POST['offset']) && $_POST['offset'] > 0
            ? $_POST['offset'] : 0;

        $prepared_query->bindValue(1, $limit, \PDO::PARAM_INT);
        $prepared_query->bindValue(2, $offset, \PDO::PARAM_INT);
        $prepared_query->execute();

        success([
            'data' => $prepared_query->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $count_select
        ]);
    }

    public function add()
    {
        if (!isset($_SESSION["captcha"]) || $_SESSION["captcha"]!==$_POST["captcha"]) {
            error(['text' => 'Некорректная капча']);
        }

        if(!isset($_POST['name']) || strlen($name = htmlspecialchars($_POST['name'])) < 1 || strlen($name) >= 255) {
            error(['text' => 'Некорректное имя']);
        }
        if(!isset($_POST['email']) || strlen($email = $_POST['email']) < 1 || strlen($email) >= 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error(['text' => 'Некорректная почта']);
        }
        if(!isset($_POST['content']) || strlen($content = htmlspecialchars($_POST['content'])) < 1 || strlen($content) >= 1000) {
            error(['text' => 'Некорректное сообщение']);
        }

        $prepared_query = $this->db->prepare(
            "INSERT INTO entries (name, email, content) VALUES (?,?,?)"
        );

        if($prepared_query->execute([$name, $email, $content])) {
            $id = $this->db->lastInsertId('id');
            $prepared_query = $this->db->prepare('SELECT * FROM entries WHERE id=?');
            $prepared_query->execute([$id]);
            success(['data' => $prepared_query->fetch(\PDO::FETCH_ASSOC)]);
        } else {
            error();
        }
    }

    public function delete()
    {
        if (!isset($_SESSION["captcha"]) || $_SESSION["captcha"]!==$_POST["captcha"]) {
            error(['text' => 'Некорректная капча']);
        }

        if(!isset($_POST['password']) || config('app.admin_password') != md5($_POST['password'])) {
            error(['text' => 'Некорректный пароль']);
        }
        if(!isset($_POST['id'])) {
            error(['text' => 'Некорректный id']);
        }

        $check_id_query = $this->db->prepare(
            "SELECT COUNT(*) FROM entries WHERE id=?"
        );

        $check_id_query->execute([$_POST['id']]);
        $count = $check_id_query->fetch(\PDO::FETCH_COLUMN);

        if(!$count) {
            error(['text' => 'Запись не найдена']);
        }

        $this->db->prepare(
            "DELETE FROM entries WHERE id=?"
        )->execute([$_POST['id']]);

        success();
    }


}