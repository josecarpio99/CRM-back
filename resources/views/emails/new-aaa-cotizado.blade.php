<x-mail::message>

# Nueva Cotización "AAA - Corporativos e Industrias"

## Propiedad: {{ $deal->owner->name }}

## Valor: MXN {{ number_format($deal->value, 2) }}

- Nombre Del Contacto: {{ $deal->customer->name }}
- Nombre De La Empresa: {{ $deal->customer->company_name }}
- Télefono: {{ $deal->mobile }}
- Correo: {{ $deal->email }}

Requerimientos:

{{ $deal->requirement }}

<x-mail::button :url="$dealUrl">
Ir a la Cotización
</x-mail::button>
</x-mail::message>
