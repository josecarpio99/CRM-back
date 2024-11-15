<x-mail::message>

# Nueva {{ $type }}: {{ $deal->name }}

## Creado Por: {{ $deal->creator->name }}

## Asignado A: {{ $deal->owner->name }}

<x-mail::button :url="$frontUrl">
Ver
</x-mail::button>
</x-mail::message>
