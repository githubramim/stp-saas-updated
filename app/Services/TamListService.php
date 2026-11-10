<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Reads the TAM (Occupational Standard) spreadsheet shipped in docs/.
 *
 * Columns used: B = Industrial Sector, C = Occupation, D = Level, E = Duration.
 * Results are cached by file hash so the XLSX is parsed only when it changes.
 */
class TamListService
{
    public function path(): string
    {
        return (string) config('tam.path', base_path('docs/TAM List.xlsx'));
    }

    /**
     * @return array<int, array{sector: string, occupation: string, level: int, duration: int}>
     */
    public function rows(): array
    {
        $path = $this->path();

        if (! is_file($path)) {
            return [];
        }

        $hash = @sha1_file($path);

        if ($hash === false) {
            return [];
        }

        return Cache::rememberForever('tam-list:'.$hash, fn () => $this->parse($path));
    }

    public function hasData(): bool
    {
        return $this->rows() !== [];
    }

    /**
     * @return array<int, string>
     */
    public function sectors(): array
    {
        return $this->distinct('sector');
    }

    /**
     * @return array<int, string>
     */
    public function occupations(): array
    {
        return $this->distinct('occupation');
    }

    /**
     * @return array<int, int>
     */
    public function levels(): array
    {
        $levels = array_values(array_unique(array_map(
            static fn (array $row) => $row['level'],
            $this->rows()
        )));

        sort($levels, SORT_NUMERIC);

        return $levels;
    }

    /**
     * Occupation => Industrial Sector, using the first matching TAM row.
     *
     * @return array<string, string>
     */
    public function occupationSectors(): array
    {
        $map = [];

        foreach ($this->rows() as $row) {
            $map[$row['occupation']] ??= $row['sector'];
        }

        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);

        return $map;
    }

    /**
     * Find every TAM entry for an occupation (optionally narrowed by level).
     *
     * A handful of Occupation + Level pairs contain more than one duration, so
     * `durations` is returned as a list and the first value is a safe default.
     *
     * @return array{
     *     occupation: string|null,
     *     sector: string|null,
     *     level: int|null,
     *     duration_hours: int|null,
     *     durations: array<int, int>,
     *     sectors: array<int, string>,
     *     levels: array<int, int>,
     *     rows: array<int, array{sector: string, occupation: string, level: int, duration: int}>
     * }
     */
    public function lookup(string $occupation, int|string|null $level = null): array
    {
        $needle = $this->normalise($occupation);
        $level = ($level === null || $level === '') ? null : (int) $level;

        $rows = array_values(array_filter(
            $this->rows(),
            fn (array $row) => $this->normalise($row['occupation']) === $needle
                && ($level === null || $row['level'] === $level)
        ));

        $durations = array_values(array_unique(array_map(
            static fn (array $row) => $row['duration'],
            $rows
        )));

        $sectors = array_values(array_unique(array_map(
            static fn (array $row) => $row['sector'],
            $rows
        )));

        $levels = array_values(array_unique(array_map(
            static fn (array $row) => $row['level'],
            $rows
        )));
        sort($levels, SORT_NUMERIC);

        return [
            'occupation' => $rows[0]['occupation'] ?? null,
            'sector' => $sectors[0] ?? null,
            'level' => $level,
            'duration_hours' => $durations[0] ?? null,
            'durations' => $durations,
            'sectors' => $sectors,
            'levels' => $levels,
            'rows' => $rows,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function distinct(string $key): array
    {
        $values = array_values(array_unique(array_map(
            static fn (array $row) => $row[$key],
            $this->rows()
        )));

        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        return $values;
    }

    private function normalise(?string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $value)));
    }

    /**
     * Parse the first worksheet of the XLSX archive.
     *
     * @return array<int, array{sector: string, occupation: string, level: int, duration: int}>
     */
    private function parse(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open the TAM List spreadsheet at {$path}.");
        }

        try {
            $sharedStrings = $this->parseSharedStrings($zip->getFromName('xl/sharedStrings.xml'));
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        } finally {
            $zip->close();
        }

        if ($sheetXml === false) {
            throw new RuntimeException("The TAM List spreadsheet at {$path} has no first worksheet.");
        }

        $sheet = $this->xml($sheetXml);
        $rows = [];

        foreach ($sheet->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $sheetRow) {
            $cells = [];

            foreach ($sheetRow->xpath('*[local-name()="c"]') ?: [] as $cell) {
                $column = preg_replace('/\d+/', '', (string) $cell['r']);

                if ($column === '') {
                    continue;
                }

                $cells[$column] = trim($this->cellValue($cell, $sharedStrings));
            }

            $sector = $cells['B'] ?? '';
            $occupation = $cells['C'] ?? '';
            $level = $cells['D'] ?? '';
            $duration = $cells['E'] ?? '';

            if ($sector === '' || $occupation === '' || ! is_numeric($level) || ! is_numeric($duration)) {
                continue;
            }

            $rows[] = [
                'sector' => $sector,
                'occupation' => $occupation,
                'level' => (int) $level,
                'duration' => (int) round((float) $duration),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function parseSharedStrings(string|false $xml): array
    {
        if ($xml === false) {
            return [];
        }

        $document = $this->xml($xml);
        $strings = [];

        foreach ($document->xpath('//*[local-name()="si"]') ?: [] as $item) {
            $strings[] = $this->textOf($item);
        }

        return $strings;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            $index = (int) (string) $cell->v;

            return $sharedStrings[$index] ?? '';
        }

        if ($type === 'inlineStr') {
            return $this->textOf($cell);
        }

        return (string) $cell->v;
    }

    private function textOf(SimpleXMLElement $node): string
    {
        $parts = [];

        foreach ($node->xpath('.//*[local-name()="t"]') ?: [] as $text) {
            $parts[] = (string) $text;
        }

        return trim(implode('', $parts));
    }

    private function xml(string $xml): SimpleXMLElement
    {
        $document = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NOBLANKS);

        if ($document === false) {
            throw new RuntimeException('Unable to parse the TAM List spreadsheet XML.');
        }

        return $document;
    }
}
