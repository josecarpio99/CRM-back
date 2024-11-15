<x-mail::message>

# Nuevo Contacto "AAA - Corporativos e Industrias"

## Propiedad: {{ $customer->owner->name }}

- Nombre Del Contacto: {{ $customer->name }}
- Nombre De La Empresa: {{ $customer->company_name }}
- Télefono: {{ $customer->mobile }}
- Correo: {{ $customer->email }}

<x-mail::button :url="$customerUrl">
Ir al Contacto
</x-mail::button>
</x-mail::message>
