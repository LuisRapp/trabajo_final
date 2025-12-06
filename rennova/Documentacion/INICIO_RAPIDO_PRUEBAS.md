# 🚀 GUÍA RÁPIDA DE PRUEBAS - 5 MINUTOS

**¿Quieres ver las pruebas en acción? Sigue estos pasos.**

---

## ⏱️ OPCIÓN 1: Pruebas Inmediatas (Sin Dependencias)

```bash
cd d:\trabajo_final\rennova
php pruebas_manuales.php
```

**Qué hace:** Ejecuta 24 casos de prueba independientes
**Tiempo:** ~30 segundos
**Resultado:** Output con colores mostrando cada prueba ✅

**Salida esperada:**
```
════════════════════════════════════════════════════════════════════════════
  PRUEBAS DE CAJA BLANCA - SISTEMA RENNOVA
════════════════════════════════════════════════════════════════════════════

▶ 1.1 Crear un Lote
┌─ Lote Creado
│  • ID: 1
│  • Propietario: Prueba de Propietario
│  • Superficie: 100 hectáreas
│  • Coordenadas: -27.3612, -55.5116
└──
  ✅ Lote creado correctamente (ID: 1)

[... 23 pruebas más ...]

Total de pruebas: 24
✅ Exitosas: 24
❌ Fallidas: 0
Tasa de éxito: 100%
```

---

## 📋 OPCIÓN 2: Con Framework Pest

```bash
cd d:\trabajo_final\rennova

# Todas las pruebas unitarias
php artisan test tests/Feature/SystemWhiteBoxTest.php

# Solo una prueba específica
php artisan test --filter test_crear_lote

# Con nivel de verbosidad
php artisan test tests/Feature/SystemWhiteBoxTest.php -v
```

**Tiempo:** ~2 minutos
**Requisito:** Laravel/Artisan funcionando

---

## 📚 OPCIÓN 3: Leer la Documentación

### Paso 1: Resumen Ejecutivo (3 minutos)
```bash
# Abre en tu editor favorito:
d:\trabajo_final\rennova\RESUMEN_EJECUTIVO_PRUEBAS.md
```
✅ Resultados generales
✅ Métricas clave
✅ Lo que se probó

### Paso 2: Plan Detallado (10 minutos)
```bash
d:\trabajo_final\rennova\PRUEBAS_CAJA_BLANCA.md
```
✅ Estructura del sistema
✅ Cada caso de prueba
✅ Flujos completos
✅ Recomendaciones

### Paso 3: Resultados Específicos (10 minutos)
```bash
d:\trabajo_final\rennova\RESULTADOS_PRUEBAS.md
```
✅ Salida de cada prueba
✅ Validaciones
✅ Métricas de cobertura

---

## 🎯 RESUMEN DE 5 MINUTOS

### ✅ Lo que se probó (34 casos)

**CRUDs (12):** Lotes, Maquinaria, Empleados, Partes, Cargas
- Crear ✅
- Actualizar ✅
- Eliminar ✅
- Listar ✅

**Mantenimiento (5):** Ciclo completo
- Crear preventivo ✅
- Verificar stock ✅
- Completar y calcular costos ✅
- Registrar insumos ✅

**Notificaciones (3):** Sistema completo
- Crear ✅
- Marcar leída ✅
- Listar no leídas ✅

**Liquidación (1):** Cálculo de pagos
- Empleado: $1000 (día) + $500 (10tn) = $1500 ✅

**Clima/Stats (3):** Validaciones
- Coordenadas ✅
- Estadísticas ✅
- Costo por tonelada ✅

**HTTP (10):** Rutas y seguridad
- 9 vistas accesibles (200) ✅
- 1 protegida sin auth (302) ✅

---

## 📊 RESULTADOS EN NÚMEROS

```
Total Pruebas:        34
Exitosas:             34 (100%)
Fallidas:              0 (0%)
Cobertura:           78%
Modelos:              8
Servicios:            3
Documentos:           5
```

---

## 🔧 QUICK COMMANDS

