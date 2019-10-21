<?php

class Review
{
    private $pdo;

    public function __construct($MYSQL_DB_NAME, $MYSQL_HOST, $MYSQL_USER, $MYSQL_PASSWORD)
    {
        try {
            $this->pdo = new PDO('mysql:dbname=' . $MYSQL_DB_NAME . ';host=' . $MYSQL_HOST, $MYSQL_USER, $MYSQL_PASSWORD);
        } catch (PDOException $e) {
            die('Подключение не удалось ' . $e->getMessage());
        }
    }

    public function getSubdivisionReview($id)
    {
        $res = $this->pdo->prepare('SELECT * FROM user_reviews WHERE moderate = 1 AND Subdivision_ID = ?');
        $res->execute(array($id));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeReview($id) {
        $res = $this->pdo->prepare('DELETE FROM user_reviews WHERE review_id=?');
        $res->execute(array($id));
    }


    public function acceptReview($id) {
        $res = $this->pdo->prepare('UPDATE user_reviews SET moderate=1 WHERE review_id=?');
        $res->execute(array($id));
    }

    public function getAllModerated()
    {
        return $this->pdo->query('SELECT ur.*, sub.Subdivision_Name, sub.Hidden_URL, cat.Domain FROM user_reviews as ur JOIN Subdivision as sub ON ur.Subdivision_ID = sub.Subdivision_ID
        JOIN Catalogue as cat ON sub.Catalogue_ID = cat.Catalogue_ID WHERE ur.moderate = 1')->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAllForModeration()
    {
        return $this->pdo->query('SELECT ur.*, sub.Subdivision_Name, sub.Hidden_URL, cat.Domain FROM user_reviews as ur JOIN Subdivision as sub ON ur.Subdivision_ID = sub.Subdivision_ID
        JOIN Catalogue as cat ON sub.Catalogue_ID = cat.Catalogue_ID WHERE ur.moderate = 0')->fetchAll(PDO::FETCH_OBJ);
    }

    public function checkAdd()
    {
        if (isset($_GET['add_review'])) {
            $this->addNewReview($GLOBALS['current_sub']['Subdivision_ID'], $_POST['modal_user_name'], $_POST['modal_user_email'], $_POST['modal_user_review'], $_POST['modal_user_rating']);
        }
    }

    public function addNewReview($sub_id, $name = '', $email = '', $review_text = '', $rating = 4)
    {
        try {
            $this->pdo->beginTransaction();
            $query = $this->pdo->prepare('INSERT INTO user_reviews (Subdivision_ID, name, email, review_text, rating) VALUES (?, ?, ?, ?, ?)');
            $query->execute([$sub_id, $name, $email, $review_text, $rating]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
}