<?php

return array(
    'includes' => array(
        SQUIRT_TEST_DIR . '/_config/include_config.php'
    ),
    'services' => array(
        'WORD_CONTAINER' => array(
            'params' => array(
                'second' => 'cabinet',
                'third' => 'replaceme:service_config.php',
                'fourth' => 'replaceme:service_config.php'
            )
        )
    )
);
