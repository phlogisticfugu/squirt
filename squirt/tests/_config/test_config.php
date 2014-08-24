<?php

return array(
    'includes' => array(
        SQUIRT_TEST_DIR . '/_config/test_include_config.php',
        SQUIRT_TEST_DIR . '/_config/test_include2_config.php'
    ),
    'prefix' => 'LAMB', 
    'services' => array(
        'TEST' => array(
            'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
        )
    ) 
);
