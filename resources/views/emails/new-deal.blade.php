<x-mail::message>

# Nuevo {{ $type }} asignado: {{ $deal->name }}

Tienes un nuevo {{ $type }} asignado

<x-mail::button :url="$frontUrl">
Ir al CRM
</x-mail::button>
</x-mail::message>
