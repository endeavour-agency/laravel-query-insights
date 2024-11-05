<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('QUERY_INSIGHTS_ENABLED', config('app.debug', true)),
];
