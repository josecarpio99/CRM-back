<x-mail::message>

# Nuevo prospecto asignado: {{ $lead->name }}

Tienes un nuevo prospecto asignado

<x-mail::button :url="$frontUrl">
Ir al CRM
</x-mail::button>
</x-mail::message>
