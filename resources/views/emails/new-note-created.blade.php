<x-mail::message>

# {{$note->user->name}} dejó una nota

## {{ $type }}: {{ $note->noteable->name }}

## Propiedad: {{ $note->user->name}}

Mensaje:
{{ $note->content }}

<x-mail::button :url="$frontUrl">
Ver
</x-mail::button>
</x-mail::message>
