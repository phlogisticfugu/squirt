<?php

return array(
    'prefix' => 'WOLF',
    'services' => array(
        'WORD_CONTAINER' => array(
            'class' => 'Squirt\Common\Container',
            'params' => array(
                'first' => 'exciting',
                'second' => 'replaceme:include_config.php',
                'third' => 'replaceme:include_config.php',
                'fourth' => 'replaceme:include_config.php'
            )
        )
    )
);
