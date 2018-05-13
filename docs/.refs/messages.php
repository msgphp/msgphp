<?php

$template = <<<'MD'
{% for domain in msgphp.domains %}
## `{{ domain.package.name }}`

### Commands

{% for command in domain.commands %}
- `{{ command }}`
{% else %}
- No commands available
{% endfor %}

### Events

{% for event in domain.events %}
- `{{ event }}`
{% else %}
- No events available
{% endfor %}

{% endfor %}
MD;

return [$template];
