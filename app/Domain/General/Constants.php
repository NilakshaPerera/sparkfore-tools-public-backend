<?php

namespace App\Domain\General;

define('PASSWORD', 'password');
define('SAVE_SUCCESS', 'Data has been saved successfully');
define('DATA_RETRIEVE_SUCCESS', 'Data has been successfully retrieved');
define('DATA_DELETE_SUCCESS', 'Data has been successfully deleted');
define('SPARKFORE_DOMAIN', 'sparkfore.com');

define('DOMAIN_TYPE_STANDARD', 'standard');
define('DOMAIN_TYPE_CUSTOM', 'custom');

define('SETTINGS_PRODUCT_MAINTENANCE_COST', 'product_maintenance');

define('HOSTING_CLOUD', 'cloud');
define('HOSTING_ON_PREM', 'on-prem');

define('AVAILABILITY_PUBLIC', 'public');
define('AVAILABILITY_PRIVATE', 'private');

define('STATUS_ONLINE', 'online');
define('STATUS_OFFLINE', 'offline');

define('GRANT_TYPE_PASSWORD', 'password');

define('REMOTE_JOB_TYPE_RESTART', 'restart');
define('REMOTE_JOB_TYPE_HOST', 'host');
define('REMOTE_JOB_TYPE_CREATE_PIPELINE', 'create_pipeline');
define('REMOTE_JOB_TYPE_BUILD_PIPELINE', 'build_pipeline');
define('REMOTE_JOB_TYPE_CREATE_CUSTOMER', 'create_customer');
define('REMOTE_JOB_TYPE_RENAME_CUSTOMER', 'rename_customer');
define('REMOTE_JOB_TYPE_DELETE_PIPELINE', 'delete_pipeline');
define('REMOTE_JOB_TYPE_PUBLIC_INSTALLATION', 'public_installation');
define('REMOTE_JOB_TYPE_STANDARD_INSTALLATION', 'standard_installation');
define('REMOTE_JOB_TYPE_DELETE_INSTALLATION', 'installation_delete');
define('REMOTE_JOB_TYPE_CHANGE_DISK_SIZE', 'change_disk_size');

define('INSTALATION_STATUS_ONLINE', 'online');
define('INSTALATION_STATUS_OFFLINE', 'offline');

define('INSTALATION_STATUS_CODE_SERVICE_UNAVAILABLE', '503');

define('INSTALLATION_STATE_RESTARTING', 'restarting');
define('INSTALLATION_STATE_STOPPED', 'stopped');
define('INSTALLATION_STATE_RUNNING', 'running');

define('GIT_VERSION_TYPE_BRANCH', 'branch');
define('GIT_VERSION_TYPE_TAG', 'TAG');
define('GIT_VERSION_TYPE_ID_BRANCH', '1');
define('GIT_VERSION_TYPE_ID_TAG', '2');
define('GIT_URL', 'https://git.autotech.se/');
define('VALIDATION_ERROR', 'Please fix the following errors');

define('PRODUCT_PRICE_NS', 'product_price');

$environments = [
    [
        'label' => 'Staging',
        'value' => 'staging',
    ],
    [
        'label' => 'Production',
        'value' => 'production',
    ]
];

