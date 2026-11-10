<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TAM (Occupational Standard) List
    |--------------------------------------------------------------------------
    |
    | The NSDA/TAM spreadsheet holds the Industrial Sector, Occupation, NTVQF
    | Level and Nominal Duration (hours) used to auto-fill courses. The file is
    | parsed with ZipArchive + SimpleXML so no extra PHP extension is required.
    |
    */
    'path' => env('TAM_LIST_PATH', base_path('docs/TAM List.xlsx')),
];
