<?php

$bundles = [];
foreach (glob('src/*Bundle/composer.json') as $file) {
    $package = json_decode(file_get_contents($file));
    $bundles[$package->name] = $package->description;
}

ksort($bundles);

$template = <<<'MD'
{% for name, desc in bundles %}
- `{{ name }}`: {{ desc }}
{% endfor %}
MD;

return [$template, 'bundles' => $bundles];
