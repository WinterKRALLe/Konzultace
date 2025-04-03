<?php

use Helpers\RoleAccess;
use Helpers\SessionTypes;
use Helpers\ThesisTypes;
use Services\SessionService;

require "vendor/autoload.php";

// Kontrola přihlášení a přístupu
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
$limit = 12; // Počet záznamů na stránku
$selectedAcademicYear = $_GET['ac_year'] ?? null;
$selectedStudent = $_GET['student'] ?? null;
$selectedTeacher = $_GET['teacher'] ?? null;

// Role a omezení přístupu
$roles = $_SESSION['user_profile']['roles'] ?? [];

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

// Inicializace služby
$sessionService = new SessionService();

// Načtení studentů a vedoucích podle filtrů
$students = $sessionService->getStudentsByFilters(startDate: $startDate, endDate: $endDate, selectedTeacher: $selectedTeacher);
$teachers = $sessionService->getTeachersByFilters(startDate: $startDate, endDate: $endDate, selectedStudent: $selectedStudent);

// Načtení konzultací podle filtrů
$data = $sessionService->getSessionsByFilters(page: $page, limit: $limit, startDate: $startDate, endDate: $endDate, selectedStudent: $selectedStudent, selectedTeacher: $selectedTeacher);
$sessions = $data['sessions'] ?? [];
$totalPages = $data['total_pages'] ?? 1;

function getPageUrl($pageNum)
{
    $params = $_GET;
    $params['page'] = $pageNum;
    return '?' . http_build_query($params) . '#pagination';
}
?>

