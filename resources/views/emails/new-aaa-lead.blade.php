<x-mail::message>

# Nuevo Prospecto "AAA - Corporativos e Industrias"

## Propiedad: {{ $lead->owner->name }}

- Nombre Del Contacto: {{ $lead->name }}
- Nombre De La Empresa: {{ $lead->company_name }}
- Télefono: {{ $lead->mobile }}
- Correo: {{ $lead->email }}

Requerimientos:

{{ $lead->requirement }}

<x-mail::button :url="$leadUrl">
Ir al Prospecto
</x-mail::button>
</x-mail::message>
