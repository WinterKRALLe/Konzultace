<?php
declare(strict_types=1);

/**
 * @param array $sessions
 * @param int $count
 * @param int $totalPages
 * @param int $page
 * @param string $selectedAcademicYear
 * @param array $students
 * @param array|null $teachers
 * @param string $col
 * @param int $currentYear
 */

use Helpers\RoleAccess;
use Helpers\SessionTypes;
use Helpers\ThesisTypes;
use Services\SessionService;

// Funkce pro generování URL paginace
function getPageUrl(int $pageNum): string
{
    $params = $_GET;
    $params['page'] = $pageNum;
    return '?' . http_build_query($params);
}

// Zpracování mazání záznamu (pokud je povoleno a je to POST požadavek)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['sessionId']) && $col === "idTeachers") {
    $sessionService = new SessionService();
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

<div
    class="flex flex-col md:flex-row items-start sm:items-center gap-4 sm:gap-10 py-2 px-2 xl:px-4 text-sm sm:text-base border-y border-[var(--border-color)] w-full justify-between">
    <form method="GET" class="flex flex-wrap sm:flex-nowrap gap-4 items-center h-full w-fit" id="filterForm">
        <select class="input w-fit" name="ac_year" onchange="resetFiltersAndSubmit()">
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

        <?php if (RoleAccess::hasRoleAccess()) { ?>
            <select class="input w-fit" name="student" onchange="this.form.submit()">
                <option value="">Všichni studenti</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student) ?>" <?= ($selectedStudent ?? null) === $student ? 'selected' : '' ?>>
                        <?= htmlspecialchars($student) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php } ?>

        <?php if (isset($teachers) && is_array($teachers)) { ?>
            <select class="input w-fit" name="teacher" onchange="this.form.submit()">
                <option value="">Všichni vedoucí</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= htmlspecialchars($teacher) ?>" <?= ($selectedTeacher ?? null) === $teacher ? 'selected' : '' ?>>
                        <?= htmlspecialchars($teacher) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php } ?>
    </form>

    <script>
        function resetFiltersAndSubmit() {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const currentParams = new URLSearchParams(window.location.search);

            // Získat aktuálně vybraný akademický rok z formuláře
            const selectedAcYear = formData.get('ac_year');

            // Vytvořit nové URL parametry
            const newParams = new URLSearchParams();

            // Přidat pouze akademický rok
            if (selectedAcYear) {
                newParams.set('ac_year', selectedAcYear);
            }

            // Přidat parametr 'page' pokud existuje a není 1 (aby se udržela paginace)
            const currentPage = currentParams.get('page');
            if (currentPage && currentPage !== '1') {
                newParams.set('page', currentPage);
            }

            // Přesměrovat na novou URL
            window.location.search = newParams.toString();
        }
    </script>
    <div class="flex items-center justify-between lg:justify-end h-full w-full md:w-fit whitespace-nowrap gap-4">
        <?php if ($count > 0): ?>
            <?php
            $countText = htmlspecialchars((string) $count, ENT_QUOTES);

            // Určujeme správný tvar slova "záznam"
            if ($count == 1) {
                $word = 'záznam';
            } elseif ($count >= 2 && $count <= 4) {
                $word = 'záznamy';
            } else {
                $word = 'záznamů';
            }
            ?>
            <?= $countText . ' ' . $word ?>
        <?php endif; ?>


        <?php if ($totalPages > 1) { ?>
            <div class="w-fit" id="pagination">
                <ol class="flex justify-center items-center font-medium whitespace-nowrap">
                    <li class=" hover:bg-[var(--bg-weak)]">
                        <a href="<?= getPageUrl(pageNum: $page - 1) ?>"
                            class="block py-1 px-3 rounded-xl hover:bg-[var(--bg-weak)] border border-transparent hover:border-[var(--border-color)]<?php echo ($page > 1) ? "" : "pointer-events-none" ?>">&lt;</a>
                    </li>

                    <li class="relative">
                        <button class="cursor-pointer w-14 text-center" onclick="toggleDropdown()"><?= $page ?> /
                            <?= $totalPages ?></button>
                        <div id="pageDropdown"
                            class="hidden left-1/2 -translate-x-1/2 absolute z-10 rounded-md border border-zinc-600 shadow-xs p-2 w-20 text-center max-h-48 overflow-y-auto">
                            <ul class="py-1">
                                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                                    <li>
                                        <a href="<?= getPageUrl($i) ?>" class="block px-4 py-2 text-xs"><?= $i ?></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <a href="<?= getPageUrl(pageNum: $page + 1) ?>"
                            class="block py-1 px-3 rounded-xl hover:bg-[var(--bg-weak)] border border-transparent hover:border-[var(--border-color)] <?php echo ($page < $totalPages) ? "" : "pointer-events-none" ?>">&gt;</a>
                    </li>
                </ol>
            </div>
            <script>
                function toggleDropdown() {
                    const dropdown = document.getElementById('pageDropdown');
                    dropdown.classList.toggle('hidden');
                }

                // Close dropdown when clicking outside
                document.addEventListener('click', function (event) {
                    const dropdown = document.getElementById('pageDropdown');
                    const button = dropdown.previousElementSibling;
                    if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            </script>
        <?php } ?>
    </div>
</div>

<?php if (!empty($sessions)) { ?>
    <div class="flex-1 overflow-auto flex w-full relative">
        <div class="overflow-y-auto w-full lg:w-3/5 border-r border-[var(--border-color)]">
            <ul class="w-full text-sm divide-y divide-[var(--border-color)]">
                <?php foreach ($sessions as $session) { ?>
                    <li class="bg-strong *:px-2 *:py-2 lg:*:px-4 flex flex-nowrap w-full *:flex max-lg:flex-row *:flex-col *:justify-around session-item relative hover:z-10 hover:shadow-[0_2px_12px_rgba(0,0,0,1)]! hover:bg-[var(--bg-weak)]! cursor-pointer"
                        data-notes="<?= htmlspecialchars($session['notes']) ?>"
                        data-notes-next="<?= htmlspecialchars($session['notes_next']) ?>"
                        data-thesis-type="<?= htmlspecialchars(ThesisTypes::getById((string) $session['idThesesTypes'])) ?>"
                        data-thesis-type-short="<?= htmlspecialchars(ThesisTypes::getByIdShort((string) $session['idThesesTypes'])) ?>"
                        data-date="<?= htmlspecialchars($session['date']) ?>"
                        data-session-type="<?= htmlspecialchars(SessionTypes::getById((string) $session['idSessionTypes'])) ?>"
                        data-length="<?= htmlspecialchars((string) $session['length']) ?>"
                        data-session-id="<?= htmlspecialchars((string) $session['idSessions']) ?>">
                        <div
                            class="max-lg:hidden w-1/4 border-r-2 border-[var(--border-color)] whitespace-nowrap text-weak pr-0! flex flex-col justify-around">
                            <p
                                class="<?= (htmlspecialchars((string) $session['idThesesTypes']) === "1") ? 'bg-[#8838ff]' : 'bg-[#2fd6b5]' ?> rounded-l-full max-lg:py-0.5 max-lg:px-1 py-1 px-2 text-sm lg:text-base text-norm">
                                <?= htmlspecialchars(ThesisTypes::getById((string) $session['idThesesTypes'])) ?>
                            </p>
                            <p class="pl-2 text-xs lg:text-sm"><?= htmlspecialchars($session['date']) ?></p>
                        </div>
                        <div class="lg:hidden">
                            <span
                                class="<?= (htmlspecialchars((string) $session['idThesesTypes']) === "1") ? 'bg-[#8838ff]' : 'bg-[#2fd6b5]' ?> p-1 rounded-lg">
                                <?= htmlspecialchars(ThesisTypes::getByIdShort((string) $session['idThesesTypes'])) ?>
                            </span>
                        </div>
                        <div class="w-full font-medium whitespace-nowrap">
                            <p class="text-sm lg:text-base"><?= htmlspecialchars($session['idStudents']) ?></p>
                            <div class="flex w-full justify-between">
                                <p class="text-xs lg:text-sm text-weak">
                                    <?= htmlspecialchars($session['idTeachers']) ?>
                                </p>
                                <p class="lg:hidden pl-2 text-xs lg:text-sm"><?= htmlspecialchars($session['date']) ?></p>
                            </div>
                        </div>
                        <div class="max-lg:hidden block w-1/4 whitespace-nowrap text-weak text-center">
                            <p><?= htmlspecialchars(SessionTypes::getById((string) $session['idSessionTypes'])) ?></p>
                            <p><?= htmlspecialchars((string) $session['length']) ?> min.</p>
                        </div>
                        <?php if ($col == "idTeachers") { ?>
                            <div class="max-xl:hidden w-1/4 flex flex-row! items-center gap-4 whitespace-nowrap text-center">
                                <form class="inline-block" action="/handle_session" method="POST">
                                    <input type="hidden" name="sessionId"
                                        value="<?= htmlspecialchars((string) $session['idSessions']) ?>">
                                    <button
                                        class="rounded-sm bg-gradient-to-b from-[#3c8ce7] to-[#62cff4] text-black px-4 py-2 text-xs font-medium hover:brightness-90 cursor-pointer"
                                        type="submit">Upravit</button>
                                </form>
                                <form class="inline-block" action="" method="POST"
                                    onsubmit="return confirm('Opravdu chcete smazat tento záznam?');">
                                    <input type="hidden" name="sessionId"
                                        value="<?= htmlspecialchars((string) $session['idSessions']) ?>">
                                    <button
                                        class="rounded-sm bg-gradient-to-b from-[#bf2900] to-[#e06b60] px-4 py-2 text-xs text-black font-medium hover:brightness-90 cursor-pointer"
                                        type="submit">Smazat</button>
                                </form>
                            </div>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="hidden w-2/5 h-full lg:flex flex-col justify-center items-center p-4" id="session-details">
            <div class="text-lg font-medium">Vyberte konzultaci</div>
            <div class="text-weak">Poznámky a další informace se zobrazí zde.</div>
        </div>
        <div id="mobile-details"
            class="lg:hidden fixed bottom-0 left-0 w-full bg-[var(--bg-strong)] h-screen overflow-y-auto transform translate-y-full transition-transform duration-300 ease-in-out z-20">
            <div
                class="flex justify-between items-center p-4 border-b border-[var(--border-color)] sticky top-0 bg-[var(--bg-strong)]">
                <div class="text-lg font-medium">Detail konzultace</div>
                <button id="close-mobile-details" class="text-3xl p-1">×</button>
            </div>
            <div id="mobile-details-content" class="p-4 flex flex-col gap-4 *:flex *:gap-2">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sessionItems = document.querySelectorAll('.session-item');
            const detailsDiv = document.getElementById('session-details');
            const mobileDetails = document.getElementById('mobile-details');
            const mobileDetailsContent = document.getElementById('mobile-details-content');
            const closeMobileDetails = document.getElementById('close-mobile-details');

            sessionItems.forEach(item => {
                item.addEventListener('click', () => {
                    // Remove 'selected' class from all items
                    sessionItems.forEach(i => i.classList.remove('selected'));
                    // Add 'selected' class to clicked item
                    item.classList.add('selected');

                    // Get data from data attributes
                    const notes = item.dataset.notes || 'Žádné poznámky';
                    const notesNext = item.dataset.notesNext || 'Žádné další poznámky';
                    const thesisType = item.dataset.thesisType || item.dataset.thesisTypeShort;
                    const date = item.dataset.date;
                    const sessionType = item.dataset.sessionType;
                    const length = item.dataset.length;
                    const sessionId = item.dataset.sessionId;

                    // Update desktop details div
                    detailsDiv.innerHTML = `
                        <div class="text-lg font-medium">Poznámky</div>
                        <div class="text-weak text-justify">${notes}</div>
                        <div class="text-lg font-medium mt-4">Další kroky</div>
                        <div class="text-weak text-justify">${notesNext}</div>
                        <?php if ($col == "idTeachers") { ?>
                        <div class="xl:hidden justify-center gap-4">
                            <form class="inline-block" action="/handle_session" method="POST">
                                <input type="hidden" name="sessionId" value="${sessionId}">
                                <button class="rounded-sm bg-gradient-to-b from-[#3c8ce7] to-[#62cff4] text-black px-4 py-2 text-xs font-medium hover:brightness-90 cursor-pointer" type="submit">Upravit</button>
                            </form>
                            <form class="inline-block" action="" method="POST" onsubmit="return confirm('Opravdu chcete smazat tento záznam?');">
                                <input type="hidden" name="sessionId" value="${sessionId}">
                                <button class="rounded-sm bg-gradient-to-b from-[#bf2900] to-[#e06b60] px-4 py-2 text-xs text-black font-medium hover:brightness-90 cursor-pointer" type="submit">Smazat</button>
                            </form>
                        </div>
                        <?php } ?>
                    `;

                    // Update mobile details div and show it
                    mobileDetailsContent.innerHTML = `
                        <div class="">
                            <p class="font-medium">Typ práce:</p>
                            <p>${thesisType}</p>
                        </div>
                        <div class="">
                            <p class="font-medium">Datum:</p>
                            <p>${date}</p>
                        </div>
                        <div class="">
                            <p class="font-medium">Typ konzultace:</p>
                            <p>${sessionType}</p>
                        </div>
                        <div class="">
                            <p class="font-medium">Délka:</p>
                            <p>${length} min.</p>
                        </div>
                        <div class="flex-col">
                            <p class="font-medium">Poznámky:</p>
                            <p class="text-weak text-justify">${notes}</p>
                        </div>
                        <div class="flex-col">
                            <p class="font-medium">Další kroky:</p>
                            <p class="text-weak text-justify">${notesNext}</p>
                        </div>
                        <?php if ($col == "idTeachers") { ?>
                        <div class="justify-center gap-4">
                            <form class="inline-block" action="/handle_session" method="POST">
                                <input type="hidden" name="sessionId" value="${sessionId}">
                                <button class="rounded-sm bg-gradient-to-b from-[#3c8ce7] to-[#62cff4] text-black px-4 py-2 text-xs font-medium hover:brightness-90 cursor-pointer" type="submit">Upravit</button>
                            </form>
                            <form class="inline-block" action="" method="POST" onsubmit="return confirm('Opravdu chcete smazat tento záznam?');">
                                <input type="hidden" name="sessionId" value="${sessionId}">
                                <button class="rounded-sm bg-gradient-to-b from-[#bf2900] to-[#e06b60] px-4 py-2 text-xs text-black font-medium hover:brightness-90 cursor-pointer" type="submit">Smazat</button>
                            </form>
                        </div>
                        <?php } ?>
                    `;
                    mobileDetails.classList.remove('translate-y-full');
                });
            });

            // Close mobile details
            closeMobileDetails.addEventListener('click', () => {
                mobileDetails.classList.add('translate-y-full');
            });
        });
    </script>
<?php } else { ?>
    <p class="text-2xl font-bold text-center">Žádný záznam!</p>
<?php } ?>