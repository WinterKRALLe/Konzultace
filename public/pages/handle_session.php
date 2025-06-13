<?php
declare(strict_types=1);

use Helpers\RoleAccess;
use Services\SessionService;

require "vendor/autoload.php";

RoleAccess::checkRoleAccess();

$sessionId = (int) ($_POST['sessionId'] ?? 0);

$editData = [];
$err = '';

$sessionService = new SessionService();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sessionId > 0 && !isset($_POST['isSubmitted'])) {
    $editData = $sessionService->getSessionById($sessionId);
}

// POST pro update / vytvoření dat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmitted'])) {
    $student = $_POST['student'] ?? null;
    $thesisType = $_POST['thesisType'] ?? null;
    $sessionType = $_POST['sessionType'] ?? null;
    $date = $_POST['date'] ?? null;
    $length = $_POST['length'] ?? null;
    $notes = $_POST['notes'] ?? '';
    $notes_next = $_POST['notes_next'] ?? '';

    // Kontrola povinných polí
    if (!$student || !$thesisType || !$sessionType || !$date || !$length) {
        $err = "Chybí povinné údaje!";
    } else {
        // Kontrola správného formátu vstupu - musí obsahovat pomlčku s formátem j_prijmeni
        if (!str_contains($student, " - ")) {
            $err = "Dodržte tvar Jméno Příjmení - j_prijmeni";
        } else {
            $parts = explode(" - ", $student);
            if (count($parts) !== 2) {
                $err = "Dodržte tvar Jméno Příjmení - j_prijmeni";
            } else {
                if ($sessionId > 0) {
                    $result = $sessionService->updateSession($sessionId, $student, $thesisType, $sessionType, $date, $length, $notes, $notes_next);
                } else {
                    $result = $sessionService->createSession($student, $thesisType, $sessionType, $date, $length, $notes, $notes_next);
                }

                if ($result) {
                    header('Location: /');
                    exit();
                } else {
                    $err = "Chyba při přidávání záznamu!";
                }
            }
        }
    }
    // Pokud došlo k chybě, data se předvyplní do formuláře
    $editData = [
        "idStudents" => $student,
        "idThesesTypes" => $thesisType,
        "idSessionTypes" => $sessionType,
        "date" => $date,
        "length" => $length,
        "notes" => $notes,
        "notes_next" => $notes_next,
    ];
}

// Zahrnutí komponenty pro formulář konzultace
include __DIR__ . '/../../views/partials/session_form.php';