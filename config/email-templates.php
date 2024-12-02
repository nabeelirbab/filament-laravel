<?php

return [
    // Table names for email templates and themes
    'table_name'       => 'vb_email_templates',
    'theme_table_name' => 'vb_email_templates_themes',

    // If you want to use your own resource for email templates, set this to true
    "publish_resource" => false,

    "mailable_directory" => 'Mail/Visualbuilder/EmailTemplates',

    // Admin Panel Resource Navigation Options
    'navigation' => [
        'sort' => 50,
        'group' => 'Settings',
    ],

    // Path to the default email template view
    'default_view' => null,

    // Path for template views
    'template_view_path' => 'vb-email-templates::email',

    // Default Email Styling - remove logo configuration
    'logo_width' => null,
    'logo_height' => null,

    // Content Width in Pixels
    'content_width' => '650',

    // Background Colours
    'header_bg_color' => '#B8B8D1',
    'body_bg_color' => '#f4f4f4',
    'content_bg_color' => '#FFFFFB',
    'callout_bg_color' => '#B8B8D1',
    'button_bg_color' => '#FFC145',

    // Text Colours
    'body_color'    => '#333333',
    'callout_color' => '#000000',
    'button_color'  => '#2A2A11',
    'anchor_color'  => '#4c05a1',

    // Contact details included in default email templates
    'customer-services' => [],

    // Clear Footer Links - set to an empty array to hide footer links
    'links' => [],

    // Options for alternative languages
    'default_locale' => 'en_GB',

    // Languages included in the language picker for email templates
    'languages' => [
        'en_GB' => ['display' => 'British', 'flag-icon' => 'gb'],
        'en_US' => ['display' => 'USA', 'flag-icon' => 'us'],
        'es'    => ['display' => 'Español', 'flag-icon' => 'es'],
        'fr'    => ['display' => 'Français', 'flag-icon' => 'fr'],
        'pt'    => ['display' => 'Brasileiro', 'flag-icon' => 'br'],
        'in'    => ['display' => 'Hindi', 'flag-icon' => 'in'],
    ],

    // Notifiable Models who can receive emails
    'recipients' => [
        '\\App\\Models\\User',
        '\\App\\Models\\Paper',
    ],

    // Guards who are authorised to edit email templates
    'editor_guards' => ['web'],

    // Allowed config keys for use in email templates
    'config_keys' => [
        'app.name',
        'app.url',
        'email-templates.customer-services'
    ],

    // Define which built-in emails are automatically sent
    'send_emails' => [
        'new_user_registered'    => true,
        'verification'           => true,
        'user_verified'          => true,
        'login'                  => true,
        'password_reset_success' => true,
        'request_reset'          => true,
    ],
];
