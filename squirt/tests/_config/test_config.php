<?php

return array(
    'includes' => array(
        SQUIRT_TEST_DIR . '/_config/test_include_config.php'
    ),
    'services' => array(
        'TEST' => array(
            'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
        )
    ) 
);
