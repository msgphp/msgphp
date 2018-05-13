<?php

$template = <<<'MD'
{% for domain in msgphp.domains if domain.domain_events %}
## `{{ domain.package.name }}`

{% for domain_event in domain.domain_events %}
- `{{ domain_event }}`
{% endfor %}

{% endfor %}
MD;

return [$template];
