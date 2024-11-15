<x-mail::message>

# Nuevo prospecto: {{ $lead->name }}

## Creado Por: {{ $lead->creator->name }}

## Asignado A: {{ $lead->owner->name }}

<x-mail::button :url="$frontUrl">
Ir al Prospecto
</x-mail::button>
</x-mail::message>
