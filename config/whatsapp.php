<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Driver
    |--------------------------------------------------------------------------
    |
    | "link" opens a wa.me deep link in the browser (no credentials needed).
    | "cloud" sends through the official WhatsApp Cloud API using the token
    | and phone-number ID configured below.
    |
    */

    'driver' => env('WHATSAPP_DRIVER', 'link'),

    'cloud_token' => env('WHATSAPP_CLOUD_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),

    /*
    |--------------------------------------------------------------------------
    | Default Country Code
    |--------------------------------------------------------------------------
    |
    | Local numbers are normalised with this dialling code before a wa.me
    | link or Cloud API payload is built. Bangladesh is the default.
    |
    */

    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '880'),

    /*
    |--------------------------------------------------------------------------
    | Message Templates
    |--------------------------------------------------------------------------
    |
    | Preset openers shown in the WhatsApp composer on the lead detail page.
    |
    */

    'templates' => [
        'intro' => "Hello {name},\n\nThank you for your interest in our training programme. Our admission counsellor would be happy to share the course details, class routine and fee structure with you.\n\nWhen would be a convenient time to talk?\n\n— {institute}",
        'follow_up' => "Hello {name},\n\nThis is a friendly follow-up regarding your admission enquiry. Seats are still available and we can help you complete the admission process today.\n\n— {institute}",
        'documents' => "Hello {name},\n\nTo complete your admission we need a copy of your National ID/Birth Certificate, a recent photograph and the educational certificates. Please share them whenever convenient.\n\n— {institute}",
        'reminder' => "Hello {name},\n\nReminder: your admission follow-up is scheduled for {date}. Please let us know if that time works for you.\n\n— {institute}",
    ],

];
