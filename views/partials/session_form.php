<?php
declare(strict_types=1);

/**
 * @param array $editData
 * @param string $err
 * @param int $sessionId
 */

use Helpers\SessionTypes;
use Helpers\ThesisTypes;

?>

<form action="" method="POST"
    class="flex flex-col m-auto items-center gap-4 w-full max-w-lg *:w-full *:last:w-fit p-4 sm:p-6 rounded-lg overflow-y-auto">

    <input type="hidden" name="sessionId" value="<?= $sessionId ?? "" ?>">

    <div class="relative inline-block">
        <label for="Student" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Student
            <input id="Student" required="" value="<?= htmlspecialchars($editData["idStudents"] ?? '') ?>"
                placeholder="Zadejte jméno studenta (min. 3 znaky)..." class="input" autocomplete="off" type="text"
                name="student" />
            <?php if (!empty($err)): ?>
                <div class="text-center"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>
        </label>
        <div id="searchResults"
            class="absolute bg-white dark:bg-[var(--color-fill-secondary)] top-full inset-x-0 border border-zinc-600 shadow z-10 rounded-sm divide-y-2 divide-gray-200 dark:divide-zinc-600 cursor-pointer *:odd:bg-slate-200 *:even:bg-slate-50 *:dark:odd:bg-[var(--color-fill)] *:dark:even:bg-[var(--color-fill-secondary)] *:px-2 *:py-1 *:first:rounded-t-sm *:last:rounded-b-sm hidden">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label for="ThesisType" class="label">Typ práce
            <select name="thesisType" id="ThesisType" class="input">
                <?php foreach (ThesisTypes::$types as $key => $value): ?>
                    <option value="<?= $key ?>" <?= ($key == ($editData["idThesesTypes"] ?? '')) ? 'selected' : '' ?>>
                        <?= $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="label" for="sessionType">
            Způsob konzultace
            <select class="input" id="sessionType" name="sessionType">
                <?php foreach (SessionTypes::$types as $key => $value): ?>
                    <option value="<?= $key ?>" <?= ($key == ($editData["idSessionTypes"] ?? '')) ? 'selected' : '' ?>>
                        <?= $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label class="label" for="date">
            Datum
            <input class="input" type="date" id="date" name="date"
                value="<?= htmlspecialchars($editData["date"] ?? '') ?>" />
        </label>

        <label class="label" for="length">
            Délka (v minutách)
            <input class="input" type="number" id="length" name="length" min="5" max="120" step="5"
                value="<?= isset($editData['length']) ? $editData['length'] : '15' ?>" />
        </label>
    </div>

    <label for="notes" class="label">Záznam
        <textarea id="notes" name="notes" class="input" rows="6"
            placeholder="Enter any additional order notes..."><?= htmlspecialchars($editData['notes'] ?? '') ?></textarea>
    </label>

    <label class="label" for="notes_next">Plán
        <textarea class="input" name="notes_next" placeholder="Message" rows="6"
            id="notes_next"><?= htmlspecialchars($editData['notes_next'] ?? '') ?></textarea>
    </label>

    <input type="hidden" name="isSubmitted" value="true" />

    <div class="flex gap-8 *:w-32 *:text-center">
        <a href="/" class="button-alt">Zrušit</a>
        <input class="button-accent cursor-pointer" type="submit"
            value="<?= empty($sessionId) ? 'Přidat' : 'Uložit změny' ?>" />
    </div>

</form>
<script src="/public/assets/js/student-search.js"></script>