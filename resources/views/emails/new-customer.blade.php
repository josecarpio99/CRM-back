<x-mail::message>

# Nuevo contacto asignado: {{ $customer->name }}

Tienes un nuevo contacto asignado

<x-mail::button :url="$frontUrl">
Ir al CRM
</x-mail::button>
</x-mail::message>
