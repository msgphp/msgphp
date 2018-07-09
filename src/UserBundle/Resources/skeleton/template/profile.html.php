<?php

declare(strict_types=1);

use MsgPhp\UserBundle\Twig\GlobalVariable;

$userVariable = GlobalVariable::NAME;

if ($logout) {
    $logoutSection = <<<LOGOUT
    <p><a href="{{ path('logout') }}">Logout</a></p>
LOGOUT;
}

return <<<TWIG
{% extends '${base}' %}

{% block ${block} %}
    <h1>Your Profile</h1>

    <p>Logged in as: <em>{{ ${userVariable}.current.${fieldName} }}</em></p>
    ${logoutSection}
{% endblock %}

TWIG;
