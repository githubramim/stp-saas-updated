<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Account Categories
    |--------------------------------------------------------------------------
    |
    | The daily expense and P&L screens use these category lists. A custom
    | category can still be typed on the expense form when "Others" is chosen.
    |
    */

    'expense_categories' => [
        'Salary',
        'Rent',
        'Utilities',
        'Internet & Phone',
        'Transport',
        'Marketing',
        'Training Materials',
        'Equipment Maintenance',
        'Office Supplies',
        'Refreshments',
        'Repairs',
        'Miscellaneous',
    ],

    'income_categories' => [
        'Course Fees',
        'Admission Fees',
        'Exam Fees',
        'Certificate Fees',
        'Donation',
        'Grant',
        'Other Income',
    ],

    /*
    |--------------------------------------------------------------------------
    | Salary Expense Category
    |--------------------------------------------------------------------------
    |
    | When a salary sheet is marked paid, an expense transaction is created in
    | this category so the Profit & Loss report includes payroll automatically.
    |
    */

    'salary_category' => 'Salary',

];
