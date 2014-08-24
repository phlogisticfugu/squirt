<?php

return array(
    'services' => array(
        'PARENT_CONTAINER' => array(
            'params' => array(
                'alpha' => 'from:include-parent',
                'beta' => 'from:include-parent',
                'gamma' => 'from:include-parent',
                'delta' => 'from:include-parent',
                'epsilon' => 'from:include-parent'
            )
        ),
        'CONTAINER' => array(
            'params' => array(
                'alpha' => 'from:include-service',
                'beta' => 'from:include-service',
                'gamma' => 'from:include-service'
            )
        )
    )
);