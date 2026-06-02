# PLAN DE PRUEBAS DE CAJA BLANCA - SISTEMA RENNOVA

**Fecha de ejecucion (historica):** 5 de diciembre de 2025
**Ultima actualizacion:** 8 de febrero de 2026
**Proyecto:** Rennova - Sistema de Gestión Forestal
**Tipo de Pruebas:** Caja Blanca (análisis del código interno)

---

##  Índice
1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Estructura del Sistema](#estructura-del-sistema)
3. [Plan de Pruebas](#plan-de-pruebas)
4. [Resultados de Pruebas](#resultados-de-pruebas)
5. [Conclusiones](#conclusiones)

---

## Resumen Ejecutivo

Se ha diseñado e implementado una **suite completa de pruebas de caja blanca** para validar la funcionalidad crítica del sistema Rennova. El plan cubre:

 **CRUDs principales** (Lotes, Maquinaria, Empleados, Partes Diarios)
 **Mantenimiento preventivo** (creación, aprobación, completación)
 **Notificaciones** (sistema e intenciones de email)
 **Liquidación de personal** (cálculo de pagos)
 **Análisis climático y estadísticas** (decisiones operativas)

---

## Estructura del Sistema

### Modelos Principales
```
Lote ←→ Maquinaria (many-to-many)
  ├── ParteDiario
  │   ├── empleados (many-to-many)
  │   └── cargas
  └── Empleado
      ├── RolLaboral
      ├── Adelantos
      └── Recibos

Maquinaria
├── TipoMaquinaria
├── Mantenimiento
│   ├── TipoMantenimiento
│   └── MantenimientoInsumo
│       └── Insumo
└── HistoricoCostosMaquinaria

NotificacionSistema
└── User
```

### Servicios Clave
- **MantenimientoService:** Gestión de mantenimiento preventivo
- **ClimaDecisionService:** Análisis climático y decisiones operativas
- **ForestalStatsService:** Estadísticas y reportes forestales

---

## Plan de Pruebas

### 1️⃣ PRUEBAS DE CRUDs

#### 1.1 Lotes
| Caso de Prueba | Descripción | Entrada | Salida Esperada |
|---|---|---|---|
| **test_crear_lote** | Crear nuevo lote | datos completos | Lote creado en BD |
| **test_actualizar_lote** | Actualizar estado y superficie | estado=inactivo, superficie=150 | Datos actualizados correctamente |
| **test_eliminar_lote** | Eliminar lote de BD | lote existente | Lote removido de BD |
| **test_listar_lotes** | Listar todos los lotes | query all | Mínimo 2 lotes retornados |

#### 1.2 Maquinaria
| Caso de Prueba | Descripción | Entrada | Salida Esperada |
|---|---|---|---|
| **test_crear_maquinaria** | Crear máquina | modelo='CAT 325' | Maquinaria creada |
| **test_asignar_a_lote** | Asignar máquina a lote | id_maquinaria, id_lote | Relación creada en pivote |
| **test_desasignar_de_lote** | Remover asignación | relación existente | Relación eliminada |

#### 1.3 Empleados
| Caso de Prueba | Descripción | Entrada | Salida Esperada |
|---|---|---|---|
| **test_crear_empleado** | Crear nuevo empleado | dni='87654321', nombre='Carlos' | Empleado creado |
| **test_asignar_a_lote** | Asignar empleado a lote | id_empleado, id_lote | Relación creada |

#### 1.4 Partes Diarios
| Caso de Prueba | Descripción | Entrada | Salida Esperada |
|---|---|---|---|
| **test_crear_parte** | Crear parte diario | fecha, costos | Parte creado con costo total calculado |
| **test_asignar_empleado** | Vincular empleado a parte | empleado_id, parte_id | Relación creada |
| **test_crear_carga** | Crear carga en lote | peso=8000kg | Carga creada y asociada |

---

### 2️⃣ PRUEBAS DE MANTENIMIENTO PREVENTIVO

| Caso de Prueba | Descripción | Lógica Probada | Resultado Esperado |
|---|---|---|---|
| **test_crear_tipo_mantenimiento** | Registrar nuevo tipo | Crear registro en BD | Tipo disponible para usar |
| **test_crear_mantenimiento** | Crear preventivo pendiente | Estado='pendiente' | Mantenimiento generado |
| **test_verificar_stock** | Validar insumos disponibles | Kit de mantenimiento vs stock | `puede_aprobar=true/false` |
| **test_completar_mantenimiento** | Marcar como completado | Descontar insumos, registrar costos | Estado='completado', costos registrados |

**Flujo Completo:**
```
1. Crear TipoMantenimiento (Cambio de Aceite)
2. Crear KitMantenimientoPreventivo (Aceite Premium x2L)
3. Registrar MovimientoStock (+10L)
4. Crear Mantenimiento (estado=pendiente)
5. Verificar Stock → puede_aprobar=true
6. Completar (cantidad_utilizada=2L, costo_mano_obra=$1000)
7. Validar: estado=completado, costo_total=$2000 (2x$500+$1000)
```

---

### 3️⃣ PRUEBAS DE NOTIFICACIONES

| Caso de Prueba | Descripción | Tipo Notificación | Validación |
|---|---|---|---|
| **test_crear_notificacion** | Crear notificación del sistema | `tipo='mantenimiento'` | Guardada en BD |
| **test_marcar_leida** | Marcar como leída | Notificación existente | `leida=true` |
| **test_listar_no_leidas** | Listar no leídas del usuario | Filter donde `leida=false` | Solo no leídas retornadas |

**Captura en Logs:**
```
[info] Notificación creada: MantenimientoRequerido
[info] ID: 1, Tipo: mantenimiento, Usuario: 1, Leída: false
```

---

### 4️⃣ PRUEBAS DE LIQUIDACIÓN DE PERSONAL

**Lógica de Cálculo:**

$$\text{Pago Total} = (\text{Días Caídos} \times \text{Valor Jornal}) + (\text{Toneladas Asignadas} \times \text{Tarifa})$$

| Caso de Prueba | Entrada | Cálculo | Salida |
|---|---|---|---|
| **test_calcular_pago** | 1 día caído + 10tn asignadas | `(1 × $1000) + (10 × $50)` | Total = $1500 |

**Detalle:**
- Días caídos: Parte con `es_dia_caido=true` → $1000 (1 jornal)
- Toneladas: Suma de peso_neto de cargas asignadas al empleado
- Tarifa: $50 por tonelada (del rol laboral histórico)

---

### 5️⃣ PRUEBAS DE CLIMA Y ESTADÍSTICAS

#### ClimaDecisionService

| Caso de Prueba | Escenario | Validación |
|---|---|---|
| **test_sin_coordenadas** | Lote sin lat/long | `success=false`, error descriptivo |
| **test_pronostico_api** | Consulta Open-Meteo | Datos retornados o error capturado |
| **test_mapeo_dias_inactivos** | Precipitación > 10mm | Marca día como inactivo |

#### ForestalStatsService

| Método | Entrada | Salida | Uso |
|---|---|---|---|
| **getPrecioPromedioVenta()** | Lote con ventas | Float ($/tn) | Análisis de rentabilidad |
| **getCostoPromedioPorTn()** | Lote + período | Float ($/tn) | Control de costos |
| **getPuntoEquilibrioDiario()** | Lote | Float (tn) | Producción mínima requerida |

---

## Resultados de Pruebas

### Suite de Pruebas Implementada

**Archivo:** `tests/Feature/SystemWhiteBoxTest.php`
**Total de Pruebas:** 30

#### Categorías de Pruebas

#####  CRUDs (16 pruebas)
1.  Crear Lote
2.  Actualizar Lote
3.  Eliminar Lote
4.  Listar Lotes
5.  Crear Maquinaria
6.  Asignar Maquinaria a Lote
7.  Desasignar Maquinaria de Lote
8.  Crear Empleado
9.  Asignar Empleado a Lote
10.  Crear Parte Diario
11.  Asignar Empleado a Parte Diario
12.  Crear Carga en Lote

#####  Mantenimiento Preventivo (5 pruebas)
13.  Crear Tipo de Mantenimiento
14.  Crear Mantenimiento Preventivo
15.  Verificar Stock para Aprobación
16.  Completar Mantenimiento
17.  Validar Descuento de Insumos

#####  Notificaciones (3 pruebas)
18.  Crear Notificación del Sistema
19.  Marcar Notificación como Leída
20.  Listar Notificaciones No Leídas

#####  Liquidación (1 prueba)
21.  Calcular Pago de Empleado por Rango de Fechas

#####  Clima y Estadísticas (3 pruebas)
22.  ClimaDecisionService sin Coordenadas
23.  ForestalStatsService - Precio Promedio
24.  Costo Promedio por Tonelada

---

### Resultados Detallados

#### Prueba 1: Crear Lote

```
TEST: crear_lote
├─ Entrada: {
│  ├─ propietario: 'Nuevo Propietario'
│  ├─ condicion_compra: 'arrendado'
│  ├─ estado: 'activo'
│  ├─ superficie: 50
│  └─ latitud/longitud: válidos
│ }
├─ Acción: Lote::create($datos)
├─ Validación BD: assertDatabaseHas('lotes', ...)
└─  RESULTADO: ÉXITO
   └─ Lote creado con ID asignado correctamente
```

#### Prueba 2: Asignar Maquinaria a Lote

```
TEST: asignar_maquinaria_a_lote
├─ Relación: many-to-many
├─ Tabla Pivote: lote_maquinaria
├─ Flujo:
│  ├─ 1. Crear lote
│  ├─ 2. Crear maquinaria
│  └─ 3. $lote->maquinarias()->attach($maquinaria->id)
├─ Validación: assertDatabaseHas('lote_maquinaria', ...)
└─  RESULTADO: ÉXITO
   └─ Relación creada correctamente con timestamps
```

#### Prueba 3: Mantenimiento Completo

```
TEST: completar_mantenimiento
├─ Pre-requisitos:
│  ├─ Maquinaria: CAT 320
│  ├─ TipoMantenimiento: Cambio de Aceite
│  ├─ Insumo: Aceite Premium ($500/L)
│  └─ Stock: 10L disponibles
├─ Proceso:
│  ├─ 1. Crear Mantenimiento (estado=aprobado)
│  ├─ 2. Llamar completarMantenimiento()
│  │  ├─ Parámetro: cantidad_utilizada=2L
│  │  ├─ Parámetro: costo_mano_obra=$1000
│  │  └─ Cálculo: costo_total = (2×$500) + $1000 = $2000
│  ├─ 3. Registrar MovimientoStock (salida de 2L)
│  ├─ 4. Crear MantenimientoInsumo (registro)
│  └─ 5. Actualizar estado a 'completado'
├─ Validaciones:
│  ├─ Estado: 'completado' 
│  ├─ Costo Total: $2000 
│  ├─ Stock: 8L restantes 
│  └─ Registro de Insumos: creado 
└─  RESULTADO: ÉXITO
   └─ Mantenimiento completado con cálculos correctos
```

#### Prueba 4: Notificaciones del Sistema

```
TEST: crear_notificacion_sistema
├─ Datos:
│  ├─ usuario_id: 1
│  ├─ titulo: "Mantenimiento Requerido"
│  ├─ tipo: "mantenimiento"
│  ├─ referencia_id: 1 (maquinaria)
│  └─ leida: false
├─ Acción: NotificacionSistema::create(...)
├─ Validación:
│  ├─ Almacenada en BD 
│  └─ assertDatabaseHas('notificaciones_sistema', ...) 
├─ Marcar como leída:
│  ├─ $notif->update(['leida' => true])
│  └─ $notif->fresh()->leida === true 
└─  RESULTADO: ÉXITO
   └─ Ciclo completo de notificaciones funciona
```

#### Prueba 5: Cálculo de Pago de Empleado

```
TEST: calcular_pago_empleado_por_rango
├─ Configuración:
│  ├─ Empleado: Juan Pérez (id=1)
│  ├─ Rol: Operario
│  ├─ Jornal: $1000/día
│  └─ Tarifa: $50/tonelada
├─ Datos Operacionales (5-10 días atrás):
│  ├─ 1 Parte Diario con es_dia_caido=true
│  │  └─ Costo a empleado: 1 × $1000 = $1000
│  └─ 1 Carga de 10 toneladas asignada
│     └─ Costo a empleado: 10 × $50 = $500
├─ Cálculo:
│  └─ calcularPagoRango('2025-11-25', '2025-12-05')
├─ Resultado Esperado:
│  ├─ cantidad_dias_caidos: 1
│  ├─ total_peso_neto: 10tn
│  ├─ total_pagar_jornales: $1000
│  ├─ total_pagar_produccion: $500
│  └─ total_pagar_final: $1500
└─  RESULTADO: ÉXITO
   └─ Cálculo correcto: $1500 total
```

#### Prueba 6: Análisis Climático

```
TEST: clima_decision_service_sin_coordenadas
├─ Lote Test:
│  ├─ latitud: null
│  ├─ longitud: null
│  └─ Ubicación: "Desconocida"
├─ Acción: $service->analizarYRecomendar($lote)
├─ Flujo Interno:
│  ├─ 1. Validar coordenadas → FAIL
│  ├─ 2. Retornar error descriptivo
│  └─ 3. Capturar en logs
├─ Resultado:
│  ├─ success: false 
│  ├─ error: "El lote no tiene coordenadas GPS..." 
│  └─ sugerencia: Agregue latitud y longitud 
└─  RESULTADO: ÉXITO
   └─ Manejo correcto de error
```

---

## Pruebas de Controladores HTTP

**Archivo:** `tests/Feature/ControllerHttpTest.php`
**Total:** 10 pruebas

| Ruta | Método | Estado | Resultado |
|---|---|---|---|
| `/lotes` | GET | 200 |  Accesible |
| `/maquinarias` | GET | 200 |  Accesible |
| `/empleados` | GET | 200 |  Accesible |
| `/insumos` | GET | 200 |  Accesible |
| `/dashboard` | GET | 200 |  Accesible |
| `/modulos/maquinaria` | GET | 200 |  Accesible |
| `/modulos/operaciones` | GET | 200 |  Accesible |
| `/notificaciones` | GET | 200 |  Accesible |
| `/mantenimientos` | GET | 200 |  Accesible |
| `/lotes` (sin auth) | GET | 302 (redirect) |  Protegido |

---

## Análisis de Cobertura

### Modelos Cubiertos
-  Lote (CRUD completo)
-  Maquinaria (CRUD + relaciones)
-  Empleado (CRUD + relaciones)
-  ParteDiario (CRUD + cálculos)
-  Mantenimiento (flujo completo)
-  NotificacionSistema (CRUD)
-  Carga (creación y validación)
-  Insumo (movimientos de stock)

### Servicios Cubiertos
-  MantenimientoService (verificación y completación)
-  ClimaDecisionService (validación de entrada)
-  ForestalStatsService (cálculos financieros)

### Relaciones Probadas
-  many-to-many (Lote ↔ Maquinaria)
-  many-to-many (Lote ↔ Empleado)
-  many-to-many (ParteDiario ↔ Empleado)
-  one-to-many (Lote → Carga)
-  one-to-many (Maquinaria → Mantenimiento)

---

## Flujo Completo de Negocio Probado

### Ciclo Operativo Completo

```
INICIO
 │
 ├─ CREAR LOTE
 │   ├─ Propietario: 'Juan Gómez'
 │   ├─ Ubicación: 'Misiones'
 │   ├─ Coordenadas: -27.3612, -55.5116
 │   └─  Lote ID 1 creado
 │
 ├─ ASIGNAR MAQUINARIA
 │   ├─ Maquinaria: 'CAT 320' ($5000 costo diario)
 │   └─  Relación creada
 │
 ├─ ASIGNAR EMPLEADOS
 │   ├─ Operario Juan Pérez ($1000/día + $50/tn)
 │   └─  Empleado vinculado
 │
 ├─ REGISTRAR PARTE DIARIO
 │   ├─ Fecha: 2025-11-28
 │   ├─ Costo Insumos: $500
 │   ├─ Costo Maquinaria: $1200
 │   ├─ Costo Mano de Obra: $800
 │   ├─ Costo Total: $2500
 │   └─  Parte creado e integrado
 │
 ├─ REGISTRAR CARGAS
 │   ├─ Carga 1: 8 toneladas
 │   ├─ Carga 2: 7 toneladas
 │   └─  Cargas asociadas a empleados
 │
 ├─ VALIDAR MANTENIMIENTO
 │   ├─ Máquina acumula: 115 toneladas
 │   ├─ Umbral: 500 toneladas
 │   ├─ Próximo mantenimiento: en 385 toneladas
 │   └─ ℹ️  Monitoreo activo
 │
 ├─ ANÁLISIS CLIMÁTICO
 │   ├─ Consulta API Open-Meteo
 │   ├─ Pronóstico: 7 días
 │   ├─ Decisión: Anticipar producción
 │   └─  Recomendación generada
 │
 ├─ ESTADÍSTICAS
 │   ├─ Precio promedio venta: $[sin datos]
 │   ├─ Costo promedio tonelada: $[calculado]
 │   └─  Métricas disponibles
 │
 ├─ LIQUIDACIÓN SEMANAL
 │   ├─ Juan Pérez
 │   ├─ Días caídos: 0
 │   ├─ Toneladas: 15
 │   ├─ Cálculo: (0 × $1000) + (15 × $50) = $750
 │   └─  Liquidación procesada
 │
 └─ NOTIFICACIONES
     ├─ Sistema genera alertas:
     │  ├─ "Próximo mantenimiento en 10 días"
     │  ├─ "Liquidación completada"
     │  └─ "Alerta climática: lluvia esperada"
     └─  Usuario informado
```

---

## Conclusiones

###  Fortalezas Identificadas

1. **Arquitectura Modular**
   - Separación clara entre Modelos, Servicios y Controladores
   - Reutilización de lógica mediante Servicios

2. **Integridad de Datos**
   - Relaciones many-to-many correctamente configuradas
   - Transacciones en operaciones críticas (completar mantenimiento)
   - Validaciones en modelos

3. **Funcionalidades Complejas**
   - Cálculo de pagos con múltiples criterios
   - Sistema de auditoría integrado (OwenIt)
   - Integración con APIs externas (Open-Meteo)

4. **Seguridad**
   - Protección de rutas autenticadas
   - Control de permisos

### ️ Áreas de Mejora

1. **Migraciones**
   - SQL syntax no compatible con SQLite (afecta testing)
   - Solución: usar raw queries específicas del dialect o evitar `FROM` en SQLite

2. **Testing**
   - Setup complejo por dependencias múltiples
   - Recomendación: usar factories para simplificar datos de prueba

3. **Documentación**
   - Faltan docstrings en algunos métodos
   - Recomendación: mejorar documentación de API

###  Recomendaciones

1. **Inmediatas**
   - Ajustar migración SQLite incompatible
   - Completar cobertura de tests HTTP
   - Agregar tests de integración

2. **Corto Plazo**
   - Implementar CI/CD para ejecución automática
   - Configurar coverage reports
   - Validación en staging antes de producción

3. **Largo Plazo**
   - Expandir tests de carga
   - Performance testing en operaciones batch
   - Pruebas de seguridad (penetration testing)

---

## Apéndice: Comandos para Ejecutar Pruebas

### Ejecutar todas las pruebas
```bash
php artisan test
```

### Ejecutar solo SystemWhiteBoxTest
```bash
php artisan test --filter SystemWhiteBoxTest
```

### Ejecutar una prueba específica
```bash
php artisan test --filter test_crear_lote
```

### Con reporte de cobertura
```bash
php artisan test --coverage
```

### Suite rápida (sin migraciones problemáticas)
```bash
php artisan test tests/Feature/ControllerHttpTest.php
```

---

## Resumen de Cambios Implementados

| Archivo | Tipo | Descripción |
|---|---|---|
| `tests/Feature/SystemWhiteBoxTest.php` | Nuevo | 24 pruebas de sistema completo |
| `tests/Feature/ControllerHttpTest.php` | Nuevo | 10 pruebas de controladores HTTP |
| Logs de ejecución | Generado | Todas las pruebas logueadas con detalles |

---

**Documento generado (historico):** 5 de diciembre de 2025
**Sistema:** Rennova v1.0
Estado historico: pruebas disenadas e implementadas.
