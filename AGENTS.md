
# Reglas de Negocio Estrictas de Rennova

mira asi quedo, elimine las reglas de negocio porque no estan claras todavia
# AGENTS.md - Estándares de Desarrollo Rennova

## Tecnologías

* PHP 8.2+
* Laravel 12
* Livewire 3
* PostgreSQL

---

## Idioma

* Utilizar nombres de clases, métodos, variables, servicios y eventos en español.
* Mantener únicamente en inglés:

  * Métodos nativos de PHP.
  * Convenciones oficiales de Laravel.
  * Convenciones oficiales de Livewire.
  * Métodos mágicos y atributos del framework.

---

## Arquitectura

### Componentes Livewire

Responsabilidades permitidas:

* Interacción con el usuario.
* Validación básica.
* Comunicación con Servicios.

Responsabilidades prohibidas:

* Reglas de negocio.
* Consultas directas a base de datos.
* Cálculos complejos.

### Servicios

Responsables de:

* Reglas de negocio.
* Procesos operativos.
* Cálculos.
* Casos de uso del sistema.

### Modelos

Responsables únicamente de:

* Relaciones.
* Casts.
* Scopes.
* Accessors y mutators simples.

No incorporar lógica de negocio compleja.

### Migraciones

* Solo definición de estructura de datos.
* No incluir lógica de negocio.

---

## Integridad de Datos

* Toda operación que afecte múltiples entidades debe ejecutarse dentro de una transacción.
* Utilizar `DB::transaction()`.
* Garantizar consistencia de datos ante excepciones.

### Borrado de Datos
* Toda tabla con información histórica o auditabilidad debe implementar SoftDeletes.
* El borrado debe ser lógico, no físico, excepto en tablas claramente transitorias.
* Al recuperar listados, considerar explícitamente si deben incluirse registros eliminados.

### Consistencia de Inventario

* Nunca generar stock negativo.
* Toda salida de stock debe validar existencia disponible antes de confirmar la operación.
* Todo movimiento de inventario debe registrar:

  * fecha y hora
  * origen
  * destino
  * usuario responsable

---

## Validaciones

* Todo dato proveniente del usuario debe validarse.
* No confiar únicamente en validaciones del frontend.
* Las reglas críticas deben validarse también en la capa de Servicios.

---

## Rendimiento

* Utilizar eager loading cuando existan relaciones.
* Evitar consultas N+1.
* No cargar colecciones completas para luego filtrarlas en memoria.
* Priorizar Eloquent y Query Builder.

---

## Prohibiciones Arquitectónicas (ERROR)

* Uso de `->delete()` sin SoftDeletes.
* Uso de `forceDelete()` sin justificación explícita.
* Uso de `DB::raw`.
* Uso de `DB::statement`.
* Uso de `DB::select` con SQL crudo.
* Uso de comandos específicos del motor de base de datos en migraciones.
* Lógica de negocio compleja dentro de modelos.
* Consultas directas a base de datos desde componentes Livewire.
* Uso de `::where()`, `::find()`, `DB::` o equivalentes dentro de componentes Livewire.
* Uso de prefijos en inglés (`get`, `set`, `find`, `save`) para métodos personalizados.
* Generación de stock negativo.
* Duplicación de reglas de negocio en múltiples capas.

---

## Excepciones Justificadas

### Uso de Funciones PostgreSQL

Se permite el uso de `DB::selectOne()` o `DB::select()` para invocar **funciones PostgreSQL existentes** cuando se cumplan TODAS estas condiciones:

1. La función PostgreSQL ya existe en la base de datos y está optimizada.
2. La lógica implementada es compleja (ej: cálculos FIFO, algoritmos de optimización).
3. Migrar a Eloquent/Query Builder resultaría en:
   - Múltiples consultas en lugar de una sola.
   - Lógica compleja en PHP que debería estar en la base de datos.
   - Degradación significativa de rendimiento.
4. La llamada se realiza desde un **Servicio**, nunca desde Livewire o Modelos.
5. La función está documentada en el código con su propósito.

**Ejemplo válido:**
```php
// InventarioService.php
$resultado = DB::selectOne('SELECT * FROM calcular_costo_fifo(?, ?)', [$idInsumo, $cantidad]);
```

**Ejemplo inválido:**
```php
// Livewire component (PROHIBIDO)
$resultado = DB::selectOne('SELECT * FROM calcular_costo_fifo(?, ?)', [$idInsumo, $cantidad]);
```

---

## Advertencias de Deuda Técnica (WARNING)

* Modelos que superen las 300 líneas.
* Métodos que superen las 80 líneas.
* Componentes Livewire que superen los 30 KB.
* Servicios con múltiples responsabilidades claramente diferenciadas.
* Componentes Livewire que acumulen lógica de negocio.

---

## Proceso Antes de Modificar Código

Antes de implementar cualquier cambio:

1. Analizar modelos relacionados.
2. Analizar servicios relacionados.
3. Analizar migraciones existentes.
4. Analizar componentes Livewire afectados.
5. Verificar impacto en inventario.
6. Mantener consistencia con la arquitectura existente.

No crear nuevas abstracciones si ya existe una solución equivalente dentro del proyecto.

---

## Criterio General

Ante cualquier duda:

1. Priorizar claridad sobre complejidad.
2. Priorizar mantenibilidad sobre optimización prematura.
3. Mantener las reglas de negocio centralizadas en Servicios.
4. Favorecer soluciones nativas de Laravel antes que implementaciones personalizadas.
5. Preservar la integridad y trazabilidad de los datos.

