<?php

$fields = "{{ form_row(form.${fieldName}) }}";
if ($hasPassword) {
    $fields .= "\n        {{ form_row(form.password) }}";
}

return <<<TWIG
{% extends '${base}' %}

{% block ${block} %}
    <h1>Login</h1>

    {{ form_start(form) }}
        {{ form_errors(form) }}
        ${fields}

        {#
        <div>
            <input type="submit" value="Login" />
            <p><a href="{{ url('forgot_password') }}">Forgot password?</a></p>
        </div>
        #}
    {{ form_end(form) }}
{% endblock %}
TWIG;
