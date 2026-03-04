<?php

namespace App\Services;

use Illuminate\Http\Request;

class ScoreCalculatorService
{
    /**
     * @param Request $request
     * @return array
     */
    public function handle (Request $request): array
    {
        $erettsegiEredmenyek = $request['erettsegi-eredmenyek'];
        $tobbletpontok = $request['tobbletpontok'];
        $kovetelmenyek = $this->getKovetelmenyek($request['valasztott-szak']);

        // Kötelező tárgyak ellenőrzése
        if (!$this->validateRequiredSubjects($erettsegiEredmenyek, $kovetelmenyek)) {
            return $this->failResponse(
                "Hiba, nem lehetséges a pontszámítás a kötelező érettségi tárgyak hiánya miatt."
            );
        }

        // Minimum 20%
        $meetsMinimumScoreRequirement = $this->meetsMinimumScoreRequirement($erettsegiEredmenyek);
        if (!$meetsMinimumScoreRequirement['passed']) {
            return $this->failResponse(
                "Hiba, nem lehetséges a pontszámítás a ".$meetsMinimumScoreRequirement['error']." tárgyból elért 20% alatti eredmény miatt."
            );
        }

        $scoreBase = $this->calculateBaseScore($erettsegiEredmenyek, $kovetelmenyek);
        $scoreExtra = $this->calculateExtraPoints($erettsegiEredmenyek, $tobbletpontok);
        $scoreSum = $scoreBase + $scoreExtra;

        return [
            'ok' => true,
            'message' => $scoreSum . " ({$scoreBase} alappont + {$scoreExtra} többletpont).",
        ];
    }

    /**
     * @param array $valasztottSzak
     * @return array
     */
    private function getKovetelmenyek(array $valasztottSzak): array
    {
        if (
            $valasztottSzak['egyetem'] === 'ELTE' &&
            $valasztottSzak['kar'] === 'IK' &&
            $valasztottSzak['szak'] === 'Programtervező informatikus'
        ) {
            return [
                'kotelezo' => [
                    [
                        'nev' => 'matematika',
                        'szint' => 'kozep',
                    ],
                ],
                'kotelezoen_valaszthato' => [
                    'biologia',
                    'fizika',
                    'informatika',
                    'kemia',
                ],
            ];
        }

        return [
            'kotelezo' => [
                [
                    'nev' => 'angol',
                    'szint' => 'emelt',
                ],
            ],
            'kotelezoen_valaszthato' => [
                'francia',
                'nemet',
                'olasz',
                'orosz',
                'spanyol',
                'tortenelem',
            ],
        ];
    }

    /**
     * @param array $requirements
     * @param array $erettsegiEredmenyek
     * @return bool
     */
    private function validateRequiredSubjects(array $erettsegiEredmenyek, array $requirements): bool
    {
        if (
            ! isset($requirements['kotelezo']) ||
            ! isset($requirements['kotelezoen_valaszthato'])
        ) {
            return false;
        }

        $kotelezo = $requirements['kotelezo'];
        $kotelezoenValaszthato = $requirements['kotelezoen_valaszthato'];

        // Kötelező tárgyak ellenőrzése
        foreach ($kotelezo as $required) {

            $found = false;

            foreach ($erettsegiEredmenyek as $exam) {

                if ($exam['nev'] !== $required['nev']) {
                    continue;
                }

                // Szintvizsgálat csak akkor, ha emelt a követelmény
                if ($required['szint'] === 'emelt') {
                    if ($exam['tipus'] !== 'emelt') {
                        continue;
                    }
                }

                $found = true;
                break;
            }

            if (! $found) {
                return false;
            }
        }

        // Kötelezően választható tárgyak közül legalább egy legyen meg
        $electiveFound = false;

        foreach ($kotelezoenValaszthato as $valaszthatoNev) {

            foreach ($erettsegiEredmenyek as $exam) {

                if ($exam['nev'] === $valaszthatoNev) {
                    $electiveFound = true;
                    break 2;
                }
            }
        }

        if (! $electiveFound) {
            return false;
        }

        return true;
    }

    /**
     * @param array $erettsegiEredmenyek
     * @return array
     */
    private function meetsMinimumScoreRequirement(array $erettsegiEredmenyek): array
    {
        foreach ($erettsegiEredmenyek as $exam) {

            $score = (int) str_replace('%', '', $exam['eredmeny']);
            if ($score < 20) {
                return [
                    'passed' => false,
                    'error' => $exam['nev'],
                ];
            }
        }

        return ['passed' => true];
    }

    private function calculateBaseScore(array $erettsegiEredmenyek, array $requirements): int
    {
        $kotelezo = $requirements['kotelezo'] ?? [];
        $kotelezoenValaszthato = $requirements['kotelezoen_valaszthato'] ?? [];

        $requiredScore = 0;

        // Kötelező tárgy pontszám
        foreach ($kotelezo as $required) {

            foreach ($erettsegiEredmenyek as $exam) {

                if ($exam['nev'] !== $required['nev']) {
                    continue;
                }

                // Szintvizsgálat csak ha emelt az elvárás
                if ($required['szint'] === 'emelt' && $exam['tipus'] !== 'emelt') {
                    continue;
                }

                $requiredScore = (int) str_replace('%', '', $exam['eredmeny']);
                break 2;
            }
        }

        // Kötelezően választható tárgyak közül a legjobb
        $bestElectiveScore = 0;

        foreach ($kotelezoenValaszthato as $valaszthatoNev) {

            foreach ($erettsegiEredmenyek as $exam) {

                if ($exam['nev'] === $valaszthatoNev) {

                    $score = (int) str_replace('%', '', $exam['eredmeny']);

                    if ($score > $bestElectiveScore) {
                        $bestElectiveScore = $score;
                    }
                }
            }
        }

        // Összegzés + duplázás
        $sum = $requiredScore + $bestElectiveScore;

        return $sum * 2;
    }
    private function calculateExtraPoints(array $erettsegiEredmenyek, array $tobbletpontok): int
    {
        $extraPoints = 0;

        // Nyelvvizsga pontok
        $languageScores = [];

        foreach ($tobbletpontok as $bonus) {

            if ($bonus['kategoria'] !== 'Nyelvvizsga') {
                continue;
            }

            $score = 0;

            // Pontérték a nyelvvizsga típus alapján
            if ($bonus['tipus'] === 'B2') {
                $score = 28;
            } elseif ($bonus['tipus'] === 'C1') {
                $score = 40;
            }

            $lang = $bonus['nyelv'];

            // Ha több vizsga ugyanabból a nyelvből → a nagyobb pont számít
            if (! isset($languageScores[$lang]) || $score > $languageScores[$lang]) {
                $languageScores[$lang] = $score;
            }
        }

        $extraPoints += array_sum($languageScores);

        // Emelt szintű érettségi tárgyak pontjai
        foreach ($erettsegiEredmenyek as $exam) {
            if ($exam['tipus'] === 'emelt') {
                $extraPoints += 50;
            }
        }

        // Max 100 pont
        return min($extraPoints, 100);
    }

    /**
     * @param string $message
     * @return array
     */
    private function failResponse(string $message): array
    {
        return [
            'ok' => false,
            'message' => $message,
            'status' => 422,
        ];
    }
}
