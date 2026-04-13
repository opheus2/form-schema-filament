<?php

declare(strict_types=1);

return [
    'state_path' => 'data',
    'fail_on_unsupported_fields' => true,
    'dynamic_data_resolver' => null,
    'ignore_layout_fields_in_payload' => [
        'divider',
        'spacing',
        'banner',
    ],
];
