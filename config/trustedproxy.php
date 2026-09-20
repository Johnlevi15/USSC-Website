<?php

$trustedProxies = array_values(array_filter(
    array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))),
    fn (string $proxy): bool => $proxy !== '',
));

return [
    'proxies' => $trustedProxies === [] ? null : $trustedProxies,
];
