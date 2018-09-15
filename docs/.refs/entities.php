<?php

$template = <<<'MD'
{% for domain in msgphp.domains %}
## `{{ domain.package.name }}`

{% if domain.entities %}
### Entities

{% for entity in domain.entities %}
- `{{ entity }}`
{% endfor %}

{% endif %}
### Fields

{% for field in domain.entity_fields %}
- `{{ field }}`
{% else %}
- No fields available
{% endfor %}

### Features

{% for feature in domain.entity_features %}
- `{{ feature }}`
{% else %}
- No features available
{% endfor %}

{% endfor %}
MD;

return [$template];
