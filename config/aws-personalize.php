<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AWS Personalize Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para AWS Personalize para recomendaciones de componentes
    |
    */

    'region' => env('AWS_DEFAULT_REGION', 'us-east-2'),
    
    'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],

    // ARN del Dataset Group de Personalize
    'dataset_group_arn' => env('AWS_PERSONALIZE_DATASET_GROUP_ARN', 'arn:aws:personalize:us-east-2:330786909811:dataset-group/SIGECOMP_Recomienda'),

    // ARN de la campaña de Personalize (opcional)
    'campaign_arn' => env('AWS_PERSONALIZE_CAMPAIGN_ARN', null),

    // Configuración de recomendaciones
    'recommendations' => [
        'num_results' => 20,
        'min_score' => 0.1,
    ],

    // Configuración de logging
    'logging' => [
        'enabled' => env('AWS_PERSONALIZE_LOGGING', true),
        'level' => env('AWS_PERSONALIZE_LOG_LEVEL', 'info'),
    ],
]; 