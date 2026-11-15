<?php

namespace App\Support\Spreadsheet;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Reads the first worksheet of a .xlsx workbook without any external library.
 *
 * Returns a list of rows, each a zero-indexed list of cell strings. Empty
 * trailing cells are trimmed and completely empty rows are preserved so the
 * caller can report accurate row numbers.
 */
class XlsxReader
{
    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const NS_RELATIONSHIPS = 'http://schemas.openxmlformats.org/package/2006/relationships';

    private const NS_OFFICE_RELATIONSHIPS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * @return array<int, array<int, string>>
     */
    public static function rows(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('The uploaded file is not a valid .xlsx workbook.');
        }

        try {
            $sharedStrings = self::sharedStrings($zip);
            $sheetPath = self::firstSheetPath($zip);
            $sheetXml = $zip->getFromName($sheetPath);

            if ($sheetXml === false) {
                throw new RuntimeException('The workbook does not contain a readable worksheet.');
            }

            return self::parseSheet($sheetXml, $sharedStrings);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private static function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $root = self::load($xml);
        $strings = [];

        foreach ($root->si as $item) {
            $strings[] = self::richText($item);
        }

        return $strings;
    }

    private static function firstSheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml !== false && $relationshipsXml !== false) {
            $workbook = self::load($workbookXml);
            $firstSheet = $workbook->sheets->sheet[0] ?? null;

            if ($firstSheet !== null) {
                $relationshipId = (string) $firstSheet->attributes(self::NS_OFFICE_RELATIONSHIPS)->id;

                if ($relationshipId !== '') {
                    $relationships = self::load($relationshipsXml);

                    foreach ($relationships->Relationship as $relationship) {
                        if ((string) $relationship['Id'] !== $relationshipId) {
                            continue;
                        }

                        $target = ltrim((string) $relationship['Target'], '/');

                        if ($target !== '' && ! str_starts_with($target, 'xl/')) {
                            $target = 'xl/'.$target;
                        }

                        if ($zip->locateName($target) !== false) {
                            return $target;
                        }
                    }
                }
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array<int, string>>
     */
    private static function parseSheet(string $xml, array $sharedStrings): array
    {
        $root = self::load($xml);
        $sheetData = $root->sheetData;

        if ($sheetData === null) {
            return [];
        }

        $rows = [];

        foreach ($sheetData->row as $row) {
            $cells = [];
            $position = 0;

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $index = $reference !== '' ? self::columnIndex($reference) : $position;

                $cells[$index] = self::cellValue($cell, $sharedStrings);
                $position = $index + 1;
            }

            $values = [];
            $lastIndex = $cells === [] ? -1 : max(array_keys($cells));

            for ($index = 0; $index <= $lastIndex; $index++) {
                $values[] = $cells[$index] ?? '';
            }

            $rows[(int) $row['r']] = $values;
        }

        ksort($rows);

        return array_values($rows);
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private static function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 'inlineStr') {
            return self::richText($cell->is);
        }

        $value = (string) $cell->v;

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        if ($type === 'b') {
            return $value === '1' ? 'TRUE' : 'FALSE';
        }

        if ($type === 'e') {
            return '';
        }

        return $value;
    }

    private static function richText(?SimpleXMLElement $node): string
    {
        if ($node === null) {
            return '';
        }

        $text = '';

        foreach ($node->xpath('.//*[local-name()="t"]') ?: [] as $part) {
            $text .= (string) $part;
        }

        return $text;
    }

    private static function columnIndex(string $reference): int
    {
        preg_match('/^[A-Z]+/i', $reference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private static function load(string $xml): SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);
        $root = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($root === false) {
            throw new RuntimeException('A worksheet inside the workbook could not be read.');
        }

        return $root;
    }
}
