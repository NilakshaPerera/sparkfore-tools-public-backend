<?php

return [
    // Remote Administration
    'aapi_url' => env('AAPI_URL', 'https://sparkfore.com'),
    'aapi_client_id' => env('AAPI_CLIENT_ID', '-'),
    'aapi_client_secret' => env('AAPI_CLIENT_SECRET', '-'),
    'aapi_username' => env('AAPI_USERNAME', '-'),
    'aapi_password' => env('AAPI_PASSWORD', '-'),
    'aapi_stages' => [ // stages should be in correct order to update the status in ansible callback
        "preparing_build_stage",
        "building_application_stage",
        "performing_tests_stage",
        "analyzing_result_stage",
        "publishing_application_stage",
    ],
    // GIT Related
    'git_url' => env('GIT_URL', 'https://git.autotech.se'),
    'github_url' => env('GIT_URL', 'https://github.com/'),
    'github_api_url' => env('GIT_URL', 'https://api.github.com/repos/'),
    'git_moodle_baseline' => env('GIT_MOODLE_BASELINE', 'https://git.autotech.se/LMS-Customer/moodle-baseline'),
    // Auth token can be created by login in to git account. setting tdefault value as '-' to avoid error "create a token
    // coming from gitea client in dev env build
    'git_auth_token' => env('GIT_AUTH_TOKEN', '-'),
    // PROMETHEUS Related
    'prometheus_pwd' => env('PROMETHEUS_PWD', ''),
    'prometheus_username' => env('PROMETHEUS_USERNAME', ''),
    "package_build" => [
        "development_cron" => "0 2 * * *",
        "staging_cron" => "0 3 1,14 * *",
        "production_cron" => "0 2 14 * *",
    ],
    "sparkfore_open_ai_url_base" => env('SPARKFORE_OPEN_AI_URL_BASE', 'http://openai:8000/api/v1/'),
    "postmark_email" => [
        "from" => env('POSTMARK_EMAIL_FROM', 'noreply@sparkfore.com'),
        "email_token" => env('POSTMARK_API_TOKEN', '-'),
        "release_note_email_template_id" => env('POSTMARK_RELEASE_NOTE_EMAIL_TEMPLATE_ID', '38230093'),
    ],
    "free_installation" => [
        "package_pipeline_name" => env('FREE_INSTALLATION_PACKAGE_PIPELINE_NAME', 'moodle-free'),
    ],


];
