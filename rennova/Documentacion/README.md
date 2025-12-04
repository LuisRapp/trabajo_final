# 📚 Documentación del Sistema Rennova

## Índice de Documentación

### 🌦️ Sistemas Climáticos y Análisis

- **[SISTEMA_ANALISIS_CLIMATICO.md](./SISTEMA_ANALISIS_CLIMATICO.md)**  
  Sistema básico de análisis climático con Open-Meteo API para predecir días de lluvia.

- **[README_CLIMA_DECISION_SERVICE.md](./README_CLIMA_DECISION_SERVICE.md)**  
  Motor inteligente de decisiones climáticas con 3 fases: Anticipación, Bloqueo y Reacción.

- **[README_ANALISIS_CLIMATICO.md](./README_ANALISIS_CLIMATICO.md)**  
  Documentación adicional sobre análisis climático.

---

### 💰 Sistema de Costos y Pagos

- **[SISTEMA_CALCULO_COSTOS.md](./SISTEMA_CALCULO_COSTOS.md)**  
  Sistema completo de cálculo de costos operacionales (mano de obra, insumos, maquinaria).

- **[CALCULO_PAGOS_EMPLEADOS.md](./CALCULO_PAGOS_EMPLEADOS.md)**  
  Lógica de cálculo de pagos a empleados (jornal, destajo, días caídos).

- **[GUIA_LIQUIDACION_PAGOS.md](./GUIA_LIQUIDACION_PAGOS.md)**  
  Guía para liquidación de pagos mensuales.

- **[PRUEBAS_CALCULO_PAGOS.md](./PRUEBAS_CALCULO_PAGOS.md)**  
  Casos de prueba para validación de cálculos de pagos.

---

### 🔧 Sistema de Mantenimientos

- **[SISTEMA_MANTENIMIENTO_DOCS.md](./SISTEMA_MANTENIMIENTO_DOCS.md)**  
  Arquitectura y funcionamiento del sistema de mantenimientos preventivos y correctivos.

- **[MANUAL_MANTENIMIENTOS.md](./MANUAL_MANTENIMIENTOS.md)**  
  Manual de uso del sistema de mantenimientos.

- **[GUIA_MANTENIMIENTOS_USO.md](./GUIA_MANTENIMIENTOS_USO.md)**  
  Guía práctica para gestión de mantenimientos.

---

### 🔔 Sistema de Notificaciones

- **[SISTEMA_NOTIFICACIONES_INTERNAS.md](./SISTEMA_NOTIFICACIONES_INTERNAS.md)**  
  Sistema de notificaciones internas del sistema.

- **[INSTRUCCIONES_NOTIFICACIONES_EMAIL.md](./INSTRUCCIONES_NOTIFICACIONES_EMAIL.md)**  
  Configuración y uso de notificaciones por email.

- **[PRUEBA_NOTIFICACIONES.md](./PRUEBA_NOTIFICACIONES.md)**  
  Casos de prueba para el sistema de notificaciones.

---

### 🎨 Optimizaciones de UI/UX

- **[OPTIMIZACIONES_AVANZADAS_UI.md](./OPTIMIZACIONES_AVANZADAS_UI.md)**  
  Optimizaciones de rendimiento en interfaces (computed properties, Alpine.js, etc.).

- **[MEJORAS_PARTE_DIARIO.md](./MEJORAS_PARTE_DIARIO.md)**  
  Mejoras implementadas en el módulo de Parte Diario.

---

### 🔐 Permisos y Seguridad

- **[SPATIE_PERMISSION_SETUP.md](./SPATIE_PERMISSION_SETUP.md)**  
  Configuración del sistema de roles y permisos con Spatie Permission.

---

### 📖 Documentación General

- **[RESUMEN_PROYECTO.md](./RESUMEN_PROYECTO.md)**  
  Resumen general del proyecto y funcionalidades principales.

- **[INDEX.md](./INDEX.md)**  
  Índice general de documentación.

- **[TROUBLESHOOTING.md](./TROUBLESHOOTING.md)**  
  Solución de problemas comunes.

---

## 🧪 Scripts de Prueba Activos

Los siguientes scripts de prueba están disponibles en la raíz del proyecto:

- **test_clima_decision_service.php**  
  Prueba exhaustiva del motor de decisiones climáticas con 3 escenarios geográficos.

- **test_calculo_costos.php**  
  Validación del sistema de cálculo de costos operacionales.

---

## 🗂️ Scripts Auxiliares

Los siguientes scripts auxiliares están disponibles en `scripts/`:

- **ver_parte.php** - Visualizar detalles de un parte diario
- **ver_notif_mant.php** - Ver notificaciones de mantenimiento
- **ver_maquina.php** - Consultar información de maquinarias

---

## 📅 Última Actualización

**Fecha**: 3 de diciembre de 2025  
**Versión del Sistema**: Laravel 12.x + Livewire 3.x  
**Estado**: Documentación organizada y actualizada

---

## 💡 Cómo Usar Esta Documentación

1. **Desarrollo de nuevas funcionalidades**: Revisar documentos de sistemas relacionados
2. **Troubleshooting**: Consultar TROUBLESHOOTING.md y documentos específicos
3. **Onboarding de nuevos desarrolladores**: Comenzar por RESUMEN_PROYECTO.md e INDEX.md
4. **Testing**: Utilizar scripts de prueba disponibles para validar cambios

---

## 🔗 Enlaces Útiles

- **Repositorio**: trabajo_final (LuisRapp/ABMs)
- **Framework**: Laravel 12.x
- **UI**: Livewire 3.x + Alpine.js + Bootstrap 5
- **Base de Datos**: PostgreSQL
- **APIs Externas**: Open-Meteo (pronóstico climático)

---

**Nota**: Esta documentación se mantiene actualizada con cada iteración del proyecto. Para contribuir con nueva documentación, agregar archivos .md en esta carpeta y actualizar este índice.