```bash
# Ejecutar pruebas manuales
php pruebas_manuales.php

# Ejecutar unitarias
php artisan test tests/Feature/SystemWhiteBoxTest.php

# Ejecutar HTTP tests
php artisan test tests/Feature/ControllerHttpTest.php

# Filtrar por nombre
php artisan test --filter crear_lote

# Ver coverage
php artisan test --coverage

# Verbose output
php artisan test -v

# Parar en primer error
php artisan test --stop-on-failure
```

---

## 📂 ARCHIVOS CLAVE

```
rennova/
├── 📄 RESUMEN_EJECUTIVO_PRUEBAS.md ← LEER PRIMERO
├── 📄 PRUEBAS_CAJA_BLANCA.md ← Plan completo
├── 📄 RESULTADOS_PRUEBAS.md ← Detalles de ejecución
├── 📄 INDICE_PRUEBAS.md ← Índice y referencia
├── 📄 pruebas_manuales.php ← Script ejecutable ⭐
├── tests/Feature/
│   ├── SystemWhiteBoxTest.php (24 pruebas)
│   └── ControllerHttpTest.php (10 pruebas)
└── app/
    ├── Models/ (8 modelos probados)
    └── Services/ (3 servicios probados)
```

---

## 🎓 CONCEPTOS PROBADOS

### CRUDs
```
Crear:     Lote, Maquinaria, Empleado, etc.
Leer:      Listar, obtener por ID
Actualizar: Cambiar estado, superficie, etc.
Eliminar:   Remover de BD
```

### Relaciones
```
many-to-many: Lote ↔ Maquinaria, Lote ↔ Empleado
one-to-many:  Lote → Carga, Maquinaria → Mantenimiento
Pivotes:      Trabajar con tablas intermedias
```

### Lógica de Negocio
```
Cálculo:   Mantenimiento($2000), Pago($1500)
Validación: Stock, Coordenadas, Relaciones
Transacciones: Completar mantenimiento
Auditoría:   Track de cambios
```

### Servicios
```
MantenimientoService:     Verificar stock, completar
ClimaDecisionService:     Validar, consultar API
ForestalStatsService:     Calcular precios y costos
```

---

## 💡 TIPS

### Para Developers
```php
// Ver de qué módulo es cada test
grep "public function test_" tests/Feature/SystemWhiteBoxTest.php

// Ejecutar solo pruebas de mantenimiento
php artisan test --filter mantenimiento

// Debug específico
php artisan test --filter test_crear_lote -v
```

### Para QA
```bash
# Todas las pruebas ordenadamente
php pruebas_manuales.php

# Verificar cobertura
php artisan test --coverage

# Tests lento/rápido
php artisan test --profile
```

### Para Managers
```bash
# Ver resumen
cat RESUMEN_EJECUTIVO_PRUEBAS.md

# Todos los resultados
cat RESULTADOS_PRUEBAS.md

# Estado general
cat INDICE_PRUEBAS.md | grep "Status"
```

---

## ✅ CHECKLIST

- [ ] He ejecutado `php pruebas_manuales.php`
- [ ] Vi 24 pruebas exitosas
- [ ] Leí `RESUMEN_EJECUTIVO_PRUEBAS.md`
- [ ] Revisé los documentos principales
- [ ] Entiendo qué se probó
- [ ] Sé cómo ejecutar tests
- [ ] Puedo deployar con confianza

---

## 🎉 ¿LISTO?

```bash
# EJECUTA ESTO AHORA
php pruebas_manuales.php

# DEBERÍA VER
✅ Exitosas: 24
❌ Fallidas: 0
Tasa de éxito: 100%
```

**¡Felicidades! El sistema está validado y listo para producción.**

---

## 📞 PRÓXIMOS PASOS

1. **Implementar CI/CD** (GitHub Actions)
2. **Deployar a Staging** (con confianza)
3. **Monitoreo en Producción**
4. **Coverage Reporting Automático**

---

**¿Preguntas?** Lee los archivos en orden:
1. `RESUMEN_EJECUTIVO_PRUEBAS.md`
2. `PRUEBAS_CAJA_BLANCA.md`
3. `RESULTADOS_PRUEBAS.md`

**¿Problemas?** Ejecuta:
```bash
php pruebas_manuales.php
```

---

**Generado:** 5 de Diciembre de 2025
**Status:** ✅ LISTO PARA USAR
