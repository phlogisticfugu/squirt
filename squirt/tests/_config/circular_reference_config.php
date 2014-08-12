<?php

/*
 * For unit testing purposes, test a file which includes itself
 * for infinite loop purposes
 */
return array(
    'includes' => array(
        __FILE__
    ),
    'services' => array(
        'TEST' => array(
            'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
        )
    )
);
