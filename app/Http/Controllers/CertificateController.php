<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Exam;
use App\Models\Student;
use App\Support\Spreadsheet\XlsxReader;
use App\Support\Spreadsheet\XlsxWriter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::with(['student', 'exam'])->latest()->paginate(20);

        $instituteId = auth()->user()->institute_id;
        $students = Student::where('institute_id', $instituteId)->orderBy('name_en')->get();
        $exams = Exam::where('institute_id', $instituteId)->latest()->get();

        return view('certificates.index', compact('certificates', 'students', 'exams'));
    }

    public function create()
    {
        $instituteId = auth()->user()->institute_id;

        $students = Student::where('institute_id', $instituteId)
            ->orderBy('name_en')
            ->get();

        $exams = Exam::where('institute_id', $instituteId)
            ->latest()
            ->get();

        return view('certificates.create', compact('students', 'exams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'exam_id' => 'nullable|exists:exams,id',
            'title' => 'required|string|max:255',
            'issued_date' => 'nullable|date',
        ]);
        $validated['certificate_no'] = 'CERT-'.strtoupper(uniqid());
        $validated['verification_code'] = strtoupper(bin2hex(random_bytes(8)));
        $validated['institute_id'] = auth()->user()->institute_id ?? null;
        Certificate::create($validated);

        return redirect()->route('certificates.index')->with('success', 'Certificate created.');
    }

    /**
     * Download an .xlsx sample pre-filled with a few real students so the
     * column layout can be tested immediately.
     */
    public function bulkTemplate()
    {
        $instituteId = auth()->user()->institute_id;

        $students = Student::where('institute_id', $instituteId)
            ->orderBy('name_en')
            ->limit(3)
            ->get(['name_en', 'reference_no']);

        $exam = Exam::where('institute_id', $instituteId)->orderBy('title')->first();
        $examTitle = (string) ($exam?->title ?? '');
        $today = now()->toDateString();

        $rows = [[
            'student_reference', 'student_name', 'title', 'exam_title', 'issued_date',
        ]];

        if ($students->isEmpty()) {
            $rows[] = ['STU-00001', 'Md. Rakib Hasan', 'Certificate of Completion', $examTitle, $today];
            $rows[] = ['STU-00002', 'Nusrat Jahan', 'Certificate of Excellence', $examTitle, $today];
        } else {
            foreach ($students->values() as $index => $student) {
                $rows[] = [
                    (string) ($student->reference_no ?? ''),
                    (string) $student->name_en,
                    $index === 1 ? 'Certificate of Excellence' : 'Certificate of Completion',
                    $examTitle,
                    $today,
                ];
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'certificate-template-');

        if ($path === false) {
            abort(500, 'The template could not be generated.');
        }

        XlsxWriter::write($path, $rows, [24, 30, 32, 32, 14]);

        return response()->download($path, 'certificate-bulk-upload-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Import certificates from an .xlsx or .csv upload.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = strtolower((string) $file->getClientOriginalExtension());

        try {
            $rows = match ($extension) {
                'xlsx' => XlsxReader::rows($file->getRealPath()),
                'csv', 'txt' => $this->csvRows($file->getRealPath()),
                default => throw new \RuntimeException('Unsupported file type. Upload an .xlsx or .csv file.'),
            };
        } catch (\Throwable $exception) {
            return redirect()
                ->route('certificates.index')
                ->with('error', $exception->getMessage());
        }

        $rows = array_values(array_filter($rows, fn (array $row) => $this->rowHasContent($row)));

        if ($rows === []) {
            return redirect()
                ->route('certificates.index')
                ->with('error', 'The uploaded file is empty.');
        }

        if (count($rows) > 1001) {
            return redirect()
                ->route('certificates.index')
                ->with('error', 'Please upload at most 1,000 data rows at a time.');
        }

        $headerIndex = $this->findHeaderRow($rows);

        if ($headerIndex === null) {
            return redirect()
                ->route('certificates.index')
                ->with('error', 'The file needs a header row with a "title" column and a student column.');
        }

        $header = $this->headerMap($rows[$headerIndex]);

        if (! isset($header['title'])) {
            return redirect()
                ->route('certificates.index')
                ->with('error', 'The file must contain a "title" column.');
        }

        $instituteId = auth()->user()->institute_id;

        $students = Student::where('institute_id', $instituteId)->get(['id', 'name_en', 'reference_no']);
        $studentById = [];
        $studentByReference = [];
        $studentByName = [];

        foreach ($students as $student) {
            $studentById[$student->id] = $student;

            if ($student->reference_no) {
                $studentByReference[mb_strtolower(trim($student->reference_no))] = $student;
            }

            $studentByName[mb_strtolower(trim((string) $student->name_en))] = $student;
        }

        $exams = Exam::where('institute_id', $instituteId)->get(['id', 'title']);
        $examByTitle = [];

        foreach ($exams as $exam) {
            $examByTitle[mb_strtolower(trim((string) $exam->title))] = $exam;
        }

        $imported = 0;
        $errors = [];

        foreach (array_slice($rows, $headerIndex + 1) as $offset => $row) {
            $rowNumber = $headerIndex + $offset + 2;

            $get = fn (string $field) => isset($header[$field])
                ? trim((string) ($row[$header[$field]] ?? ''))
                : '';

            $title = $get('title');

            if ($title === '') {
                $errors[] = "Row {$rowNumber}: the title is required.";

                continue;
            }

            $student = null;
            $reference = $get('student_reference');
            $studentId = $get('student_id');
            $studentName = $get('student_name');

            if ($reference !== '') {
                $student = $studentByReference[mb_strtolower($reference)] ?? null;

                if (! $student) {
                    $errors[] = "Row {$rowNumber}: no student found with reference \"{$reference}\".";

                    continue;
                }
            } elseif ($studentId !== '' && ctype_digit($studentId)) {
                $student = $studentById[(int) $studentId] ?? null;

                if (! $student) {
                    $errors[] = "Row {$rowNumber}: no student found with ID {$studentId}.";

                    continue;
                }
            } elseif ($studentName !== '') {
                $student = $studentByName[mb_strtolower($studentName)] ?? null;

                if (! $student) {
                    $errors[] = "Row {$rowNumber}: no student found named \"{$studentName}\".";

                    continue;
                }
            }

            $exam = null;
            $examTitle = $get('exam_title');

            if ($examTitle !== '') {
                $exam = $examByTitle[mb_strtolower($examTitle)] ?? null;

                if (! $exam) {
                    $errors[] = "Row {$rowNumber}: no exam found titled \"{$examTitle}\".";

                    continue;
                }
            }

            $issuedDate = null;
            $issuedRaw = $get('issued_date');

            if ($issuedRaw !== '') {
                $issuedDate = $this->parseDate($issuedRaw);

                if ($issuedDate === null) {
                    $errors[] = "Row {$rowNumber}: \"{$issuedRaw}\" is not a valid date.";

                    continue;
                }
            }

            try {
                Certificate::create([
                    'institute_id' => $instituteId,
                    'student_id' => $student?->id,
                    'exam_id' => $exam?->id,
                    'certificate_no' => 'CERT-'.strtoupper((string) Str::ulid()),
                    'title' => mb_substr($title, 0, 255),
                    'issued_date' => $issuedDate,
                    'verification_code' => strtoupper(bin2hex(random_bytes(8))),
                ]);

                $imported++;
            } catch (\Throwable) {
                $errors[] = "Row {$rowNumber}: the certificate could not be saved.";
            }
        }

        $skipped = count($errors);

        if ($imported === 0) {
            return redirect()
                ->route('certificates.index')
                ->with('error', 'No certificates were imported.')
                ->with('bulk_errors', array_slice($errors, 0, 50));
        }

        $message = $imported.' certificate(s) imported.';

        if ($skipped > 0) {
            $message .= ' '.$skipped.' row(s) skipped.';
        }

        return redirect()
            ->route('certificates.index')
            ->with('success', $message)
            ->with('bulk_errors', array_slice($errors, 0, 50));
    }

    public function show(Certificate $certificate)
    {
        return view('certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        return view('certificates.edit', compact('certificate'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'issued_date' => 'nullable|date',
        ]);
        $certificate->update($validated);

        return redirect()->route('certificates.index')->with('success', 'Updated.');
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return redirect()->route('certificates.index')->with('success', 'Deleted.');
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function findHeaderRow(array $rows): ?int
    {
        $limit = min(count($rows), 10);

        for ($index = 0; $index < $limit; $index++) {
            $header = $this->headerMap($rows[$index]);

            $hasStudent = isset($header['student_reference'])
                || isset($header['student_id'])
                || isset($header['student_name']);

            if (isset($header['title']) && $hasStudent) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Map the uploaded column headings onto the fields used by the importer.
     *
     * @param  array<int, string>  $row
     * @return array<string, int>
     */
    private function headerMap(array $row): array
    {
        $aliases = [
            'student_reference' => ['student_reference', 'reference_no', 'reference', 'student_id_no', 'student_code', 'id_no'],
            'student_id' => ['student_id', 'db_id'],
            'student_name' => ['student_name', 'name', 'name_en', 'student'],
            'title' => ['title', 'certificate_title', 'certificate', 'certificate_name'],
            'exam_title' => ['exam_title', 'exam', 'exam_name', 'examination'],
            'issued_date' => ['issued_date', 'issue_date', 'date', 'issued', 'certificate_date'],
        ];

        $lookup = [];

        foreach ($aliases as $field => $names) {
            foreach ($names as $name) {
                $lookup[$name] = $field;
            }
        }

        $map = [];

        foreach ($row as $index => $value) {
            $key = $this->normalizeHeader((string) $value);

            if ($key === '' || ! isset($lookup[$key])) {
                continue;
            }

            $field = $lookup[$key];

            if (! isset($map[$field])) {
                $map[$field] = $index;
            }
        }

        return $map;
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    /**
     * @param  array<int, string>  $row
     */
    private function rowHasContent(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function csvRows(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException('The uploaded file could not be read.');
        }

        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';

        if ($firstLine !== false) {
            $candidates = [
                ',' => substr_count($firstLine, ','),
                ';' => substr_count($firstLine, ';'),
                "\t" => substr_count($firstLine, "\t"),
            ];

            arsort($candidates);

            $delimiter = (string) array_key_first($candidates);
        }

        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = array_map(function ($cell) {
                $cell = (string) $cell;

                // Strip a UTF-8 BOM from the first header cell.
                $cell = preg_replace('/^\xEF\xBB\xBF/', '', $cell) ?? $cell;

                return trim($cell);
            }, $row);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Accept Y-m-d, common day-first formats and raw Excel date serials.
     */
    private function parseDate(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value) && (float) $value > 0 && (float) $value < 100000) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d', 'd.m.Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date !== false) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                // Try the next format.
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
