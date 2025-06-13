<?php
declare(strict_types=1);

use Services\SessionService;

require "vendor/autoload.php";

// Kontrola přihlášení a přístupu
if (isset($_SESSION['user_profile'])) {

    // Načtení parametrů z URL
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 10; // Počet záznamů na stránku
    $selectedAcademicYear = $_GET['ac_year'] ?? null;
    $selectedStudent = $_GET['student'] ?? null;

    // Role a omezení přístupu
    $_SESSION["user_profile"]["roles"] = ["utb_zam"]; // Ponecháno pro demonstraci, ve skutečné aplikaci by role přišly z autentizace
    $roles = $_SESSION['user_profile']['roles'] ?? [];
    $utb_zam = in_array('utb_zam', $roles);
    $col = $utb_zam ? "idTeachers" : "idStudents";

    // Výchozí akademický rok
    $currentYear = (int) date('Y');
    if ((int) date('n') <= 8) {
        $currentYear--;
    }
    $defaultAcademicYear = sprintf('%d-%d', $currentYear, $currentYear + 1);
    $selectedAcademicYear = $selectedAcademicYear ?: $defaultAcademicYear;

    [$yearStart, $yearEnd] = explode('-', $selectedAcademicYear);
    $startDate = sprintf('%d-09-01', (int) $yearStart);
    $endDate = sprintf('%d-08-31', (int) $yearEnd);

    $sessionService = new SessionService();
    $students = $sessionService->getStudentsByFilters(startDate: $startDate, endDate: $endDate, col: $col);

    // Načtení konzultací podle filtrů
    $data = $sessionService->getSessionsByFilters(page: $page, limit: $limit, startDate: $startDate, endDate: $endDate, selectedStudent: $selectedStudent, col: $col);
    $sessions = $data['sessions'] ?? [];
    $count = $data['count'];
    $totalPages = $data['total_pages'] ?? 1;

    // Zahrnutí komponenty pro zobrazení seznamu konzultací
    include 'views/partials/session_list.php';
} else { ?>
    <div class="w-full h-full flex flex-col justify-center mx-auto text-center max-w-screen-md">
        <div
            class="w-full h-auto bg-[rgba(255,255,255,.1) dark:bg-[rgba(0,0,0,.1) backdrop-blur-xs shadow-xs dark:shadow-white flex flex-col justify-center gap-6 p-8 rounded-lg">
            <p class="text-3xl font-exo">Konzultace <span class="text-[#64b9e4]">FaME</span></p>
            <p class="text-2xl">Evidence konzultací bakalářských a diplomových prací</p>

            <a class="button-alt w-fit mx-auto" href="/callback.php">
                <span class="flex items-center gap-4 h-10">
                    <img class="h-full" src="/public/assets/images/ms.ico" alt="" />
                    <span>Přihlásit se</span>
                </span>
            </a>
            <div class="flex flex-col gap-2">
                <p class="text-xs mt-4">V případě problémů kontaktujte IT administrátora FaME.</p>
                <p class="text-xs">Externisté, žádejte přístup kontaktováním IT administrátora FaME.</p>
                <a class="text-xs" href="mailto:webmaster@fame.utb.cz" title="IT administrátor FaME">
                    webmaster@fame.utb.cz</a>
            </div>
        </div>
    </div>
    <?php
}
?>