<?php
declare(strict_types=1);

use Helpers\RoleAccess;
use Services\SessionService;

require "vendor/autoload.php";

if (!isset($_SESSION['user_profile'])) {
    header("Location: /");
    exit;
}
RoleAccess::checkRoleAccess();
if (!RoleAccess::checkNameAccess()) {
    http_response_code(403);
    exit('Nemáš oprávnění k přístupu. <a href="/">Domů</a>');
}

// Načtení parametrů z URL
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 20; // Počet záznamů na stránku
$selectedAcademicYear = $_GET['ac_year'] ?? null;
$selectedStudent = $_GET['student'] ?? null;
$selectedTeacher = $_GET['teacher'] ?? null;

// Role a omezení přístupu
$roles = $_SESSION['user_profile']['roles'] ?? [];
$col = "idTeachers"; // V adminu vždy filtrujeme podle učitelů pro akce

// Výchozí akademický rok
$currentYear = (int) date('Y');
if ((int) date('n') <= 8) {
    $currentYear--;
}
$defaultAcademicYear = sprintf('%d-%d', $currentYear, $currentYear + 1);
$selectedAcademicYear = $selectedAcademicYear ?: $defaultAcademicYear;
[$yearStart, $yearEnd] = explode(
    '-',
    $selectedAcademicYear
);
$startDate = sprintf('%d-09-01', (int) $yearStart);
$endDate = sprintf('%d-08-31', (int) $yearEnd); // Inicializace služby
$sessionService = new SessionService(); // Načtení studentů a vedoucích podle filtrů
$students = $sessionService->getStudentsByFilters(
    startDate: $startDate,
    endDate: $endDate,
    selectedTeacher: $selectedTeacher
);
$teachers = $sessionService->getTeachersByFilters(startDate: $startDate, endDate: $endDate, selectedStudent: $selectedStudent);

// Načtení konzultací podle filtrů
$data = $sessionService->getSessionsByFilters(page: $page, limit: $limit, startDate: $startDate, endDate: $endDate, selectedStudent: $selectedStudent, selectedTeacher: $selectedTeacher);
$sessions = $data['sessions'] ?? [];
$count = $data['count'];
$totalPages = $data['total_pages'] ?? 1;

// Zahrnutí komponenty pro zobrazení seznamu konzultací
include 'views/partials/session_list.php';