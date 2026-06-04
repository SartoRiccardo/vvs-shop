<?php

return [
    'fedex_ficp' => [
        'code'        => 'fedex_ficp',
        'title'       => 'FedEx International Connect Plus',
        'description' => 'FedEx FICP — tracked international delivery',
        'active'      => true,
        'class'       => \Webkul\FedExShipping\Carriers\FedExFICP::class,
    ],
];
