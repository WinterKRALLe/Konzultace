<?php

use Helpers\RoleAccess;
use Helpers\SessionTypes;
use Helpers\ThesisTypes;
use Services\SessionService;

require "vendor/autoload.php";

RoleAccess::checkRoleAccess();

$sessionId = $_POST['sessionId'] ?? '';
$editData = [];
$err = '';

// Získání dat pro update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($sessionId)) {
    $sessionService = new SessionService();
    $editData = $sessionService->getSessionById($sessionId);
}

// POST pro update / vytvoření dat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmitted'])) {
    $student = $_POST['student'] ?? null;
    $thesisType = $_POST['thesisType'] ?? null;
    $sessionType = $_POST['sessionType'] ?? null;
    $date = $_POST['date'] ?? null;
    $length = $_POST['length'] ?? null;
    $notes = $_POST['notes'] ?? null;
    $notes_next = $_POST['notes_next'] ?? null;

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
                $identifier = trim($parts[1]);
                $regex = '/^([a-z])(?:[a-z]*\d*)?_([a-z]+)$/i';
                if (!preg_match($regex, $identifier)) {
                    $err = "Dodržte tvar Jméno Příjmení - j_prijmeni";
                } else {
                    $sessionService = new SessionService();
                    if (!empty($sessionId)) {
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
    }
}
?>

<form action="" method="POST"
      class="flex flex-col justify-center m-auto items-center gap-4 w-full max-w-lg *:w-full *:last:w-fit p-4 sm:p-6 rounded-xl bg-slate-50/90 dark:bg-[var(--color-fill-secondary)]/90">

    <input type="hidden" name="sessionId" value="<?= htmlspecialchars($sessionId ?? '') ?>">

    <div class="relative inline-block">
        <label for="Student" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Student
            <input id="Student"
                   required=""
                   value="<?= htmlspecialchars($editData["idStudents"]) ?? '' ?>"
                   placeholder="Zadejte jméno studenta (min. 3 znaky)..."
                   class="input"
                   autocomplete="off"
                   type="text" name="student"/>
            <?php if (!empty($err)): ?>
                <div class="text-center"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>
        </label>
        <div id="searchResults"
             class="absolute bg-white dark:bg-[var(--color-fill-secondary)] top-full inset-x-0 border border-zinc-600 shadow z-10 rounded-sm divide-y divide-y-2 divide-gray-200 dark:divide-zinc-600 cursor-pointer *:odd:bg-slate-200 *:even:bg-slate-50 *:dark:odd:bg-[var(--color-fill)] *:dark:even:bg-[var(--color-fill-secondary)] *:px-2 *:py-1 *:first:rounded-t-sm *:last:rounded-b-sm hidden"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label for="ThesisType" class="label">Typ práce
            <select name="thesisType" id="ThesisType" class="input">
                <?php foreach (ThesisTypes::$types as $key => $value): ?>
                    <option value="<?= $key ?>" <?= $key == $editData["idThesesTypes"] ? 'selected' : '' ?>>
                        <?= $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="label" for="sessionType">
            Způsob konzultace
            <select class="input" id="sessionType" name="sessionType">
                <?php foreach (SessionTypes::$types as $key => $value): ?>
                    <option value="<?= $key ?>" <?= $key == $editData["idSessionTypes"] ? 'selected' : '' ?>>
                        <?= $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label class="label" for="date">
            Datum
            <input class="input" type="date" id="date" name="date" value="<?= htmlspecialchars($editData["date"]) ?>"/>
        </label>

        <label class="label" for="length">
            Délka (v minutách)
            <input class="input" type="number" id="length" name="length" min="5" max="120" step="5"
                   value="<?= isset($editData['length']) ? htmlspecialchars($editData['length']) : '15' ?>"/>
        </label>
    </div>

    <label for="notes" class="label">Záznam
        <textarea id="notes" name="notes" class="input" rows="6"
                  placeholder="Enter any additional order notes..."><?= htmlspecialchars($editData['notes']) ?></textarea>
    </label>

    <label class="label" for="notes_next">Plán
        <textarea class="input" name="notes_next" placeholder="Message" rows="6"
                  id="notes_next"><?= htmlspecialchars($editData['notes_next']) ?></textarea>
    </label>

    <input type="hidden" name="isSubmitted" value="true"/>

    <div class="flex gap-8 *:w-32 *:text-center">
        <a href="/" class="button-alt">Zrušit</a>
        <input class="button-accent cursor-pointer" type="submit"
               value="<?= empty($sessionId) ? 'Přidat' : 'Uložit změny' ?>"/>
    </div>

</form>

<script lang="js">
    const searchInput = document.getElementById('Student');
    const searchResults = document.getElementById('searchResults');
    let debounceTimer;

    const searchUsers = async (query) => {
        if (query.length < 3) {
            searchResults.style.display = 'none';
            return;
        }

        searchResults.style.display = 'block';
        searchResults.innerHTML = 'Vyhledávám...';

        try {
            const response = await fetch(`search.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.error) {
                if (data.error.includes('přihlaste se znovu')) {
                    window.location.href = 'callback.php';
                    return;
                }
                searchResults.innerHTML = data.error;
                return;
            }

            if (data.users.length === 0) {
                searchResults.innerHTML = 'Žádní studenti nenalezeni';
                return;
            }

            searchResults.innerHTML = data.users.map(user => `
            <div class="search-item hover:bg-slate-300">
                <div>${user.displayName} - <span class="text-sm text-gray-600 dark:text-[var(--color-text-muted)]">${user.email.split('@')[0]}</span></div>
            </div>
        `).join('');

        } catch (error) {
            searchResults.innerHTML = 'Chyba při vyhledávání';
            console.error('Search error:', error);
        }
    };

    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            searchUsers(e.target.value);
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    searchResults.addEventListener('click', (e) => {
        const searchItem = e.target.closest('.search-item');
        if (searchItem) {
            searchInput.value = searchItem.querySelector('div').textContent;
            searchResults.style.display = 'none';
        }
    });
</script>