<div
    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-10 my-4 ml-2 sm:ml-0 text-sm sm:text-base">
    <a class="button-accent inline-block whitespace-nowrap" href="/handle_session">Přidat nový záznam</a>
    <form method="GET" class="flex flex-wrap sm:flex-nowrap items-center gap-4">
        <!-- Akademický rok -->
        <select class="input w-fit" name="ac_year" onchange="this.form.submit()">
            <?php
            $startYear = 2022;
            for ($year = $currentYear; $year >= $startYear; $year--):
                $academicYearOption = sprintf('%d-%d', $year, $year + 1);
                $selected = ($academicYearOption === $selectedAcademicYear) ? 'selected' : '';
                ?>
                <option value="<?= htmlspecialchars($academicYearOption) ?>" <?= $selected ?>>
                    <?= $year . ' - ' . ($year + 1) ?>
                </option>
            <?php endfor; ?>
        </select>

        <!-- Studenti -->
        <select class="input w-fit" name="student" onchange="this.form.submit()">
            <option value="">Všichni studenti</option>
            <?php foreach ($students as $student): ?>
                <option value="<?= htmlspecialchars($student) ?>" <?= $selectedStudent === $student ? 'selected' : '' ?>>
                    <?= htmlspecialchars($student) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Vedoucí -->
        <select class="input w-fit" name="teacher" onchange="this.form.submit()">
            <option value="">Všichni vedoucí</option>
            <?php foreach ($teachers as $teacher): ?>
                <option value="<?= htmlspecialchars($teacher) ?>" <?= $selectedTeacher === $teacher ? 'selected' : '' ?>>
                    <?= htmlspecialchars($teacher) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (!empty($sessions)) { ?>
    <div class="overflow-x-auto rounded-lg">
        <table
            class="min-w-full divide-y-2 divide-gray-200 dark:divide-zinc-600 mt-6 text-sm relative after:absolute after:-top-6 after:h-6 after:inset-x-0 after:bg-linear-to-r after:from-[#3a6179] after:via-[#658295] after:to-[#3a6179]">
            <thead>
                <tr class="bg-slate-50 dark:bg-[var(--color-fill-secondary)]">
                    <th class="px-4 py-2 font-medium whitespace-nowrap">Student</th>
                    <th class="px-4 py-2 font-medium whitespace-nowrap">Vedoucí</th>
                    <th class="px-4 py-2 font-medium whitespace-nowrap">Typ práce</th>
                    <th class="px-4 py-2 font-medium whitespace-nowrap">Typ konzultace</th>
                    <th class="px-4 py-2 font-medium whitespace-nowrap">Datum</th>
                    <th class="px-4 py-2 font-medium whitespace-nowrap">Délka</th>
                    <th class="px-4 py-2 font-medium whitespace-nowrap">Popis</th>
                    <th class="px-4 py-2 font-medium whitespace-nowrap">Plán</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-600 border-b border-gray-200 dark:border-zinc-600">
                <?php foreach ($sessions as $session) { ?>
                    <tr
                        class="odd:bg-slate-200 even:bg-slate-50 dark:odd:bg-[var(--color-fill)] dark:even:bg-[var(--color-fill-secondary)]">
                        <td class="px-4 py-2 font-medium whitespace-nowrap text-gray-950 dark:text-[var(--color-text-base)]">
                            <?= htmlspecialchars($session['idStudents']) ?>
                        </td>
                        <td class="px-4 py-2 font-medium whitespace-nowrap text-gray-950 dark:text-[var(--color-text-base)]">
                            <?= htmlspecialchars($session['idTeachers']) ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-gray-600 dark:text-[var(--color-text-muted)] text-center">
                            <?= htmlspecialchars(ThesisTypes::getById($session['idThesesTypes'])) ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-gray-600 dark:text-[var(--color-text-muted)] text-center">
                            <?= htmlspecialchars(SessionTypes::getById($session['idSessionTypes'])) ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-gray-600 dark:text-[var(--color-text-muted)] text-center">
                            <?= htmlspecialchars($session['date']) ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-gray-600 dark:text-[var(--color-text-muted)] text-center">
                            <?= htmlspecialchars($session['length']) ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-gray-600 dark:text-[var(--color-text-muted)]">
                            <?= htmlspecialchars($session['notes']) ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-gray-600 dark:text-[var(--color-text-muted)]">
                            <?= htmlspecialchars($session['notes_next']) ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-center">
                            <form class="inline-block" action="/handle_session" method="POST">
                                <input type="hidden" name="sessionId" value="<?= htmlspecialchars($session['idSessions']) ?>">
                                <button
                                    class="rounded-sm bg-gradient-to-b from-[#3c8ce7] to-[#62cff4] text-black px-4 py-2 text-xs font-medium hover:brightness-90 cursor-pointer"
                                    type="submit">Upravit
                                </button>
                            </form>
                            &nbsp;&nbsp;
                            <form class="inline-block" action="" method="POST"
                                onsubmit="return confirm('Opravdu chcete smazat tento záznam?');">
                                <input type="hidden" name="sessionId" value="<?= htmlspecialchars($session['idSessions']) ?>">
                                <button
                                    class="rounded-sm bg-gradient-to-b from-[#bf2900] to-[#e06b60] px-4 py-2 text-xs text-black font-medium hover:brightness-90 cursor-pointer"
                                    type="submit">Smazat
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1) { ?>
        <div class="w-fit my-2 px-4 py-2 mx-auto" id="pagination">
            <ol class="flex justify-center gap-1 text-xs font-medium">
                <?php
                // První stránka
                if ($page > 2) { ?>
                    <li>
                        <a href="<?= getPageUrl(1) ?>" class="button-alt">1</a>
                    </li>
                    <?php if ($page > 3) { ?>
                        <li class="pt-2">...</li>
                    <?php }
                }

                // Rozsah stránek kolem aktuální
                for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++) {
                    if ($i == $page) { ?>
                        <li>
                            <span class="button-accent"><?= $i ?></span>
                        </li>
                    <?php } else { ?>
                        <li>
                            <a href="<?= getPageUrl($i) ?>" class="button-alt"><?= $i ?></a>
                        </li>
                    <?php }
                }

                // Poslední stránka
                if ($page < $totalPages - 1) {
                    if ($page < $totalPages - 2) { ?>
                        <li class="pt-2">...</li>
                    <?php } ?>
                    <li>
                        <a href="<?= getPageUrl($totalPages) ?>" class="button-alt"><?= $totalPages ?></a>
                    </li>
                <?php } ?>
            </ol>
        </div>
    <?php }

} else {
    echo '<p class="text-2xl font-bold text-center">Žádný záznam!</p>';
} ?>


<?php
// Mazání záznamu
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sessionId = (int) $_POST['sessionId'];
    $result = $sessionService->deleteSession($sessionId);
    if ($result) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        echo "Chyba při mazání!";
    }
}
?>