<?php

$types = [];
foreach (glob('src/*/Infra/Doctrine/{**/*,*}Type.php', \GLOB_BRACE) as $file) {
    $class = 'MsgPhp\\'.str_replace('/', '\\', substr($file, 4, -4));
    $types[$class::NAME] = $class;
}

ksort($types);

$template = <<<'MD'
Type name | Type class
--- | ---
{% for name, class in types %}
`{{ name }}` | `{{ class }}`
{% endfor %}
MD;

return [$template, 'types' => $types];
