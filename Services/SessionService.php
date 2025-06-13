<?php

namespace Services;

use PDO;

class SessionService
{
    private PDO $conn;
    private string $table = "theses_sessions";

    public function __construct(PDO $connection = null)
    {
        if ($connection === null) {
            $database = new Database();
            $connection = $database->getConnection();
        }
        $this->conn = $connection;
    }

    public function createSession($student, $thesisType, $sessionType, $date, $length, $notes, $notes_next): bool
    {
        try {
            $sql = "INSERT INTO $this->table (idStudents, idTeachers, idThesesTypes, idSessionTypes, date, length, notes, notes_next) 
                    VALUES (:student, :teacher, :thesisType, :sessionType, :date, :length, :notes, :notes_next)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student', $student);
            $stmt->bindParam(':teacher', $_SESSION['user_profile']['name']);
            $stmt->bindParam(':thesisType', $thesisType, PDO::PARAM_INT);
            $stmt->bindParam(':sessionType', $sessionType, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':length', $length, PDO::PARAM_INT);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':notes_next', $notes_next);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new \RuntimeException('Database operation failed', 0, $e);
        }
    }

    public function updateSession($sessionId, $student, $thesisType, $sessionType, $date, $length, $notes, $notes_next): bool
    {
        try {
            $sql = "UPDATE $this->table 
                SET idStudents = :student, 
                    idThesesTypes = :thesisType, 
                    idSessionTypes = :sessionType, 
                    date = :date, 
                    length = :length, 
                    notes = :notes, 
                    notes_next = :notes_next 
                WHERE idSessions = :sessionId";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_INT);
            $stmt->bindParam(':student', $student);
            $stmt->bindParam(':thesisType', $thesisType, PDO::PARAM_INT);
            $stmt->bindParam(':sessionType', $sessionType, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':length', $length, PDO::PARAM_INT);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':notes_next', $notes_next);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new \RuntimeException('Database operation failed', 0, $e);
        }
    }

    public function getSessionById(int $sessionId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE idSessions = :sessionId");
        $stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentsByFilters(string $startDate, string $endDate, ?string $selectedTeacher = null, ?string $col = null): array
    {
        $query = "SELECT DISTINCT idStudents FROM $this->table WHERE date BETWEEN :startDate AND :endDate";
        $params = [':startDate' => $startDate, ':endDate' => $endDate];

        $allowedColumns = ['idStudents', 'idTeachers'];
        if ($col && in_array($col, $allowedColumns)) {
            if ($col == 'idStudents') {
                $userName = "%" . $_SESSION['user_profile']['mail'];
            } else {
                $userName = $_SESSION['user_profile']['name'] . "%";
            }
            $query .= " AND $col LIKE :userName";
            $params[':userName'] = $userName;
        }
        if ($selectedTeacher) {
            $query .= " AND idTeachers = :selectedTeacher";
            $params[':selectedTeacher'] = $selectedTeacher;
        }
        $query .= " ORDER BY idStudents ASC";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTeachersByFilters(string $startDate, string $endDate, ?string $selectedStudent = null): array
    {
        $query = "SELECT DISTINCT idTeachers FROM $this->table WHERE date BETWEEN :startDate AND :endDate";
        $params = [':startDate' => $startDate, ':endDate' => $endDate];

        if ($selectedStudent) {
            $query .= " AND idStudents = :selectedStudent";
            $params[':selectedStudent'] = $selectedStudent;
        }
        $query .= " ORDER BY idTeachers ASC";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getSessionsByFilters(int $page, int $limit, string $startDate, string $endDate, ?string $selectedStudent = null, ?string $selectedTeacher = null, ?string $col = null): array
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM $this->table WHERE date BETWEEN :startDate AND :endDate";
        $params = [':startDate' => $startDate, ':endDate' => $endDate];

        if ($col) {
            if ($col == 'idStudents') {
                $userName = "%" . $_SESSION['user_profile']['mail'];
            } else {
                $userName = $_SESSION['user_profile']['name'] . "%";
            }
            $query .= " AND $col LIKE :userName";
            $params[':userName'] = $userName;
        }
        if ($selectedStudent) {
            $query .= " AND idStudents = :selectedStudent";
            $params[':selectedStudent'] = $selectedStudent;
        }
        if ($selectedTeacher) {
            $query .= " AND idTeachers = :selectedTeacher";
            $params[':selectedTeacher'] = $selectedTeacher;
        }
        $query .= " ORDER BY idSessions DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($sessions as &$session) {
            $session['date'] = date('d.m.Y', strtotime($session['date']));
        }

        $countQuery = "SELECT COUNT(*) FROM $this->table WHERE date BETWEEN :startDate AND :endDate";
        $countParams = [':startDate' => $startDate, ':endDate' => $endDate];
        if ($col) {
            $countQuery .= " AND $col LIKE :userName";
            $countParams[':userName'] = $userName;
        }
        if ($selectedStudent) {
            $countQuery .= " AND idStudents = :selectedStudent";
            $countParams[':selectedStudent'] = $selectedStudent;
        }
        if ($selectedTeacher) {
            $countQuery .= " AND idTeachers = :selectedTeacher";
            $countParams[':selectedTeacher'] = $selectedTeacher;
        }
        $countStmt = $this->conn->prepare($countQuery);
        foreach ($countParams as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);

        return [
            'sessions' => $sessions,
            'count' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
        ];
    }

    public function deleteSession(int $sessionId): bool
    {
        $query = "DELETE FROM $this->table WHERE idSessions = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $sessionId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}