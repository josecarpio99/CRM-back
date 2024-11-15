<x-mail::message>

# Nuevo contacto: {{ $customer->name }}

## Creado Por: {{ $customer->creator->name }}

## Asignado A: {{ $customer->owner->name }}

<x-mail::button :url="$frontUrl">
Ir al Contacto
</x-mail::button>
</x-mail::message>
