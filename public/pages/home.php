<?php

use Helpers\SessionTypes;
use Helpers\ThesisTypes;
use Services\SessionService;

require "vendor/autoload.php";

// Kontrola přihlášení a přístupu
if (isset($_SESSION['user_profile'])) {

    // Načtení parametrů z URL
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 12; // Počet záznamů na stránku
    $selectedAcademicYear = $_GET['ac_year'] ?? null;
    $selectedStudent = $_GET['student'] ?? null;

    // Role a omezení přístupu
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
    $totalPages = $data['total_pages'] ?? 1;

    ?>
    <div
        class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-10 my-4 ml-2 sm:ml-0 text-sm sm:text-base">
        <?php if (in_array('utb_zam', $roles)) { ?>
            <a class="button-accent inline-block whitespace-nowrap" href="/handle_session">Přidat nový záznam</a>
        <?php } ?>
        <form method="GET" class="flex items-center justify-evenly gap-2 sm:gap-4 w-full sm:w-fit">
            <!-- Akademický rok -->
            <select class="input sm:w-fit" name="ac_year" onchange="this.form.submit()">
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

            <?php if (in_array('utb_zam', $roles)) { ?>
                <!-- Studenti -->
                <select class="input sm:w-fit" name="student" onchange="this.form.submit()">
                    <option value="">Všichni studenti</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= htmlspecialchars($student) ?>" <?= $selectedStudent === $student ? 'selected' : '' ?>>
                            <?= htmlspecialchars($student) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php } ?>
        </form>
    </div>

    <?php if (!empty($sessions)) { ?>
        <div class="overflow-x-auto rounded-lg">
            <table
                class="min-w-full divide-y-2 divide-gray-200 dark:divide-zinc-600 mt-6 text-sm relative after:absolute after:-top-6 after:h-6 after:inset-x-0 after:bg-linear-to-r after:from-[#3a6179] after:via-[#658295] after:to-[#3a6179]">
                <thead>
                    <tr class="bg-slate-50 dark:bg-[var(--color-fill-secondary)]">
                        <th class="px-4 py-2 font-medium whitespace-nowrap"><?= $col == "idTeachers" ? "Student" : "Vedoucí" ?>
                        </th>
                        <th class="px-4 py-2 font-medium whitespace-nowrap">Typ práce</th>
                        <th class="px-4 py-2 font-medium whitespace-nowrap">Typ konzultace</th>
                        <th class="px-4 py-2 font-medium whitespace-nowrap">Datum</th>
                        <th class="px-4 py-2 font-medium whitespace-nowrap">Délka</th>
                        <th class="px-4 py-2 font-medium whitespace-nowrap">Popis</th>
                        <th class="px-4 py-2 font-medium whitespace-nowrap">Plán</th>
                        <?php if ($col == "idTeachers")
                            echo '<th class="px-4 py-2"></th>'
                                ?>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-600 border-b border-gray-200 dark:border-zinc-600">
                    <?php foreach ($sessions as $session) { ?>
                        <tr
                            class="odd:bg-slate-200 even:bg-slate-50 dark:odd:bg-[var(--color-fill)] dark:even:bg-[var(--color-fill-secondary)]">
                            <td class="px-4 py-2 font-medium whitespace-nowrap text-gray-950 dark:text-[var(--color-text-base)]">
                                <?= $col == "idTeachers" ? htmlspecialchars($session['idStudents']) : htmlspecialchars($session['idTeachers']) ?>
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
                            <?php if ($col == "idTeachers")
                                echo '
                        <td class="px-4 py-2 whitespace-nowrap text-center">
                            <form class="inline-block" action="/handle_session" method="POST">
                                <input type="hidden" name="sessionId" value="' . htmlspecialchars($session['idSessions']) . '">
                                <button
                                    class="rounded-sm bg-gradient-to-b from-[#3c8ce7] to-[#62cff4] text-black px-4 py-2 text-xs font-medium hover:brightness-90 cursor-pointer"
                                    type="submit">Upravit
                                </button>
                            </form>
                            &nbsp;&nbsp;
                            <form class="inline-block" action="" method="POST"
                                onsubmit="return confirm(\'Opravdu chcete smazat tento záznam?\');">
                                <input type="hidden" name="sessionId" value="' . htmlspecialchars($session['idSessions']) . '">
                                <button
                                    class="rounded-sm bg-gradient-to-b from-[#bf2900] to-[#e06b60] px-4 py-2 text-xs text-black font-medium hover:brightness-90 cursor-pointer"
                                    type="submit">Smazat
                                </button>
                            </form>
                        </td>'
                                    ?>
                            </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1) { ?>
            <div class="w-fit px-4 py-2 mx-auto">
                <ol class="flex justify-center gap-1 text-xs font-medium">
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <li>
                            <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'button-accent' : 'button-alt' ?> text-center leading-8">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php } ?>
                </ol>
            </div>
        <?php }
    } else {
        echo '<p class="text-2xl font-bold text-center">Nemáte žádný záznam!</p>';
    }
    ?>

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
} else { ?>
    <div class="w-full h-full flex flex-col justify-center gap-6 max-w-screen-md mx-auto text-center">
        <p class="text-2xl">Konzultace bakalářských a diplomových prací FaME</p>

        <a class="button-alt w-fit mx-auto" href="/callback.php">
            <span class="flex items-center gap-4 h-10">
                <img class="h-full" src="/public/assets/images/ms.ico" alt="" />
                <span>Přihlásit se</span>
            </span>
        </a>
        <p class="text-xs mt-4">V případě problémů kontaktujte IT administrátora FaME na adrese<a
                href="mailto:webmaster@fame.utb.cz" title="IT administrátor FaME"> webmaster@fame.utb.cz</a>.</p>
    </div>
    <?php
}
?>