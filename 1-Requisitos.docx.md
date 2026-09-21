&nbsp;**Proyecto**

***Gestión Rennova***

**Documento de Requisitos del Sistema**

***Versión  00.04***

***Fecha: 20/09/2026***

Realizado por: Rapp Luis

Realizado para: Rennova

&nbsp;

**Lista de Cambios**

&nbsp;

| Nro | Fecha | Descripción | Autor |
| :---- | :---- | :---- | :---- |
| 0 | 25/04/25 | Comienzo de redacción de la documentación | Rapp Luis&nbsp; |
| 1 | 15/01/2026 | Refactorización de documentación | Rapp Luis&nbsp; |
| 2 | 27/04/2026 | Agregado: 10 Diagramas de Secuencia de Diseño GRASP (UC-61, UC-59, UC-65, UC-63, UC-62, UC-13, UC-41, UC-66, UC-01, UC-57) en DIAGRAMAS\_SECUENCIA\_DISENO.md. Arquitectura Controlador-thin, Expertos de dominio, GRASP patterns, transacciones por Repositorio. | Rapp Luis |
| 3 | 30/04/2026 | Actualizado el glosario de términos al formato de tabla con columnas Término, Categoría y Comentarios. | Rapp Luis |
| 4 | 20/09/2026 | Agregado: sección "Procesos Automatizados del Sistema" (mantenimiento por umbral, análisis climático, propuestas de asignación) con disparador, responsable, manejo de errores y visibilidad por proceso. Agregados RF-18..RF-21, IRQ-09/IRQ-10 y NFR-06 (referenciados por la matriz de trazabilidad). Corregida la matriz: referencias RF-58/RF-59 por UC-58/UC-59. | Rapp Luis |

&nbsp;

**Índice**

&nbsp;

**[Índice de Figuras	3](#índice-de-figuras)**

[**Presentación General	4**](#presentación-general)

[**Subsistemas del Proyecto	7**](#subsistemas-del-proyecto)

[Figura 1 \- Diagrama de Subsistemas	7](#figura-1---diagrama-de-subsistemas)

[**Diagrama de Caso de Uso del Sistema	10**](#diagrama-de-caso-de-uso-del-sistema)

[Figura 2 \- Diagrama Caso Usos: Principal	10](#figura-2---diagrama-caso-usos:-principal)

[Figura 3 \- Diagrama Caso Usos: Producción(detalles)	11](#figura-3---diagrama-caso-usos:-producción\(detalles\))

[Figura 4 \- Diagrama Caso Usos: Maquinaria y Mantenimiento(detalles)	11](#figura-4---diagrama-caso-usos:-maquinaria-y-mantenimiento\(detalles\))

[**Casos de Usos	12**](#casos-de-usos)

[**Objetivos de la Iteración	15**](#objetivos-de-la-iteración)

[**Requisitos del Sistema	18**](#requisitos-del-sistema)

[**Requisitos de Funcionales	26**](#requisitos-de-funcionales)

[**Procesos Automatizados del Sistema	26b**](#procesos-automatizados-del-sistema)

[**Diagrama de Casos de Usos	35**](#diagrama-de-casos-de-usos)

[Figura 5 \- Diagrama Caso Usos: Principal	35](#figura-5---diagrama-caso-usos:-principal)

[Figura 6 \- Diagrama Caso Usos: Producción(detalles)	36](#figura-6---diagrama-caso-usos:-producción\(detalles\))

[Figura 7  \- Diagrama Caso Usos: Maquinaria y Mantenimiento(detalles)	36](#figura-7---diagrama-caso-usos:-maquinaria-y-mantenimiento\(detalles\))

[**Definición de Actores	37**](#definición-de-actores)

[**Casos de uso del Sistema	38**](#casos-de-uso-del-sistema)

[**Diagramas de Secuencia de Diseño:	101**](#diagramas-de-secuencia-de-diseño:)

[Figura 8: UC-61: Cargar Parte Diario (Operación Crítica de Producción)	101](#figura-8:-uc-61:-cargar-parte-diario-\(operación-crítica-de-producción\))

[Figura 9: UC-59: Liquidar Pagos (Complejidad de Reglas Financieras)	101](#figura-9:-uc-59:-liquidar-pagos-\(complejidad-de-reglas-financieras\))

[Figura 10: UC-65: Planificación de Tareas por Lote (Soporte Operativo)	102](#figura-10:-uc-65:-planificación-de-tareas-por-lote-\(soporte-operativo\))

[Figura 12: UC-62: Cerrar Orden de Mantenimiento (Cierre Complejo con FIFO)	103](#figura-12:-uc-62:-cerrar-orden-de-mantenimiento-\(cierre-complejo-con-fifo\))

[Figura 13: UC-13: Alta Venta	103](#figura-13:-uc-13:-alta-venta)

[Figura 14: UC-41: Alta Carga	104](#figura-14:-uc-41:-alta-carga)

[Figura 15: UC-66: Gestionar Asignaciones y Propuestas	105](#figura-15:-uc-66:-gestionar-asignaciones-y-propuestas)

[Figura 16: UC-01: Alta Lote	105](#figura-16:-uc-01:-alta-lote)

[Figura 17: UC-57: Informes Generales	106](#figura-17:-uc-57:-informes-generales)

[**Requisitos No funcionales	107**](#requisitos-no-funcionales)

[**Matriz de Rastreabilidad Objetivo/Requisitos	109**](#matriz-de-rastreabilidad-objetivo/requisitos)

[**Glosario de Términos	109**](#glosario-de-términos)

&nbsp;

### **Índice de Figuras** {#índice-de-figuras}

**[Figura 1 \- Diagrama de Subsistemas	7](#figura-1---diagrama-de-subsistemas)**

[**Figura 2 \- Diagrama Caso Usos: Principal	10**](#figura-2---diagrama-caso-usos:-principal)

[**Figura 3 \- Diagrama Caso Usos: Producción(detalles)	11**](#figura-3---diagrama-caso-usos:-producción\(detalles\))

[**Figura 4 \- Diagrama Caso Usos: Maquinaria y Mantenimiento(detalles)	11**](#figura-4---diagrama-caso-usos:-maquinaria-y-mantenimiento\(detalles\))

[**Figura 5 \- Diagrama Caso Usos: Principal	35**](#figura-5---diagrama-caso-usos:-principal)

[**Figura 6 \- Diagrama Caso Usos: Producción(detalles)	36**](#figura-6---diagrama-caso-usos:-producción\(detalles\))

[**Figura 7  \- Diagrama Caso Usos: Maquinaria y Mantenimiento(detalles)	36**](#figura-7---diagrama-caso-usos:-maquinaria-y-mantenimiento\(detalles\))

[**Figura 8: UC-61: Cargar Parte Diario (Operación Crítica de Producción)	101**](#figura-8:-uc-61:-cargar-parte-diario-\(operación-crítica-de-producción\))

[**Figura 9: UC-59: Liquidar Pagos (Complejidad de Reglas Financieras)	101**](#figura-9:-uc-59:-liquidar-pagos-\(complejidad-de-reglas-financieras\))

[**Figura 10: UC-65: Planificación de Tareas por Lote (Soporte Operativo)	102**](#figura-10:-uc-65:-planificación-de-tareas-por-lote-\(soporte-operativo\))

[**Figura 12: UC-62: Cerrar Orden de Mantenimiento (Cierre Complejo con FIFO)	103**](#figura-12:-uc-62:-cerrar-orden-de-mantenimiento-\(cierre-complejo-con-fifo\))

[**Figura 13: UC-13: Alta Venta	103**](#figura-13:-uc-13:-alta-venta)

[**Figura 14: UC-41: Alta Carga	104**](#figura-14:-uc-41:-alta-carga)

[**Figura 15: UC-66: Gestionar Asignaciones y Propuestas	105**](#figura-15:-uc-66:-gestionar-asignaciones-y-propuestas)

[**Figura 16: UC-01: Alta Lote	105**](#figura-16:-uc-01:-alta-lote)

[**Figura 17: UC-57: Informes Generales	106**](#figura-17:-uc-57:-informes-generales)

&nbsp;

&nbsp;

1. ### ***Presentación General*** {#presentación-general}

Renova enfrenta la necesidad de crecer en el mercado maderero argentino, en un contexto donde la industria busca mejorar la eficiencia mediante el control sobre sus operaciones. Actualmente, la empresa carece de un sistema unificado para gestionar y auditar sus procesos forestales, logísticos y financieros.&nbsp;

Para responder a estas limitaciones, se desarrollará un sistema de software que integre las distintas áreas que conforman la empresa, de esta manera se busca que cubra control sobre aspectos operativos, gestión de personal, estadísticas financieras, flujo de caja y otros conceptos administrativos, para poder lograr centralizar la información de manera ordenada y útil.

2. ***Participantes del Proyecto***

Debe contener una lista con todos los participantes en el proyecto:

Desarrolladores: Rapp Luis

Clientes: Rapp Marcelo

3. ***Objetivos del sistema***

Se debe hacer una lista con los objetivos que se esperan alcanzar con el software a desarrollar.

&nbsp;

&nbsp;

| OBJ–01&nbsp; | Optimización y unificación de la gestión operativa y administrativa. |
| :---- | :---- |
| **Descripción**&nbsp; | Proveer a los actores del negocio una plataforma unificada para gestionar la producción, insumos, gastos, ingresos y personal. El valor aportado radica en eliminar la dispersión de datos, asegurar la trazabilidad y facilitar la toma de decisiones. |
| **Estabilidad**&nbsp; | Alta |
| **Comentarios**&nbsp; | Constituye el núcleo del sistema. Esta característica de alto nivel se desglosará posteriormente en múltiples Casos de Uso específicos para cada área administrativa |

&nbsp;

&nbsp;

| OBJ–02 | Soporte analítico para la toma de decisiones estratégicas |
| :---- | :---- |
| **Descripción**&nbsp; | Dotar a la gerencia de herramientas de monitoreo del desempeño operativo, financiero y administrativo, mediante la visualización de reportes e indicadores (KPIs). El sistema transformará los datos centralizados en información estratégica que aporte valor observable a los directivos |
| **Estabilidad**&nbsp; | Media |
| **Comentarios**&nbsp; | Mientras que la necesidad de análisis es Alta (estable), los KPIs específicos y el formato de los reportes tienen una estabilidad Media/Baja, ya que son reglas e informes del dominio propensos a cambiar con la evolución de la empresa |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| OBJ–03&nbsp; | Automatización y optimización de la liquidación de nóminas. |
| :---- | :---- |
| **Descripción**&nbsp; | Proveer a los actores del área de Recursos Humanos la capacidad de generar las liquidaciones de pago de forma automática, evaluando la productividad y asistencia. Este objetivo busca reducir la carga operativa y los errores manuales del negocio |
| **Estabilidad**&nbsp; | Media |
| **Comentarios**&nbsp; | El objetivo de pagar es altamente estable, pero las reglas exactas de cálculo de pagos se definirán y gestionarán como Reglas de Negocio separadas, dada su naturaleza sujeta a variaciones legales o corporativas |

&nbsp;

&nbsp;

| OBJ–04&nbsp; | Control y liquidación de costos operativos por alquiler de maquinaria. |
| :---- | :---- |
| **Descripción**&nbsp; | Facilitar al área administrativa el seguimiento automatizado y cálculo de los costos de alquiler de maquinaria, utilizando variables como el volumen de producción y el tipo de máquina. El valor aportado es mantener un control financiero preciso de la extracción |
| **Estabilidad**&nbsp; | Baja&nbsp; |
| **Comentarios**&nbsp; | ninguno&nbsp; |

&nbsp;

| OBJ–05&nbsp; | Garantía de trazabilidad y seguridad mediante auditoría de transacciones. |
| :---- | :---- |
| **Descripción**&nbsp; | Asegurar a nivel de sistema que todas las acciones críticas (ingresos, modificaciones, eliminaciones de datos) realizadas por los usuarios queden registradas. Esto otorga valor al mitigar riesgos de seguridad y cumplir con las políticas de control interno de la empresa |
| **Estabilidad**&nbsp; | Alta&nbsp; |
| **Comentarios**&nbsp; | Metodológicamente, este es un requisito no funcional. |

### **Subsistemas del Proyecto** {#subsistemas-del-proyecto}

**Diagrama de los Subsistemas**

![][image1]

#### **Figura 1 \- Diagrama de Subsistemas** {#figura-1---diagrama-de-subsistemas}

&nbsp;

**Descripción de  subsistema**

1. **Subsistema de Producción**

**Responsabilidades**: Gestiona la operación forestal a nivel de lote y día: ABM de lotes y su estado operativo (activo/en\_proceso/cerrado), planificación/seguimiento de tareas por lote (\`LoteTarea\`), registración de partes diarios con validaciones de ventana temporal (no futuro y no más de 7 días), captura de producción por cargas (peso bruto/tara/peso neto, destino, chofer, categoría de madera), asignación de empleados y maquinarias al lote/parte/carga, y soporte de decisiones operativas basadas en clima (pronóstico, días inactivos, estrategia/recomendaciones) y recomendaciones de asignación automática (propuestas con estimaciones y candidatos).&nbsp;&nbsp;

**Interfaz proporcionada**: El subsistema de Producción expone sus funcionalidades mediante componentes responsables de la gestión de lotes, el registro de partes diarios y la administración de cargas de producción. Asimismo, proporciona mecanismos para consultar recomendaciones operativas y gestionar propuestas de asignación de recursos.

La lógica de negocio se organiza en servicios encargados del análisis de condiciones operativas, la evaluación de factores climáticos y la generación de propuestas automáticas de asignación.

Las principales entidades del dominio asociadas a este subsistema incluyen Lote, LoteTarea, ParteDiario, Carga y AllocationProposal.

**Dependencias**: El subsistema de Producción depende del subsistema de Recursos Humanos para la asignación de empleados a las actividades operativas y el cálculo de costos laborales asociados. También depende del subsistema de Maquinaria y Equipos para gestionar la asignación y el uso de maquinaria durante las operaciones.

Asimismo, interactúa con el subsistema de Gestión Administrativa y Stock para registrar el consumo de insumos utilizados en la operación diaria.

La comunicación con otros subsistemas incluye mecanismos asincrónicos basados en eventos y procesos en segundo plano para la actualización del uso de maquinaria y la generación automática de propuestas de asignación de recursos. Finalmente, el subsistema utiliza servicios técnicos transversales de infraestructura, como gestión de transacciones de base de datos, registro de eventos (logging), integración con servicios externos de información climática y mecanismos de auditoría de entidades.

2. **Subsistema de Maquinaria y Equipos**

**Responsabilidades**: Gestiona el ciclo de vida de la maquinaria y sus mantenimientos: estado de maquinarias, tipo de maquinaria y su precio de alquiler por destajo, control de toneladas acumuladas (odómetro), programación/ejecución/cierre de mantenimientos, verificación de stock de kits preventivos, descuento de insumos usados, cálculo de costos del mantenimiento y generación de documentos (orden de mantenimiento/compra en PDF).

**Interfaz proporcionada**: El subsistema de Maquinaria y Equipos expone funcionalidades para la gestión de maquinarias, la administración de mantenimientos y la generación de documentación asociada a estas operaciones. Estas capacidades permiten registrar, aprobar y completar tareas de mantenimiento, así como gestionar los distintos tipos de maquinaria y sus configuraciones operativas.

**Dependencias**: Este subsistema depende del módulo de Gestión Administrativa y Stock para registrar el consumo de insumos utilizados durante las tareas de mantenimiento. También colabora con el subsistema de Producción, ya que los costos asociados al uso y mantenimiento de la maquinaria se integran en los cálculos de costos operativos diarios. 						Además, utiliza mecanismos de comunicación asincrónica para actualizar el uso acumulado de las maquinarias y emplea servicios técnicos transversales del sistema, como logging y auditoría de entidades.

&nbsp;

3. **Subsistema de Recursos Humanos**

**Responsabilidades**: Gestiona empleados y su costo laboral: cálculo de costo por día (jornal vs destajo según producción/día caído), cálculo de pagos en rango de fechas, soporte de historización de tarifas/valores por rol laboral (vigencias) y vínculo con asignaciones a lotes/partes/mantenimientos vía tablas pivote.	**Interfaz proporcionada (puntos de entrada lógicos):** El subsistema de Recursos Humanos expone funcionalidades para la gestión de empleados, la administración de roles laborales y el cálculo de costos o pagos asociados al trabajo realizado. También permite gestionar el historial de roles y registrar liquidaciones o comprobantes vinculados a la actividad laboral.&nbsp;

**Dependencias:** Este subsistema depende del subsistema de Producción para obtener información sobre las actividades registradas en partes diarios y cargas, lo que permite calcular pagos por producción y validar registros laborales según las fechas de trabajo. Asimismo, colabora con el subsistema Financiero y de Costos al proporcionar información sobre liquidaciones y costos de mano de obra utilizados en cálculos económicos e indicadores. Además, utiliza servicios técnicos transversales del sistema, como mecanismos de auditoría de entidades y registro de eventos (logging).

&nbsp;

4. **Subsistema Financiero y de Costos**  
   **Responsabilidades**: Consolida información económica y de performance forestal para reportes: calcula precio promedio real de venta por tonelada, costo promedio por tonelada (con desgloses), punto de equilibrio, series temporales (producción diaria, evolución de costo) y genera reportes visuales y PDFs.  
   **Interfaz proporcionada**: El subsistema Financiero y de Costos expone funcionalidades para el análisis económico de la operación, incluyendo el cálculo de indicadores clave, la consolidación de información de producción y ventas, y la generación de reportes y documentos asociados a la actividad operativa.								**Dependencias**: Este subsistema depende del subsistema de Producción para obtener información sobre niveles de producción, toneladas procesadas y costos operativos registrados en las actividades diarias. También depende del subsistema de Recursos Humanos al incorporar datos de liquidaciones y costos laborales. 								Asimismo, depende de manera indirecta del subsistema de Maquinaria y Equipos, ya que los costos de uso y mantenimiento de maquinaria influyen en los cálculos económicos. Finalmente, utiliza servicios técnicos de infraestructura para el acceso a datos, optimización de consultas y generación de reportes en formato documental.  
   &nbsp;  
5. **Subsistema de Gestión Administrativa**  
   **Responsabilidades**: Administra maestros comerciales (clientes, proveedores), insumos y control de inventario/stock con lotes FIFO: registra entradas (compra/ajustes/devoluciones), calcula stock disponible y precio promedio, mantiene lotes de inventario y trazabilidad de consumos mediante movimientos; además soporta compras/documentación asociada a mantenimiento (propuesta de compra y envío de orden por email/PDF).  
   **Interfaz proporcionada**:El subsistema de Gestión Administrativa expone funcionalidades para la administración de clientes, proveedores e insumos, así como para el control de inventario y movimientos de stock. También proporciona mecanismos para registrar entradas y salidas de insumos, mantener la trazabilidad del inventario y gestionar procesos de compra asociados a actividades operativas.  
   **Dependencias**: Este subsistema depende de la infraestructura de persistencia para la gestión de inventario y el cálculo de disponibilidad de stock. Asimismo, mantiene interacción con el subsistema de Maquinaria y Equipos para registrar el consumo de insumos utilizados en tareas de mantenimiento.  
   Además, se integra con el subsistema de Producción en procesos de planificación y en la generación de propuestas de asignación o compra vinculadas a la operación. Finalmente, utiliza servicios técnicos transversales del sistema, como mecanismos de logging, notificaciones y auditoría de entidades.

&nbsp;

   4. 

   5. ### **Diagrama de Caso de Uso del Sistema** {#diagrama-de-caso-de-uso-del-sistema}

   6. ***![][image2]***

   7. #### **Figura 2 \- Diagrama Caso Usos: Principal** {#figura-2---diagrama-caso-usos:-principal}

&nbsp;![][image3]

#### **Figura 3 \- Diagrama Caso Usos: Producción(detalles)** {#figura-3---diagrama-caso-usos:-producción(detalles)}

![][image4]

#### **Figura 4 \- Diagrama Caso Usos: Maquinaria y Mantenimiento(detalles)** {#figura-4---diagrama-caso-usos:-maquinaria-y-mantenimiento(detalles)}

&nbsp;

### **Casos de Usos**&nbsp; {#casos-de-usos}

* Alta: caso de uso crítico de operación diaria; si falla, bloquea producción/mantenimiento/liquidación.  
* Media: necesario para operar con frecuencia o para control/gestión, pero no bloquea inmediatamente el día a día.  
* Baja: configuración o ABM esporádico (maestros/seguridad) y tareas poco frecuentes.

&nbsp;

| Nro Caso de uso | Nombre | Nivel de Prioridad |
| ----- | ----- | ----- |
| 1 | Alta Lote | Alta |
| 2 | Baja Lote | Media |
| 3 | Modificar Lote | Alta |
| 4 | Ver Lote | Alta |
| 5 | Alta Insumo | Media |
| 6 | Baja Insumo | Baja |
| 7 | Modificar Insumo | Media |
| 8 | Ver Insumo | Alta |
| 9 | Alta Maquinaria | Media |
| 10 | Baja Maquinaria | Baja |
| 11 | Modificar Maquinaria | Media |
| 12 | Ver Maquinaria | Media |
| 13 | Alta Venta | Media |
| 14 | Baja Venta | Baja |
| 15 | Modificar Venta | Media |
| 16 | Ver Venta | Media |
| 21 | Alta Empleado | Media |
| 22 | Baja Empleado | Baja |
| 23 | Modificar Empleado | Media |
| 24 | Ver Empleado | Media |
| 25 | Alta Cliente | Media |
| 26 | Baja Cliente | Baja |
| 27 | Modificar Cliente | Media |
| 28 | Ver Cliente | Media |
| 29 | Alta Proveedor | Media |
| 30 | Baja Proveedor | Baja |
| 31 | Modificar Proveedor | Media |
| 32 | Ver Proveedor | Media |
| 33 | Alta Chofer | Media |
| 34 | Baja Chofer | Baja |
| 35 | Modificar Chofer | Media |
| 36 | Ver Chofer | Media |
| 37 | Alta Stock Insumo | Alta |
| 38 | Baja Stock Insumo | Media |
| 39 | Modificar Stock Insumo | Media |
| 40 | Ver Stock Insumo | Alta |
| 41 | Alta Carga | Alta |
| 42 | Baja Carga | Media |
| 43 | Modificar Carga | Alta |
| 44 | Ver Carga | Alta |
| 45 | Alta Categoria | Baja |
| 46 | Baja Categoria | Baja |
| 47 | Modificar Categoria | Baja |
| 48 | Ver Categoria | Baja |
| 49 | Alta Usuario | Baja |
| 50 | Baja Usuario | Baja |
| 51 | Modificar Usuario | Baja |
| 52 | Ver Usuario | Baja |
| 53 | Alta Adelanto | Media |
| 54 | Baja Adelanto | Media |
| 55 | Modificar Adelanto | Media |
| 56 | Ver Adelanto | Media |
| 57 | Informes generales | Media |
| 58 | Generar Recibos | Alta |
| 59 | Liquidar Pagos | Alta |
| 61 | Cargar Parte diario | Alta |
| 62 | Cerrar orden de mantenimiento | Alta |
| 63 | Programar mantenimiento | Media |
| 64 | Configurar permisos | Baja |
| 65 | Planificacion de tareas por lote (ha) | Media |
| 66 | Gestionar asignaciones y propuestas | Media |
| 67 | Configurar notificaciones de mantenimiento | Baja |
| 68 | Gestionar catalogos y listas de precios | Baja |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

**Proyecto**

***Proyecto Rennova***

**Documento de Requisitos del Sistema**

***Versión  00.04***

***Fecha: 20/09/2026***

Realizado por: Rapp Luis

Realizado para: Rennova

&nbsp;

8. ### ***Objetivos de la Iteración*** {#objetivos-de-la-iteración}

Se debe hacer una lista con los objetivos que se esperan alcanzar con el software a desarrollar.

&nbsp;

| OBJ–01 | Centralización e integración de registros operativos y administrativos |
| :---- | :---- |
| **Descripción**&nbsp; | Proveer una plataforma unificada para el negocio, eliminando la dispersión actual de datos en planillas manuales. Esto aporta valor estratégico al garantizar la consistencia y mejorar la trazabilidad global de la información en todas las áreas de la empresa. |
| **Estabilidad**&nbsp; | Alta&nbsp; |
| **Comentarios**&nbsp; | Este es un objetivo de nivel de empresa que justifica la inversión en el sistema y atraviesa todas las funcionalidades. |

&nbsp;

&nbsp;

| OBJ–02 | Soporte operativo y trazabilidad de la producción forestal |
| :---- | :---- |
| **Descripción**&nbsp; | Dotar al Personal Administrativo de herramientas para la planificación de tareas por lote (ha), y al Capataz/Supervisor de la capacidad de registrar partes diarios (y asociar/registrar cargas cuando corresponda). Aporta valor al asegurar la trazabilidad de la madera e incorporar soporte operativo mediante la consulta y análisis del clima. |
| **Estabilidad**&nbsp; | Alta&nbsp; |
| **Comentarios**&nbsp; | Las reglas de validación climática y de días no operativos se gestionan como flujos y variaciones dentro de la lógica de los partes diarios. |

&nbsp;

| OBJ–03 | Gestión del mantenimiento y control de costos de maquinaria. |
| :---- | :---- |
| **Descripción**&nbsp; | Permitir el registro de máquinas y equipos, la programación y cierre de mantenimientos preventivos y correctivos, y el envío de notificaciones asociadas. Incluye administrar costos históricos por tonelada e imputarlos en los costos de la maquinaria. |
| **Estabilidad**&nbsp; | Alta |
| **Comentarios**&nbsp; | \- |

&nbsp;

| OBJ–04 | Automatización de la liquidación de nóminas de Recursos Humanos |
| :---- | :---- |
| **Descripción**&nbsp; | Facilitar al área administrativa la liquidación automática de pagos al personal basándose en métricas de productividad y días trabajados, gestionando además el registro de adelantos y la generación de recibos. |
| **Estabilidad**&nbsp; | Media |
| **Comentarios**&nbsp; | Las fórmulas de liquidación dependen de leyes laborales y reglas de negocio corporativas, por lo que requerirán un diseño que soporte variaciones. |

&nbsp;

| OBJ–05 | Soporte analítico financiero y de costos |
| :---- | :---- |
| **Descripción**&nbsp; | Consolidar la información de ventas y los costos operativos (derivados de partes diarios, consumos de insumos, mantenimientos y liquidaciones) para generar reportes e indicadores (KPIs) estratégicos sobre costos, ingresos y rentabilidad. |
| **Estabilidad**&nbsp; | Media |
| **Comentarios**&nbsp; | Aunque la necesidad de reportes es estable, los tipos exactos de indicadores (KPIs) requeridos suelen evolucionar conforme madura la visión del negocio por parte de la gerencia |

&nbsp;

| OBJ–06 | Gestión administrativa comercial y de abastecimiento |
| :---- | :---- |
| **Descripción**&nbsp; | Proveer al Personal Administrativo la capacidad de gestionar clientes, proveedores, choferes y ventas. Incluye el control del stock de insumos con valorización FIFO, junto con la administración de catálogos (categorías/unidades/tipos) y listas de precios, garantizando el soporte material a la producción. |
| **Estabilidad**&nbsp; | Alta&nbsp; |
| **Comentarios**&nbsp; | \- |

&nbsp;

| OBJ–07 | Seguridad y auditoría funcional del sistema |
| :---- | :---- |
| **Descripción**&nbsp; | Proveer a los roles de soporte (Administrador) las interfaces para configurar usuarios, roles y permisos, incorporando un registro y consulta de auditoría de acciones (logs) para garantizar la trazabilidad, seguridad funcional y el control interno de los procesos. |
| **Estabilidad**&nbsp; | Alta&nbsp; |
| **Comentarios**&nbsp; | Abarca tanto los casos de uso de administración de usuarios como el cumplimiento de los atributos de calidad (seguridad) de la Especificación Complementaria. |

&nbsp;

9. 

   10. ### **Requisitos del Sistema** {#requisitos-del-sistema}

       1. *Requisitos de Información*

Debe tener una lista de requisitos de almacenamientos y de restricciones de información que se haya identificado.&nbsp;

&nbsp;

| IRQ–01&nbsp; | Información sobre lotes y cargas |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-02 Soporte operativo y trazabilidad de la producción forestal |  |
| **Requisitos asociados**&nbsp; | UC-01..UC-04 Gestión de lotes (Alta/Baja/Modificar/Ver Lote) UC-41..UC-44 Gestión de cargas (Alta/Baja/Modificar/Ver Carga) UC-33..UC-36 Gestión de choferes (Alta/Baja/Modificar/Ver Chofer) |  |
| **Descripción**&nbsp; | El sistema deberá almacenar toda la información vinculada a los lotes y cargas de madera, permitiendo asegurar la trazabilidad de la producción.&nbsp; |  |
| **Datos específicos**&nbsp; | Entidad: Lote Nombre Ubicación Especie Superficie (ha) Entidad Transaccional: Carga Nro de ticket Categoría de madera Chofer Peso bruto Tara Peso neto Destino |  |
|  |  |  |
| **Estabilidad**&nbsp; | Alta&nbsp; |  |
| **Comentarios**&nbsp; | \- |  |

&nbsp;

| IRQ–02 | Información sobre maquinaria y equipos |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-03 Gestión del mantenimiento y control de costos de maquinaria |  |
| **Requisitos asociados**&nbsp; | UC-09..UC-12 Gestión de maquinaria (Alta/Baja/Modificar/Ver Maquinaria) UC-62 Cerrar orden de mantenimiento UC-63 Programar mantenimiento |  |
| **Descripción**&nbsp; | El sistema deberá almacenar los datos relacionados con las máquinas físicas utilizadas en la producción y los registros transaccionales de su mantenimiento.&nbsp; |  |
| **Datos específicos**&nbsp; | Entidad Física: Maquinaria Identificador (Patente / Código interno) Tipo de máquina (Categoría) Estado operativo actual Fecha de alta en el sistema / Producción asociada acumulada (toneladas extraídas) / Costos históricos acumulados por tonelada Entidad Transaccional: Orden de Mantenimiento Máquina asociada (Referencia a la Maquinaria) Tipo de mantenimiento (Preventivo / Correctivo) Estado del mantenimiento (Programado / Cerrado) Fechas (Programada / Cierre) Costo de insumos imputados&nbsp;&nbsp; |  |
|  |  |  |
| **Estabilidad**&nbsp; | Media |  |
| **Comentarios**&nbsp; | Los atributos precedidos por una barra ("/") como "Producción asociada" y "Costos históricos" son atributos derivados. No se almacenarán como valores estáticos, sino que el sistema los calculará lógicamente en tiempo de ejecución sumando las cargas registradas en los partes diarios y los costos de las órdenes de mantenimiento cerradas, respectivamente. |  |

&nbsp;

| IRQ–03 | Información sobre empleados |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-04 Automatización de la liquidación de nóminas de Recursos Humanos |  |
| **Requisitos asociados**&nbsp; | UC-21..UC-24 Gestión de empleados (Alta/Baja/Modificar/Ver Empleado) UC-53..UC-56 Gestión de adelantos (Alta/Baja/Modificar/Ver Adelanto) UC-59 Liquidar Pagos UC-58 Generar Recibos&nbsp; |  |
| **Descripción**&nbsp; | El sistema deberá almacenar la información del personal de la empresa, necesaria para la gestión operativa y de pagos.&nbsp; |  |
| **Datos específicos**&nbsp; | Entidad Física: Empleado Nombre y apellido DNI / CUIT Datos de contacto Categoría laboral o rol / Historial de asignación de tareas (Derivado) / Jornales y productividad acumulada (Derivado) / Saldo actual de adelantos (Derivado) Entidad Transaccional: Adelanto Empleado solicitante (Referencia al Empleado) Fecha de solicitud Monto del adelanto Estado (ej. Pendiente de descuento, Descontado) Entidad Transaccional: Recibo Empleado liquidado Período de liquidación (fecha inicio y fin) Monto Bruto (basado en productividad/jornales) Descuentos aplicados (Adelantos deducidos) Monto Neto a pagar |  |
|  |  |  |
| **Estabilidad**&nbsp; | Alta&nbsp; |  |
| **Comentarios**&nbsp; | Los atributos precedidos por una barra ("/") en la entidad Empleado/Chofer son atributos derivados. Esto significa que el sistema no los almacena como campos estáticos, sino que los calcula dinámicamente consultando las transacciones vinculadas (Partes Diarios para la productividad, Adelantos para los saldos). |  |

&nbsp;

| IRQ–04 | Información financiera y de costos |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-05 Soporte analítico financiero y de costos |  |
| **Requisitos asociados**&nbsp; | UC-13..UC-16 Gestión de ventas (Alta/Baja/Modificar/Ver Venta) UC-Consultar Estadísticas Forestales UC-Generar Recibos UC-Liquidar Pagos UC-Registrar Parte Diario UC-37..UC-40 Gestión de stock de insumos (FIFO) UC-Gestionar Mantenimientos (Programar / Cerrar orden) |  |
| **Descripción**&nbsp; | El sistema deberá consolidar la información de ventas y costos operativos para generar reportes e indicadores (KPIs) de costos, ingresos y rentabilidad por lote y por período. Los ingresos, egresos y el flujo de caja se obtienen dinámicamente como métricas derivadas a partir de las transacciones operativas y de ventas registradas. |  |
| **Datos específicos**&nbsp; | Entidad Transaccional: Venta Cliente (Referencia), Cargas incluidas (Referencia), Fecha de venta, Precio unitario, / Monto total (Derivado) Entidad Operativa: Parte Diario. Lote (Referencia) Fecha, / Costos del día (Derivado: mano de obra, insumos, maquinaria, total), / Toneladas del día (Derivado de cargas asociadas), / Costo unitario por tonelada (Derivado). Entidad Operativa/Inventario: Movimiento de Stock (FIFO) Insumo (Referencia), Tipo (entrada/salida), Cantidad, Fecha, Motivo / Referencia operativa (ej. Parte Diario asociado), Precio unitario de compra, / Costo total del movimiento (Derivado al aplicar FIFO), Lote de inventario consumido (Referencia, si aplica) Entidad Operativa: Orden de Mantenimiento Maquinaria (Referencia), Tipo / Estado, Fechas (Programación y Cierre), / Costo total de insumos consumidos (Derivado de movimientos asociados) Entidad Transaccional: Liquidación Empleado (Referencia), Período de liquidación (fecha inicio/fin), Monto bruto, / Descuentos totales (Derivado de adelantos deducidos), / Monto neto (Derivado), Observaciones / Comprobante generado, Métricas Gerenciales (KPIs no persistidos), / Ingresos del período (Derivado de Ventas), / Egresos/costos del período (Derivado de Partes Diarios, Movimientos FIFO, Mantenimientos y Recibos), / Rentabilidad general y por lote (Derivada).&nbsp; |  |
|  |  |  |
| **Estabilidad**&nbsp; | Alta&nbsp; |  |
| **Comentarios**&nbsp; | Los campos marcados como “Derivado” se obtienen consultando y consolidando transacciones subyacentes. El sistema no cuenta con un ABM contable general de ingresos/egresos; estas métricas se derivan de ventas y costos operativos registrados. |  |

&nbsp;

| IRQ–05 | Información sobre clientes, proveedores e insumos |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-06 Gestion administrativa comercial y de abastecimiento |  |
| **Requisitos asociados**&nbsp; | UC-25..UC-28 Gestion de clientes (Alta/Baja/Modificar/Ver Cliente) UC-29..UC-32 Gestion de proveedores (Alta/Baja/Modificar/Ver Proveedor) UC-05..UC-08 Gestion de insumos (Alta/Baja/Modificar/Ver Insumo) UC-37..UC-40 Gestion de stock de insumos (FIFO) UC-45..UC-48 Gestion de categorias (Categoria de madera) UC-68 Gestionar catalogos y listas de precios&nbsp; |  |
| **Descripción**&nbsp; | El sistema deberá almacenar la información de clientes, proveedores e insumos, y controlar el stock necesario para la operación, garantizando la disponibilidad de materiales para la producción y la trazabilidad de consumos y movimientos de inventario.&nbsp; |  |
| **Datos específicos**&nbsp; | Entidad Maestra: Cliente Razon social / nombre, CUIT/DNI (según corresponda), Dirección, Datos de contacto Entidad Maestra: Proveedor Razon social / nombre, CUIT/DNI (según corresponda), Dirección, Datos de contacto Entidad Maestra: Insumo Nombre / descripción, Unidad de medida (según catálogo), Activo/inactivo (si aplica), Stock actual (Derivado de movimientos). Entidad Operativa/Inventario: Movimiento de Stock Insumo (Referencia), Tipo (entrada/salida), Cantidad y fecha, Motivo / referencia operativa (ej. Parte Diario, Mantenimiento), Precio unitario de compra, Lote de inventario (FIFO) y costo total del movimiento (si aplica) Entidad Operativa/Inventario (FIFO): Lote de Inventario Insumo (Referencia), Cantidad disponible, Costo unitario / fecha de ingreso (para valorizacion FIFO) Entidad Catalogo: Categoria de madera Nombre/descripción&nbsp; |  |
|  |  |  |
| **Estabilidad**&nbsp; | Media |  |
| **Comentarios**&nbsp; | \- |  |

&nbsp;

| IRQ–06 | &nbsp;Información sobre usuarios y permisos |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-07 Seguridad y auditoria funcional del sistema |  |
| **Requisitos asociados**&nbsp; | UC-49..UC-52 Gestion de usuarios (Alta/Baja/Modificar/Ver Usuario) UC-64 Configurar permisos&nbsp; |  |
| **Descripción**&nbsp; | El sistema deberá almacenar información relacionada con la gestión de usuarios y sus niveles de acceso (roles y permisos), permitiendo controlar qué funcionalidades puede ejecutar cada perfil.&nbsp; |  |
| **Datos específicos**&nbsp; | Entidad de Seguridad: Usuario Identificador de usuario Nombre y apellido (segun implementación) Email/usuario de acceso (según implementacion) Estado de la cuenta (activo/inactivo) Fecha y hora de creación Entidad de Seguridad: Rol Nombre del rol/perfil Permisos asociados (Referencia) Entidad de Seguridad: Permiso Nombre/código del permiso Descripción (si aplica) |  |
|  |  |  |
| **Estabilidad**&nbsp; | Alta&nbsp; |  |
| **Comentarios**&nbsp; | \- |  |

&nbsp;

| IRQ–07 | Información de auditoría del sistema |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-07 Seguridad y auditoria funcional del sistema |  |
| **Requisitos asociados**&nbsp; |  |  |
| **Descripción**&nbsp; | El sistema deberá mantener un registro de auditoría de cambios en datos (altas, modificaciones y bajas) sobre las principales entidades del dominio, permitiendo trazabilidad de quien realizó la acción, cuando, sobre que entidad y que valores fueron modificados. La auditoría puede ser consultada por usuarios autorizados.&nbsp;&nbsp; |  |
| **Datos específicos**&nbsp; | Entidad Técnica/Soporte: Registro de Auditoría Usuario responsable (Referencia, si aplica) Fecha y hora de la operación Evento/acción (created/updated/deleted) Entidad afectada (tipo de modelo) Identificador del registro afectado Valores anteriores y nuevos (cuando aplique) Metadata técnica (ej. dirección IP, si aplica)&nbsp; |  |
|  |  |  |
| **Estabilidad**&nbsp; | Alta&nbsp; |  |
| **Comentarios**&nbsp; | No se auditan consultas/lecturas como evento funcional. La auditoría registra operaciones persistidas; errores de ejecución se tratan como logging técnico separado. |  |

&nbsp;

| IRQ–08 | Información de reportes e indicadores |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-05 Soporte analitico financiero y de costos; OBJ-02 Soporte operativo y trazabilidad de la producción forestal&nbsp; |  |
| **Requisitos asociados**&nbsp; | UC-57 Informes generales (estadísticas forestales/KPIs y exportación a PDF) UC-65 Planificación de tareas por lote (ha) UC-61 Cargar Parte diario (fuente de costos y operación diaria)&nbsp; |  |
| **Descripción**&nbsp; | El sistema no requiere almacenar reportes “cerrados” como datos maestros, sino que debe ser capaz de procesar la información transaccional subyacente para generar métricas, reportes e indicadores (KPIs) de manera dinámica. Los reportes pueden exportarse (por ejemplo a PDF) a demanda.&nbsp; |  |
| **Datos específicos**&nbsp; | Métricas de producción y operativa / Total producido por lote y por periodo (derivado de Cargas; toneladas \= peso\_neto) / Total extraído por categoría de madera y periodo (derivado de Cargas \+ CategoriaMadera) / Evolución temporal de produccion (series por dia/mes, derivado de Cargas/Partes) Métricas financieras y de costos (consolidación) / Ingresos del periodo (derivado de Ventas asociadas a Cargas) / Costos/egresos operativos del periodo (derivado de Partes Diarios, Movimientos FIFO, Mantenimientos y Recibos) / Precio promedio por tonelada, costo promedio por tonelada (derivados) / Rentabilidad promedio (derivada de precio promedio vs costo promedio) / Distribución de costos (insumos / maquinaria / mano de obra) (derivado de Partes \+ Recibos) / Punto de equilibrio (derivado, según definición del reporte) Métricas de clima (cuando se consulta/genera) / Días no operativos por lluvia (derivado de registros de clima real por lote/período) / Precipitación acumulada (mm) por lote/período (derivada del snapshot de clima)&nbsp; |  |
|  |  |  |
| **Estabilidad**&nbsp; | Alta&nbsp; |  |
| **Comentarios**&nbsp; | &nbsp;Todos los elementos con prefijo “/” son métricas derivadas. |  |

&nbsp;

| IRQ–09 | Información de clima operativo por lote |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-02 Soporte operativo y trazabilidad de la producción forestal; OBJ-03 Gestión del mantenimiento y control de costos de maquinaria |  |
| **Requisitos asociados**&nbsp; | RF-18 Sincronización y análisis climático automático; UC-61 Cargar Parte diario (validación climática); UC-63 Programar mantenimiento (fecha fuera de ventana de lluvia); IRQ-08 Información de reportes e indicadores |  |
| **Descripción**&nbsp; | El sistema deberá almacenar el estado operativo climático por lote y por día, persistido por los procesos automáticos de sincronización (pronóstico e histórico real), incluyendo la fuente del dato (api externa / archivo histórico / pronóstico pasado / fallback), el error de API cuando corresponda y la marca de actualización. Este registro es el insumo de la validación de partes diarios, la programación de mantenimientos y las métricas de días no operativos. |  |
| **Datos específicos**&nbsp; | Entidad Técnica/Soporte: Registro de Clima por Día y Lote Lote (Referencia) Fecha Estado operativo del día (OPERATIVO / INACTIVO) Razón (ej. lluvia, viento, umbral) Fuente del dato (api / archive / forecast / fallback) Error de API (si aplica) Snapshot de datos climáticos / Estado, razón y fuente del pronóstico / Marca de hora de actualización del pronóstico / Estado, razón y fuente del clima real |  |
|  |  |  |
| **Estabilidad**&nbsp; | Alta&nbsp; |  |
| **Comentarios**&nbsp; | La política ante falla de la API externa es fail-open: se asume el día operativo, se persiste el error como registro fallback y se reintenta en la siguiente corrida programada. |  |

&nbsp;

| IRQ–10 | Información de notificaciones del sistema y propuestas de asignación |  |
| :---- | :---- | :---: |
| **Objetivos asociados**&nbsp; | OBJ-03 Gestión del mantenimiento y control de costos de maquinaria; OBJ-07 Seguridad y auditoría funcional del sistema |  |
| **Requisitos asociados**&nbsp; | RF-19 Propuestas de asignación automática; RF-20 Generación automática de órdenes de mantenimiento; RF-21 Monitoreo de procesos automatizados; UC-66 Gestionar asignaciones y propuestas; UC-67 Configurar notificaciones de mantenimiento |  |
| **Descripción**&nbsp; | El sistema deberá almacenar las notificaciones internas generadas por los procesos automatizados (órdenes generadas por umbral, recordatorios y vencimientos de mantenimiento), la configuración de destinatarios por tipo de notificación, y las propuestas de asignación automática con sus candidatos y estados. |  |
| **Datos específicos**&nbsp; | Entidad de Soporte: Notificación del Sistema Usuario destino (Referencia) Mantenimiento asociado (Referencia, si aplica) Tipo (umbral\_alcanzado / recordatorio\_programado / mantenimiento\_vencido / stock\_insuficiente) Título y mensaje / Fecha límite para accionar / Estado de leída y accionada con marcas de hora Entidad de Configuración: Destinatarios de Notificaciones de Mantenimiento Usuario (Referencia) Tipo de suscripción (umbral / recordatorio / stock) Entidad de Recomendación: Propuesta de Asignación Lote y tarea (Referencia) Tipo de tarea / Estado (borrador, aplicada, cerrada) / Métricas de productividad esperada / Candidatos de empleados, maquinarias e insumos con selección / Metadatos de orden de compra enviada |  |
|  |  |  |
| **Estabilidad**&nbsp; | Media&nbsp; |  |
| **Comentarios**&nbsp; | La notificación interna es el canal de registro (se crea antes del intento de envío de email); el email es canal de aviso adicional con reintentos. |  |

### **Requisitos de Funcionales** {#requisitos-de-funcionales}

&nbsp;

| RF-01 | Gestión de Lotes |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-02 Soporte operativo y trazabilidad de la producción forestal |
| **Requisitos asociados** | UC-01..UC-04 Gestion de lotes (Alta/Baja/Modificar/Ver Lote); IRQ-01 Información sobre lotes y cargas |
| **Descripción** | El sistema deberá permitir registrar, modificar, consultar y dar de baja lotes de producción, vinculando datos de origen y características del lote, y soportando la trazabilidad de la producción asociada (cargas/partes diarios). La comparación entre planificación y ejecución se realiza mediante la planificación de tareas por lote (ha) y la producción registrada. |
| **Estabilidad** | Alta |
| **Comentarios** | \- |

&nbsp;

| RF-02 | Gestión de Cargas |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-02 Soporte operativo y trazabilidad de la producción forestal |
| **Requisitos asociados** | UC-41..UC-44 Gestion de cargas (Alta/Baja/Modificar/Ver Carga); UC-61 Cargar Parte diario; IRQ-01 Información sobre lotes y cargas |
| **Descripción** | El sistema deberá registrar cada carga de madera asociándose al lote y, cuando corresponda, al parte diario. Debe permitir almacenar ticket, pesos (bruto, tara, neto), destino, fecha de carga, categoría de madera y chofer responsable, manteniendo trazabilidad. |
| **Estabilidad** | Alta |
| **Comentarios** | La asociación a parte diario aplica cuando la carga se registra dentro del flujo del parte o se vincula posteriormente. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| RF-03 | Planificación de Producción |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-02 Soporte operativo y trazabilidad de la producción forestal |
| **Requisitos asociados** | UC-65 Planificación de tareas por lote (ha); UC-61 Cargar Parte diario; IRQ-08 Información de reportes e indicadores |
| **Descripción** | El sistema deberá permitir planificar tareas por lote en superficie (ha) y comparar la planificación con la ejecución real mediante métricas derivadas (partes diarios y producción registrada). |
| **Estabilidad** | Media |
| **Comentarios** | La planificación es por ha (LoteTarea). El volumen real se deriva de cargas/producción registrada. |

&nbsp;

| RF-04 | Gestión de Maquinaria y Equipos |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-03 Gestión del mantenimiento y control de costos de maquinaria |
| **Requisitos asociados** | UC-09..UC-12 Gestión de maquinaria (Alta/Baja/Modificar/Ver Maquinaria); IRQ-02 Información sobre maquinaria y equipos |
| **Descripción** | El sistema debera permitir registrar, consultar y modificar la información estática de cada maquinaria/equipo utilizado en la producción (tipo, modelo, estado, parámetros operativos).&nbsp; |
| **Estabilidad** | Alta |
| **Comentarios** | \- |

&nbsp;

| RF-05 | Costos de maquinaria&nbsp; |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-03 Gestión del mantenimiento y control de costos de maquinaria |
| **Requisitos asociados** | UC-61 Cargar Parte diario; UC-62 Cerrar orden de mantenimiento; IRQ-02 Información sobre maquinaria y equipos; IRQ-04 Información financiera y de costos |
| **Descripción** | El sistema deberá calcular e imputar costos de maquinaria a la operación (por ejemplo en el parte diario), en base al uso registrado (maquinarias asociadas a cargas/parte) y costos vinculados (mantenimientos cerrados y/o parámetros configurados), para consolidación de costos operativos. |
| **Estabilidad** | Media |
| **Comentarios** | \- |

&nbsp;

| RF-06 | Gestión de mantenimientos |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-03 Gestión del mantenimiento y control de costos de maquinaria |
| **Requisitos asociados** | UC-63 Programar mantenimiento; UC-62 Cerrar orden de mantenimiento; IRQ-02 Información sobre maquinaria y equipos |
| **Descripción** | El sistema deberá permitir programar mantenimientos preventivos y registrar/cerrar mantenimientos correctivos sobre maquinarias, almacenando fechas, estado y consumos/costos asociados (por ejemplo, insumos vinculados). |
| **Estabilidad** | Media |
| **Comentarios** | \- |

&nbsp;

| RF-07 | Gestión de empleados |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-04 Automatizacion de la liquidacion de nominas de Recursos Humanos |
| **Requisitos asociados** | UC-21..UC-24 Gestion de empleados; UC-33..UC-36 Gestion de choferes; IRQ-03 Informacion sobre empleados y choferes |
| **Descripción** | El sistema deberá administrar la información maestra del personal (empleados), permitiendo alta, baja, consulta y modificación de datos, roles y contacto.&nbsp; |
| **Estabilidad** | Alta |
| **Comentarios** | \- |

&nbsp;

| RF-08 | Liquidación de pagos |
| :---- | :---- |
| **Objetivos Asociados** | &nbsp;OBJ-04 Automatización de la liquidación de nóminas de Recursos Humanos |
| **Requisitos asociados** | UC-59 Liquidar Pagos; UC-61 Cargar Parte diario; UC-53..UC-56 Gestión de adelantos; IRQ-03 Información sobre empleados y choferes; IRQ-04 Información financiera y de costos |
| **Descripción** | El sistema deberá calcular automáticamente los haberes del personal en base a días trabajados/productividad registrada y aplicar descuentos por adelantos, generando el resultado de liquidación para su emisión como comprobante. |
| **Estabilidad** | Media |
| **Comentarios** | \- |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| RF-09 | Emisión de recibos y gestión de adelantos |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-04 Automatizacion de la liquidacion de nominas de Recursos Humanos&nbsp; |
| **Requisitos asociados** | UC-58 Generar Recibos; UC-53..UC-56 Gestion de adelantos; IRQ-03 Informacion sobre empleados&nbsp; |
| **Descripción** | El sistema deberá registrar adelantos entregados al personal y emitir recibos/comprobantes de pago, manteniendo historial transaccional de liquidaciones efectuadas. |
| **Estabilidad** | Alta |
| **Comentarios** | \- |

&nbsp;

&nbsp;

| RF-10 | Reportes financieros y de rentabilidad |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-05 Soporte analitico financiero y de costos |
| **Requisitos asociados** | UC-57 Informes generales; UC-13..UC-16 Gestion de ventas; IRQ-04 Informacion financiera y de costos; IRQ-08 Informacion de reportes e indicadores |
| **Descripción** | El sistema deberá consolidar dinámicamente ingresos derivados de ventas y costos operativos derivados de transacciones (partes diarios, stock FIFO, mantenimientos y recibos) para presentar indicadores de ingresos, egresos, flujo de caja y rentabilidad por lote y por periodo. |
| **Estabilidad** | Media |
| **Comentarios** | No existe un ABM contable estático de ingresos/egresos; son métricas derivadas. |

&nbsp;

| RF-11 | Generación de reportes financieros |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-05 Soporte analitico financiero y de costos |
| **Requisitos asociados** | UC-57 Informes generales; IRQ-08 Informacion de reportes e indicadores; IRQ-04 Informacion financiera y de costos |
| **Descripción** | El sistema deberá generar reportes y vistas de estadísticas que muestren resultados económicos derivados (ingresos, costos/egresos, rentabilidad, punto de equilibrio) por periodo y por lote, a partir de ventas y costos operativos registrados. |
| **Estabilidad** | Media |
| **Comentarios** | No se implementa un “balance contable” formal; los resultados son métricas derivadas de transacciones. |

&nbsp;

| RF-12 | Gestión de clientes y proveedores |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-06 Gestion administrativa comercial y de abastecimiento |
| **Requisitos asociados** | UC-25..UC-28 Gestion de clientes; UC-29..UC-32 Gestion de proveedores; IRQ-05 Informacion sobre clientes, proveedores e insumos |
| **Descripción** | El sistema deberá administrar la información de clientes y proveedores, incluyendo sus datos comerciales y de contacto necesarios para la operación. |
| **Estabilidad** | Media |
| **Comentarios** | \- |

&nbsp;

&nbsp;

&nbsp;

| RF-13 | Gestión de insumos y stock |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-06 Gestion administrativa comercial y de abastecimiento. |
| **Requisitos asociados** | UC-05..UC-08 Gestion de insumos; UC-37..UC-40 Gestion de stock de insumos (FIFO); IRQ-05 Informacion sobre clientes, proveedores e insumos |
| **Descripción** | El sistema deberá controlar el stock de insumos registrando entradas, salidas y consumos asociados a la operación (por ejemplo, parte diario o mantenimiento), manteniendo trazabilidad y valoración FIFO cuando corresponda. |
| **Estabilidad** | Alta |
| **Comentarios** | \- |

&nbsp;

| RF-14 | Gestion de usuarios (ABM) |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-07 Seguridad y auditoria funcional del sistema |
| **Requisitos asociados** | &nbsp;UC-49..UC-52 Gestion de usuarios; IRQ-06 Informacion sobre usuarios y permisos&nbsp; |
| **Descripción** | El sistema debera permitir crear, modificar, consultar y dar de baja usuarios, manteniendo su estado y datos de acceso. |
| **Estabilidad** | Alta |
| **Comentarios** | La asignación de roles/permisos se cubre en RF-15. |

&nbsp;

| RF-15 | Configuración de roles y permisos |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-07 Seguridad y auditoria funcional del sistema |
| **Requisitos asociados** | UC-64 Configurar permisos; IRQ-06 Informacion sobre usuarios y permisos. |
| **Descripción** | El sistema deberá definir y aplicar permisos de acceso por rol/perfil y permitir asignar roles a usuarios, controlando el acceso a módulos y operaciones críticas. |
| **Estabilidad** | Alta |
| **Comentarios** | \- |

&nbsp;

| RF-16 | Registro de auditoría |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-07 Seguridad y auditoria funcional del sistema |
| **Requisitos asociados** | IRQ-07 Información de auditoría del sistema |
| **Descripción** | El sistema deberá registrar acciones de cambio sobre entidades auditables (altas/modificaciones/bajas) indicando usuario, fecha/hora, entidad afectada y cambios relevantes, y permitir su consulta por usuarios autorizados. |
| **Estabilidad** | Alta |
| **Comentarios** | No se auditan lecturas/consultas como evento funcional; errores se tratan como logging técnico separado. |

&nbsp;

| RF-17 | Generación de indicadores de gestión (KPIs) |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-05 Soporte analitico financiero y de costos; OBJ-02 Soporte operativo y trazabilidad de la producción forestal |
| **Requisitos asociados** | UC-57 Informes generales; IRQ-08 Informacion de reportes e indicadores |
| **Descripción** | El sistema deberá generar indicadores de gestión sobre producción (toneladas, categorías, evolución), costos (insumos/maquinaria/mano de obra) y rentabilidad, basados en datos registrados en partes diarios, cargas, ventas, stock FIFO, mantenimientos y recibos.&nbsp; |
| **Estabilidad** | Media |
| **Comentarios** | \- |

&nbsp;

| RF-18 | Sincronización y análisis climático automático |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-02 Soporte operativo y trazabilidad de la producción forestal; OBJ-03 Gestión del mantenimiento y control de costos de maquinaria |
| **Requisitos asociados** | UC-61 Cargar Parte diario (validación climática con override); UC-63 Programar mantenimiento (fecha fuera de ventana de lluvia); IRQ-09 Información de clima operativo por lote; IRQ-08 Información de reportes e indicadores |
| **Descripción** | El sistema deberá sincronizar automáticamente el pronóstico y el histórico climático real por lote activo con coordenadas (mediante tareas programadas: análisis de decisiones cada 6 horas, análisis de riesgo diario y sincronización de clima real diario), persistir el estado operativo por lote y día con su fuente y errores, y recomendar estrategias operativas (anticipación/reacción). El estado persistido deberá ser consumido por la validación de partes diarios (con override explícito del usuario en días no operativos) y por la programación de mantenimientos fuera de ventanas de lluvia. |
| **Estabilidad** | Alta |
| **Comentarios** | Servicio externo Open-Meteo (sin autenticación). Política ante falla de la API: fail-open (se asume día operativo, se registra el error y se reintenta en la siguiente corrida). |

&nbsp;

| RF-19 | Propuestas de asignación automática de recursos |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-01 Centralización e integración de registros operativos y administrativos; OBJ-02 Soporte operativo y trazabilidad de la producción forestal |
| **Requisitos asociados** | UC-65 Planificación de tareas por lote (ha) (disparador); UC-66 Gestionar asignaciones y propuestas (revisión y aplicación); IRQ-10 Información de notificaciones y propuestas |
| **Descripción** | El sistema deberá generar automáticamente propuestas de asignación de recursos (empleados, maquinarias e insumos) para un lote cuando el lote se crea o cambia de estado/condiciones, y cuando se guarda su planificación de tareas. Las propuestas se basarán en el histórico de desempeño (productividad por tarea/especie de los últimos 24 meses, con mínimo de muestras y tarifas de respaldo si no hay histórico), se generarán como trabajos encolados únicos e idempotentes (sin duplicados) y quedarán en estado de borrador para que el usuario las revise, seleccione candidatos y las aplique o descarte (UC-66). La propuesta por sí sola no asigna recursos: la decisión final es humana. |
| **Estabilidad** | Media |
| **Comentarios** | Requiere worker de cola activo en el servidor (ver RF-21 y NFR de operación). |

&nbsp;

| RF-20 | Generación automática de órdenes de mantenimiento preventivo |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-03 Gestión del mantenimiento y control de costos de maquinaria; OBJ-07 Seguridad y auditoría funcional del sistema |
| **Requisitos asociados** | UC-62 Cerrar orden de mantenimiento (cierre y snapshot); UC-63 Programar mantenimiento; UC-67 Configurar notificaciones de mantenimiento; IRQ-02 Información sobre maquinaria y equipos; IRQ-09; IRQ-10 |
| **Descripción** | El sistema deberá acumular automáticamente las toneladas procesadas por cada maquinaria a partir de las cargas registradas (odómetro) y verificar diariamente los umbrales de mantenimiento. Al superarse un umbral sin orden abierta, deberá generar en una transacción: la orden de mantenimiento preventivo con fecha programada resuelta fuera de la ventana de lluvia, la asignación automática de personal disponible, la detección de faltantes de stock del kit preventivo con propuesta de compra cuando corresponda, y la notificación interna a los usuarios configurados (canal de registro). Adicionalmente deberá enviar el aviso por email con reintentos (canal adicional) y, de forma periódica, enviar recordatorios de órdenes programadas y marcar como vencidas las no confirmadas, notificándolo internamente. |
| **Estabilidad** | Alta |
| **Comentarios** | La notificación interna se crea antes del intento de email: si el email falla, el aviso interno persiste. El cierre de la orden (UC-62) registra el snapshot del odómetro que reinicia el ciclo. |

&nbsp;

| RF-21 | Monitoreo de procesos automatizados |
| :---- | :---- |
| **Objetivos Asociados** | OBJ-07 Seguridad y auditoría funcional del sistema |
| **Requisitos asociados** | UC-67 Configurar notificaciones de mantenimiento; IRQ-10 Información de notificaciones y propuestas |
| **Descripción** | El sistema deberá proporcionar al Administrador una pantalla de estado de los procesos automatizados que muestre: el catálogo de procesos con su disparador y frecuencia, el estado de sincronización climática por lote (estado del día, fuente y errores) y el estado de la cola de trabajos (pendientes y fallidos, con su error). El objetivo es que una persona pueda ver qué pasó e intervenir si un proceso se traba. |
| **Estabilidad** | Media |
| **Comentarios** | Construida sobre datos ya persistidos por los procesos (registros de clima y tabla de trabajos de la cola). |

&nbsp;

&nbsp;

### **Procesos Automatizados del Sistema** {#procesos-automatizados-del-sistema}

El sistema cuenta con tres procesos automatizados: flujos que corren solos, sin que una persona dispare cada paso. Cada uno es disparado por el tiempo (tareas programadas), por un evento (registro de carga, guardado de planificación) o por un cambio de estado (lote creado o modificado). Tienen entradas, pasos y salidas definidos, manejan sus fallos (reintentos, fallbacks persistentes y transacciones) y dejan efectos observables (datos modificados, documentos generados, avisos enviados). El frontend es el lugar donde la persona configura estos procesos, ve su estado e interviene si algo se traba.

**PA-01 — Mantenimiento preventivo por umbral de producción** (RF-20)

| Aspecto | Definición |
| :---- | :---- |
| **Propósito** | Anticipar el desgaste: cuando una máquina acumula toneladas desde su último mantenimiento y supera el umbral de su tipo, el sistema genera la orden, el personal y los avisos sin que nadie lo pida. |
| **Disparador** | Doble: evento en tiempo real (cada carga registrada incrementa el odómetro de la maquinaria) y tiempo (verificación diaria de umbrales a las 06:30; verificación de programados cada 4 horas). |
| **Entradas** | Odómetro por maquinaria (derivado de cargas), umbral por tipo de maquinaria, kits preventivos y su stock, clima por lote (para fechar la orden), personal disponible por rol y fecha. |
| **Salidas** | Orden de mantenimiento programada, asignación de personal, notificaciones internas a usuarios configurados, email con documentos adjuntos, propuesta de compra de insumos ante faltantes. |
| **Responsable** | Ejecuta: el planificador de tareas del servidor. Interviene: Personal Administrativo (aprueba y cierra la orden, UC-62); el Administrador configura destinatarios de avisos (UC-67). |
| **Manejo de errores** | Transacción por maquinaria (una falla no arrastra las demás) con rollback y registro; salteo idempotente si ya existe orden abierta; email con reintentos y respaldo en notificación interna; vencimientos marcados y notificados internamente. |
| **Visibilidad** | Configurar: umbrales (tipos de maquinaria), kits, destinatarios. Ver: listado de mantenimientos, campana de notificaciones, pantalla de estado de procesos. Intervenir: aprobar, reprogramar, cerrar. |

**PA-02 — Análisis climático operativo** (RF-18)

| Aspecto | Definición |
| :---- | :---- |
| **Propósito** | Decidir con datos si se opera o no: sincroniza pronóstico e histórico por lote, mapea días operativos/inactivos y recomienda estrategia (anticipación / reacción / normal). |
| **Disparador** | Tiempo: análisis de decisiones cada 6 horas, análisis de riesgo diario (06:00) y sincronización de clima real diario (00:30). Demanda: al cargar un parte diario, si el pronóstico del día no está fresco, se sincroniza en el momento. |
| **Entradas** | Coordenadas GPS por lote, servicio externo de información meteorológica (pronóstico y archivo histórico). |
| **Salidas** | Estado operativo por lote/día persistido (con fuente y errores), recomendaciones de estrategia, insumo para la validación del parte diario, insumo para fechar mantenimientos sin lluvia (PA-01), reportes de lluvias. |
| **Responsable** | Ejecuta: el planificador de tareas del servidor. Interviene: el Capataz puede registrar el parte con override motivado en día no operativo (la decisión final es humana). El Personal Administrativo carga las coordenadas del lote (sin coordenadas no hay proceso). |
| **Manejo de errores** | Política fail-open deliberada: sin datos de clima se asume día operativo y el error queda persistido como registro fallback; sin reintento inmediato (la cadencia de 6 horas actúa como reintento natural). |
| **Visibilidad** | Configurar: coordenadas por lote. Ver: aviso en el parte diario, reporte PDF de lluvias, pantalla de estado de procesos (sync por lote, fuente y errores). Intervenir: override en el parte diario. |

**PA-03 — Propuestas de asignación automática de recursos** (RF-19)

| Aspecto | Definición |
| :---- | :---- |
| **Propósito** | Que planificar un lote no empiece de cero: cuando el lote cambia o se planifica, el sistema propone empleados, máquinas e insumos basándose en el histórico real de desempeño. |
| **Disparador** | Cambio de estado: lote creado o con cambio de estado/especie/superficie/tarea principal (observador del modelo). Acción de planificación: al guardar la planificación de tareas del lote (UC-65). |
| **Entradas** | Lote y tareas activas, histórico de productividad (partes, cargas y asignaciones), disponibilidad actual de recursos. |
| **Salidas** | Propuesta en borrador con candidatos (empleados, maquinarias, insumos) y métricas esperadas; orden de compra por email a pedido; al aplicarse, las asignaciones efectivas del lote. |
| **Responsable** | Ejecuta: la cola de trabajos del servidor. Interviene: Personal Administrativo revisa, selecciona candidatos y aplica o descarta en el listado de propuestas (UC-66). La propuesta no asigna nada sola: la decisión es humana. |
| **Manejo de errores** | Trabajos únicos por lote (sin duplicados) e idempotentes (no regeneran si ya se generó hoy); los fallos de cola quedan registrados y visibles en la pantalla de estado de procesos. |
| **Visibilidad** | Configurar: tipos de tarea al planificar. Ver: listado de propuestas filtrable por lote y estado, pantalla de estado de procesos (cola). Intervenir: seleccionar, aplicar, descartar. |

**Encadenamiento entre los procesos** (sin solapamientos ni huecos)

Las cargas registradas alimentan el odómetro de maquinaria (PA-01 en tiempo real); la verificación diaria de umbrales usa el clima sincronizado (PA-02) para fechar la orden fuera de la ventana de lluvia; los avisos generados se notifican por canales interno y email. El cambio de estado o planificación de un lote dispara las propuestas (PA-03), que al aplicarse generan las asignaciones con las que se cargan los partes diarios — que a su vez registran las cargas que alimentan el odómetro. El clima (PA-02) informa a los otros dos sin ser informado por nadie; las propuestas (PA-03) son el único proceso que no dispara nada automático hacia adelante: para eso está la persona. Cada proceso tiene un disparador propio (tiempo / tiempo+evento / estado), por lo que no se solapan.

&nbsp;

&nbsp;

1. ### **Diagrama de Casos de Usos** {#diagrama-de-casos-de-usos}

   11. ***![][image2]***

#### **Figura 5 \- Diagrama Caso Usos: Principal** {#figura-5---diagrama-caso-usos:-principal}

![][image5]

#### **Figura 6 \- Diagrama Caso Usos: Producción(detalles)** {#figura-6---diagrama-caso-usos:-producción(detalles)}

![][image6]

&nbsp;

#### **Figura 7  \- Diagrama Caso Usos: Maquinaria y Mantenimiento(detalles)** {#figura-7---diagrama-caso-usos:-maquinaria-y-mantenimiento(detalles)}

&nbsp;

&nbsp;

1. 

   2. ### **Definición de Actores** {#definición-de-actores}

      3. &nbsp;

| ACT–01&nbsp; | Personal Administrativo |
| :---- | :---- |
| **Tipo** | Actor Principal |
| **Descripción**&nbsp; | Este actor representa al personal de la empresa encargado de iniciar y gestionar tareas administrativas orientadas al negocio, tales como la carga de datos, gestión de clientes, proveedores, control de insumos y la emisión de reportes gerenciales y operativos. |
| **Comentarios**&nbsp; | \-&nbsp;&nbsp; |

&nbsp;

| ACT–02&nbsp; | Capataz |
| :---- | :---- |
| **Tipo** | Actor Principal |
| **Descripción**&nbsp; | Este actor representa al encargado de supervisar y validar las tareas operativas realizadas en campo. Utiliza el sistema para registrar la ejecución operativa, incluyendo la extracción, el registro de cargas y la validación/carga de los partes diarios. |
| **Comentarios**&nbsp; | \-&nbsp;&nbsp; |

&nbsp;

| ACT–03&nbsp; | Administrador |
| :---- | :---- |
| **Tipo** | Actor Principal |
| **Descripción**&nbsp; | Este actor representa a la persona responsable de la configuración, auditoría y mantenimiento del sistema informático, incluyendo la gestión de usuarios, asignación de roles y permisos, y el control de la seguridad. |
| **Comentarios**&nbsp; | \- |

&nbsp;

| ACT–04&nbsp; | API clima |
| :---- | :---- |
| **Tipo**&nbsp; | Actor de Apoyo |
| **Descripción**&nbsp; | Este actor representa un servicio externo de terceras partes que proporciona un servicio de información meteorológica al sistema. El sistema consume estos datos como insumo automático para validar y asistir en la planificación de tareas de extracción forestal. |
| **Comentarios**&nbsp; | En los diagramas UML, este actor se identifica visualmente mediante el estereotipo \<\<system\>\> para diferenciarlo de los actores humanos.&nbsp;&nbsp; |

         4. 

&nbsp;

### **Casos de uso del Sistema** {#casos-de-uso-del-sistema}

&nbsp;

| UC-01 | Alta Lote |
| :---- | :---- |
| Actores | Personal Administrativo&nbsp; |
| Descripción | Permite registrar un nuevo lote con su identificación y características básicas para ser utilizado en planificación, cargas y partes diarios. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a **Gestión de Lotes** \> **Registrar Lote**. 2- El sistema muestra formulario con campos: **Código de Lote** (único), propietario, ubicación, especie, **superficie (ha)**, **condición de compra** (vuelo forestal, tn), **precio por lote, fecha de compra** y observaciones. 3- El usuario completa los datos obligatorios y selecciona **Guardar**. 4- El sistema valida: presencia de obligatorios,formatos (superficie numérica \> 0, fecha válida). 5- El sistema registra el nuevo lote, asigna **id interno**, establece **estado inicial \= adquirido**, guarda **fecha/hora** y **usuario** creador, y genera entrada de **auditoría**. 6- El sistema confirma el alta y muestra el detalle del lote creado. |
| Postcondición&nbsp; | Lote creado y disponible para **Planificación**, **Cargas** y **Partes Diarios**. |
| Excepciones | 3a. **Cancelación**: si el usuario cancela, no se guardan cambios y se vuelve a Gestión de Lotes. 4a. **Datos inválidos/incompletos**: el sistema marca campos con error y solicita corrección. 5a. **Error de persistencia/BD**: el sistema informa el error, revierte la operación y sugiere reintentar.&nbsp; |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-02 | Baja Lote |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite dar de baja un lote existente. La baja se modela como baja lógica (cambio de estado a baja) para mantener trazabilidad. |
| Precondición | Existe un lote registrado en estado distinto de baja. |
| Secuencia Normal | 1- El usuario navega a Gestión de Lotes. 2- El sistema lista los lotes existentes con su estado. 3- El usuario selecciona un lote y elige la opción Dar de baja. 4- El sistema solicita confirmación de la acción. 5- El usuario confirma la baja. 6- El sistema actualiza el estado del lote a baja, guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Lote marcado como baja y no disponible para nuevas operaciones. |
| Excepciones | 3a. Lote inexistente: el sistema informa que el lote no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios y se vuelve al listado. 6a. Error de persistencia/BD: el sistema informa el error y no actualiza el estado. |

&nbsp;

&nbsp;

&nbsp;

| UC-03 | Modificar Lote |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite actualizar los datos de un lote existente (ubicación, especie, superficie, condición de compra, estado, tipo de tarea principal y coordenadas). |
| Precondición | Existe un lote registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Lotes. 2- El sistema lista los lotes existentes. 3- El usuario selecciona un lote y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales del lote. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida campos obligatorios, formatos y valores permitidos. 7- El sistema guarda los cambios y registra auditoría. Si el estado queda en en\_proceso, el sistema puede redirigir a la planificación de tareas o recomendaciones del lote. 8- El sistema confirma la modificación y actualiza el listado/detalle. |
| Postcondición&nbsp; | Lote actualizado con los nuevos datos. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios y se vuelve al listado. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-04 | Ver lote |
| :---- | :---- |
| Actores | Personal administrativo |
| Descripción | Permite consultar la información de los lotes registrados y su estado actual. |
| Precondición |  |
| Secuencia Normal | 1- El usuario navega a Gestión de Lotes. 2- El sistema muestra el listado de lotes con filtro de búsqueda. 3- El usuario aplica búsqueda si lo necesita. 4- El usuario selecciona un lote para ver su detalle. 5- El sistema muestra la información del lote (datos generales, estado, superficie, especie, ubicación y coordenadas si existen). |
| Postcondición&nbsp; | Información del lote visualizada. |
| Excepciones | 4a. Lote inexistente: el sistema informa que el lote no existe o no está disponible. 5a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-05 | Alta Insumo |
| :---- | :---- |
| Actores | Personal administrativo |
| Descripción | Permite registrar un nuevo insumo con su información básica y referencias a proveedor y unidad de medida. |
| Precondición | Existen unidades de medida y proveedores registrados. |
| Secuencia Normal | 1- El usuario navega a Gestión de Insumos \> Registrar Insumo. 2- El sistema muestra formularios con campos: nombre, descripción (opcional), unidad de medida y proveedor. 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: campos obligatorios, formato de texto, y existencia de unidad de medida y proveedor. 5- El sistema registra el insumo, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado.. |
| Postcondición&nbsp; | Insumo creado y disponible para movimientos de stock y mantenimientos. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios y se vuelve al listado. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-06 | Baja Insumo |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite dar de baja un insumo existente. La baja se modela como baja lógica (marcar como inactivo) para mantener historial y trazabilidad. |
| Precondición | Existe un insumo registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Insumos. 2- El sistema lista los insumos existentes. 3- El usuario selecciona un insumo y elige la opción Dar de baja. 4- El sistema solicita confirmación. 5- El usuario confirma la baja. 6- El sistema marca el insumo como inactivo, guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Insumo marcado como inactivo y no disponible para nuevas operaciones. |
| Excepciones | 3a. Insumo inexistente: el sistema informa que el insumo no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-07 | Modificar Insumo |
| :---- | :---- |
| Actores | Personal administrativo |
| Descripción | Permite actualizar los datos de un insumo existente (nombre, descripción, unidad de medida y proveedor). |
| Precondición | Existe un insumo registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Insumos. 2- El sistema lista los insumos existentes. 3- El usuario selecciona un insumo y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida campos obligatorios y existencia de unidad de medida y proveedor. 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Insumo actualizado. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-08 | Ver insumo |
| :---- | :---- |
| Actores | Personal administrativo |
| Descripción | Permite consultar la informacion de insumos y su disponibilidad de stock calculada. |
| Precondición |  |
| Secuencia Normal | 1- El usuario navega a Gestión de Insumos. 2- El sistema muestra el listado de insumos con opciones de búsqueda. 3- El usuario aplica búsqueda por nombre, descripción, proveedor o unidad de medida. 4- El usuario selecciona un insumo para ver su detalle. 5- El sistema muestra la información del insumo y el stock disponible calculado a partir de los movimientos de inventario. |
| Postcondición&nbsp; | Información del insumo visualizada. |
| Excepciones | 4a. Insumo inexistente: el sistema informa que el insumo no existe o no está disponible. 5a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-09 | Alta Maquinaria |
| :---- | :---- |
| Actores | Personal Administrativo. |
| Descripción | Permite registrar una nueva maquinaria/equipo con su tipo, modelo y estado operativo para su uso en producción y mantenimientos. |
| Precondición | Existen tipos de maquinaria registrados. |
| Secuencia Normal | 1- El usuario navega a Gestión de Maquinarias \> Registrar Maquinaria. 2- El sistema muestra formulario con campos: tipo de maquinaria, modelo, estado (operativa, en\_mantenimiento, fuera\_de\_servicio), si es alquilada, fecha de inicio de actividades y umbral de toneladas (opcional). 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: campos obligatorios, formatos (fecha válida, umbral numérico \>= 0\) y valores permitidos para el estado. 5- El sistema registra la maquinaria, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Maquinaria creada y disponible para asignaciones y mantenimientos. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar.. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-10 | Baja Maquinaria |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite dar de baja una maquinaria existente. La baja se modela como baja lógica (cambio de estado a fuera\_de\_servicio) para mantener historial. |
| Precondición | Existe una maquinaria registrada. |
| Secuencia Normal | 1- El usuario navega a Gestión de Maquinarias. 2- El sistema lista las maquinarias existentes. 3- El usuario selecciona una maquinaria y elige la opción Dar de baja. 4- El sistema solicita confirmación. 5- El usuario confirma la baja. 6- El sistema cambia el estado a fuera\_de\_servicio, guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Maquinaria marcada como fuera\_de\_servicio y no disponible para nuevas operaciones. |
| Excepciones | 3a. Maquinaria inexistente: el sistema informa que la maquinaria no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-11 | Modificar Maquinaria |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite actualizar los datos de una maquinaria existente (tipo, modelo, estado, si es alquilada, fecha de inicio y umbral de toneladas). |
| Precondición | Existe una maquinaria registrada. |
| Secuencia Normal | 1- El usuario navega a Gestión de Maquinarias. 2- El sistema lista las maquinarias existentes con opción de búsqueda. 3- El usuario selecciona una maquinaria y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida: campos obligatorios, formatos (fecha válida, umbral numérico \>= 0\) y valores permitidos para el estado. 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Maquinaria actualizada con los nuevos datos. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios y se vuelve al listado. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-12 | Ver maquinaria |
| :---- | :---- |
| Actores | Personal administrativo |
| Descripción | Permite consultar la información de las maquinarias registradas y su estado operativo actual. |
| Precondición |  |
| Secuencia Normal | 1- El usuario navega a Gestión de Maquinarias. 2- El sistema muestra el listado de maquinarias. 3- El usuario aplica búsqueda por modelo, estado o tipo si lo necesita. 4- El usuario visualiza los datos de una maquinaria (tipo, modelo, estado, fecha de inicio, si es alquilada y umbral de toneladas si aplica). |
| Postcondición&nbsp; | Información de maquinaria visualizada. |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-13 | Alta Venta |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite registrar una venta seleccionando un cliente y facturando cargas pendientes dentro de un rango de fechas, generando el total de la operación. |
| Precondición | Existe al menos un cliente registrado y existen cargas en estado pendiente para ese cliente. |
| Secuencia Normal | 1- El usuario navega a Gestión de Ventas \> Nueva Venta. 2- El sistema muestra campos: cliente, fecha desde, fecha hasta y observaciones (opcional). 3- El usuario selecciona un cliente y un rango de fechas y elige Buscar cargas pendientes. 4- El sistema valida: cliente seleccionado, fechas presentes y rango válido. 5- El sistema lista las cargas pendientes del cliente dentro del rango, mostrando ticket, fecha, categoría, peso (toneladas), precio unitario y subtotal, y calcula el total. 6- El usuario revisa el detalle y selecciona Registrar Venta. 7- El sistema registra la venta con fecha de emisión actual, monto total y observaciones, asocia las cargas a la venta y actualiza el estado de las cargas a facturada, y registra auditoría. 8- El sistema confirma el alta y actualiza el historial de ventas. |
| Postcondición&nbsp; | Venta registrada y cargas asociadas marcadas como vendidas. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios y se vuelve al historial. 4a. Datos inválidos/incompletos: el sistema informa el error y solicita corrección. 5a. Sin cargas pendientes: el sistema informa que no se encontraron cargas y no registra la venta. 7a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-14 | Baja Venta |
| :---- | :---- |
| Actores | Personal administrativo |
| Descripción | Permite dar de baja una venta registrada. La baja se modela como baja lógica (marcar la venta como inactiva) para mantener historial; las cargas asociadas vuelven a estado pendiente. |
| Precondición | Existe una venta registrada en estado activo. |
| Secuencia Normal | 1- El usuario navega a Gestión de Ventas \> Historial. 2- El sistema muestra el listado de ventas. 3- El usuario selecciona una venta y elige la opción Dar de baja. 4- El sistema solicita confirmación de la acción. 5- El usuario confirma la baja. 6- El sistema marca la venta como inactiva (activo \= false), actualiza las cargas asociadas a estado pendiente, registra auditoría y actualiza el historial. 7- El sistema confirma la baja. |
| Postcondición&nbsp; | Venta marcada como inactiva y cargas asociadas disponibles nuevamente como pendientes. |
| Excepciones | 3a. Venta inexistente: el sistema informa que la venta no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-15 | Modificar Venta |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite actualizar datos de una venta registrada (observaciones y monto) desde el detalle de la operación. |
| Precondición | Existe una venta registrada. |
| Secuencia Normal | 1- El usuario navega a Gestión de Ventas \> Historial. 2- El sistema muestra el listado de ventas. 3- El usuario selecciona una venta y abre su detalle. 4- El usuario activa el modo edición. 5- El usuario modifica observaciones y/o monto y selecciona Guardar. 6- El sistema valida formatos básicos (por ejemplo, monto numérico) y guarda los cambios y registra auditoría. 7- El sistema confirma la modificación y actualiza el historial. |
| Postcondición&nbsp; | La venta queda actualizada en el sistema. |
| Excepciones | 5a. Cancelación: si el usuario cancela la edición, no se guardan cambios. 6a. Datos inválidos: el sistema informa el error y solicita corrección. 6b. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-16 | Ver Venta |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite consultar el historial de ventas y ver el detalle de cargas facturadas por cada venta. |
| Precondición |  |
| Secuencia Normal | 1- El usuario navega a Gestión de Ventas \> Historial. 2- El sistema muestra el listado de ventas con opción de búsqueda. 3- El usuario aplica búsqueda por cliente, id de recibo o monto si lo necesita. 4- El usuario selecciona una venta y visualiza su detalle (cliente, fecha, monto, observaciones y detalle de cargas con categoría, peso, precio y subtotal). |
| Postcondición&nbsp; |  |
| Excepciones | 4a. Venta inexistente: el sistema informa que la venta no existe o no está disponible. 4b. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-21 | Alta Empleado |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite registrar un nuevo empleado con sus datos personales y su rol laboral para ser utilizado en partes diarios y liquidaciones. |
| Precondición | Existen roles laborales registrados. |
| Secuencia Normal | 1- El usuario navega a Gestion de Empleados \> Registrar Empleado. 2- El sistema muestra formulario con campos: rol laboral, DNI, apellido, nombre, fecha de nacimiento, fecha de inicio de actividades y fecha de fin (opcional). 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: rol existente, DNI de 8 dígitos y único, campos obligatorios, y fechas válidas (fecha fin posterior a fecha inicio si se informa). 5- El sistema registra el empleado, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Empleado creado y disponible para asignaciones, partes diarios y liquidaciones. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios y se vuelve al listado. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-22 | Baja Empleado |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite dar de baja un empleado existente. La baja se modela como baja lógica, registrando una fecha de fin de actividades para conservar historial y trazabilidad. |
| Precondición | El empleado existe en la nómina y no debe tener liquidaciones pendientes. |
| Secuencia Normal | 1- El usuario navega a Gestión de Empleados. 2- El sistema lista los empleados existentes. 3- El usuario selecciona un empleado y elige la opción Dar de baja. 4- El sistema solicita confirmación de la acción. 5- El usuario confirma la baja. 6- El sistema registra la baja completando fecha\_fin\_actividades (por ejemplo, con la fecha actual) y guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Empleado marcado como dado de baja (con fecha\_fin\_actividades informada). |
| Excepciones | 3a. Empleado inexistente: el sistema informa que el empleado no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-23 | Modificar Empleado |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite actualizar los datos de un empleado existente (rol laboral, datos personales y fechas de actividad). |
| Precondición | Existe un empleado registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Empleados. 2- El sistema lista los empleados existentes. 3- El usuario selecciona un empleado y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida: rol existente, DNI (si se informa) de 8 dígitos y único, campos obligatorios y fechas válidas. 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Empleado actualizado. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-24 | Ver Empleado |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite consultar la información de empleados registrados y aplicar búsqueda por datos personales o rol laboral. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Empleados. 2- El sistema muestra el listado de empleados con paginación. 3- El usuario aplica búsqueda por apellido, nombre, DNI o rol laboral si lo necesita. 4- El usuario visualiza la información del empleado (rol, DNI, apellido, nombre y fechas de actividad). |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-25 | Alta Cliente |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite registrar un nuevo cliente con sus datos comerciales básicos para su uso en ventas, choferes y reportes. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Clientes \> Registrar Cliente. 2- El sistema muestra formularios con campos: razón social, CUIT, dirección y contacto (opcional). 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: campos obligatorios, CUIT con formato válido y CUIT único. 5- El sistema registra el cliente, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Cliente creado y disponible para operaciones del sistema. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-26 | Baja Cliente |
| :---- | :---- |
| Actores | Personal administrativo |
| Descripción | Permite dar de baja un cliente existente. La baja se modela como baja lógica (marcar como inactivo) para mantener historial y trazabilidad. |
| Precondición | Existe un cliente registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Clientes. 2- El sistema lista los clientes existentes con opción de búsqueda. 3- El usuario selecciona un cliente y elige la opción Dar de baja. 4- El sistema solicita confirmación. 5- El usuario confirma la baja. 6- El sistema marca el cliente como inactivo, guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Cliente marcado como inactivo y excluido de nuevas operaciones. |
| Excepciones | 3a. Cliente inexistente: el sistema informa que el cliente no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-27 | Modificar Cliente |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite actualizar los datos de un cliente existente (razón social, CUIT, dirección y contacto). |
| Precondición | Existe un cliente registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Clientes. 2- El sistema lista los clientes existentes. 3- El usuario selecciona un cliente y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida: campos obligatorios, formato de CUIT y CUIT único (si se modifica). 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Cliente actualizado. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-28 | Ver Cliente |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite consultar el listado de clientes y sus datos principales, con búsqueda por razón social, CUIT o contacto. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Clientes. 2- El sistema muestra el listado de clientes con opción de búsqueda. 3- El usuario aplica la búsqueda si lo necesita. 4- El usuario visualiza la información del cliente. |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-29 | Alta Proveedor |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite registrar un nuevo proveedor con sus datos comerciales básicos para su uso en insumos y abastecimiento. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Proveedores \> Registrar Proveedor. 2- El sistema muestra formulario con campos: razón social, CUIT, dirección, teléfono (opcional) y email (opcional). 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: campos obligatorios, CUIT con formato válido y CUIT único, y si se informa email, que sea válido. 5- El sistema registra el proveedor, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Proveedor creado y disponible para operaciones del sistema. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-30 | Baja Proveedor |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite dar de baja un proveedor existente. La baja se modela como baja lógica (marcar como inactivo) para mantener historial y trazabilidad. |
| Precondición | Existe un proveedor registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Proveedores. 2- El sistema lista los proveedores existentes con opción de búsqueda. 3- El usuario selecciona un proveedor y elige la opción Dar de baja. 4- El sistema solicita confirmación. 5- El usuario confirma la baja. 6- El sistema marca el proveedor como inactivo, guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Proveedor marcado como inactivo y excluido de nuevas operaciones. |
| Excepciones | 3a. Proveedor inexistente: el sistema informa que el proveedor no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-31 | Modificar Proveedor |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite actualizar los datos de un proveedor existente (razón social, CUIT, dirección, teléfono y email). |
| Precondición | Existe un proveedor registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Proveedores. 2- El sistema lista los proveedores existentes. 3- El usuario selecciona un proveedor y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida: campos obligatorios, formato de CUIT y CUIT único (si se modifica), y formato de email si se informa. 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Proveedor actualizado. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-32 | Ver Proveedor |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite consultar el listado de proveedores y sus datos principales, con búsqueda por razón social, CUIT o email. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Proveedores. 2- El sistema muestra el listado de proveedores con opción de búsqueda. 3- El usuario aplica la búsqueda si lo necesita. 4- El usuario visualiza la información del proveedor. |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-33 | Alta Chofer |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite registrar un nuevo chofer asociado a un cliente para su uso en cargas. |
| Precondición | Existen clientes registrados. |
| Secuencia Normal | 1- El usuario navega a Gestión de Choferes \> Registrar Chofer. 2- El sistema muestra formulario con campos: cliente, apellido, nombre, DNI, teléfono (opcional), dirección (opcional) y estado (activo/inactivo). 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: campos obligatorios, existencia de cliente, y DNI único. 5- El sistema registra el chofer, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Chofer creado y disponible para asociar a cargas. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios y se vuelve al listado. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-34 | Baja Chofer |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite dar de baja un chofer existente. La baja se modela como baja lógica (estado \= inactivo) para mantener historial y trazabilidad. |
| Precondición | Existe un chofer registrado en estado activo. |
| Secuencia Normal | 1- El usuario navega a Gestión de Choferes. 2- El sistema lista los choferes existentes con opción de búsqueda. 3- El usuario selecciona un chofer y elige la opción Dar de baja. 4- El sistema solicita confirmación de la acción. 5- El usuario confirma la baja. 6- El sistema actualiza el estado del chofer a inactivo, guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Chofer marcado como inactivo y no disponible para nuevas operaciones. |
| Excepciones | 3a. Chofer inexistente: el sistema informa que el chofer no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-35 | Modificar Chofer |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite actualizar los datos de un chofer existente (cliente asociado, datos personales y estado). |
| Precondición | Existe un chofer registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Choferes. 2- El sistema lista los choferes existentes. 3- El usuario selecciona un chofer y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida: campos obligatorios, existencia de cliente, y DNI único. 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Chofer actualizado con los nuevos datos. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-36 | Ver Chofer&nbsp; |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite consultar la información de choferes registrados y su estado (activo/inactivo). |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Choferes. 2- El sistema muestra el listado de choferes con opción de búsqueda. 3- El usuario aplica búsqueda por apellido, nombre, DNI, teléfono, dirección o cliente si lo necesita. 4- El usuario visualiza la información del chofer (cliente, datos personales y estado). |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-37 | Alta Stock Insumo |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite registrar una entrada de stock (lote de inventario) para un insumo con costo unitario y fecha, para su consumo posterior mediante FIFO. |
| Precondición | Existen insumos registrados. |
| Secuencia Normal | 1- El usuario navega a Gestión de Stock (FIFO) \> Registrar Entrada. 2- El sistema muestra formulario con campos: insumo, proveedor (opcional), cantidad, precio unitario, número de factura (opcional), fecha de compra, tipo de movimiento (compra/ajuste\_entrada/devolución) y observaciones (opcional). 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: insumo existente, cantidad \> 0, precio unitario \> 0, fecha válida y valores permitidos para tipo de movimiento. 5- El sistema registra un nuevo lote de inventario con cantidad inicial y disponible, y registra el movimiento de stock de tipo entrada con el costo total correspondiente, y registra auditoría. 6- El sistema confirma el alta y actualiza el listado y estadísticas. |
| Postcondición&nbsp; | Entrada de stock registrada y disponible para consumo. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios y se vuelve al listado. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-38 | Baja Stock Insumo |
| :---- | :---- |
| Actores | Capataz/Supervisor, Personal Administrativo |
| Descripción | Permite registrar una salida (consumo) de stock de un insumo asociada a una operación (por ejemplo, un parte diario o un mantenimiento). La baja del stock se modela como consumo lógico (movimiento de salida) y no como eliminación de registros. |
| Precondición | Existe stock disponible suficiente del insumo. |
| Secuencia Normal | 1- El usuario registra el consumo de un insumo desde una operación que lo requiera (por ejemplo, al cargar un Parte Diario o al completar un Mantenimiento). 2- El sistema solicita el insumo y la cantidad consumida. 3- El usuario completa los datos obligatorios y confirma el registro. 4- El sistema valida: insumo existente, cantidad \> 0 y stock disponible suficiente. 5- El sistema registra uno o más movimientos de stock de tipo salida aplicando FIFO sobre los lotes de inventario disponibles, actualiza cantidades disponibles y marca lotes como agotados cuando corresponda, y registra auditoría. 6- El sistema confirma el registro del consumo. |
| Postcondición&nbsp; | Stock descontado y consumo registrado como movimientos de salida. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios.  4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 4b. Stock insuficiente: el sistema informa que no hay stock disponible suficiente y no registra el consumo. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-39 | Modificar Stock Insumo |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite corregir el inventario registrando un ajuste de entrada o devolución como un nuevo movimiento de stock (sin editar históricos), manteniendo trazabilidad. |
| Precondición | Existe un insumo registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Stock (FIFO) \> Registrar Entrada. 2- El sistema muestra el formulario de entrada de stock. 3- El usuario selecciona el insumo, completa cantidad, precio unitario, fecha, y selecciona tipo de movimiento (ajuste\_entrada o devolución), y selecciona Guardar. 4- El sistema valida: insumo existente, cantidad \> 0, precio unitario \> 0 y fecha válida. 5- El sistema registra el nuevo lote/movimiento de entrada correspondiente y registra auditoría. 6- El sistema confirma el ajuste y actualiza el listado y estadísticas. |
| Postcondición&nbsp; | Stock actualizado mediante un movimiento registrado (ajuste) con trazabilidad. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error y no registra el ajuste. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-40 | Ver Stock Insumo |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite consultar el stock de insumos y el detalle de lotes de inventario (FIFO) con filtros, estadísticas y movimientos asociados. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Stock (FIFO). 2- El sistema muestra estadísticas (stock total, valor de inventario, lotes activos y próximos a agotar) y el listado de lotes de inventario. 3- El usuario aplica filtros por insumo, proveedor, tipo de movimiento, estado (disponibles/agotados) y rango de fechas si lo necesita. 4- El usuario selecciona un lote para ver su detalle. 5- El sistema muestra la información del lote (cantidades, precio, fecha, proveedor y movimientos asociados). |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Lote inexistente: el sistema informa que el lote no existe o no está disponible. 5a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-41 | Alta Carga |
| :---- | :---- |
| Actores | Capataz |
| Descripción | Permite registrar una nueva carga de madera asociada a un lote y a una fecha, con datos de ticket, pesos, destino y referencias opcionales a categoría, chofer y parte diario. |
| Precondición | Existen lotes registrados. |
| Secuencia Normal | 1- El usuario navega a Gestión de Cargas \> Registrar Carga. 2- El sistema muestra formulario con campos: lote, categoria de madera (opcional), chofer (opcional), parte diario (opcional), ticket (opcional), peso bruto (opcional), tara (opcional), peso neto (opcional), destino (opcional) y fecha de carga. 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: lote existente, fecha válida (no futura), formatos numéricos (pesos \>= 0\) y existencia de referencias opcionales si se informan. 5- El sistema registra la carga con estado inicial pendiente, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Carga creada y disponible para asociar a ventas y reportes. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios y se vuelve al listado. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 4b. Fecha futura: el sistema informa el error y no registra la carga. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar.&nbsp; |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-42 | Baja Carga |
| :---- | :---- |
| Actores | Capataz |
| Descripción | Permite dar de baja una carga existente. La baja se modela como baja lógica (marcar como inactiva) para mantener trazabilidad. |
| Precondición | Existe una carga registrada. |
| Secuencia Normal | 1- El usuario navega a Gestión de Cargas. 2- El sistema lista las cargas existentes con opción de búsqueda. 3- El usuario selecciona una carga y elige la opción Dar de baja. 4- El sistema solicita confirmación de la acción. 5- El usuario confirma la baja. 6- El sistema marca la carga como inactiva, guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Carga marcada como inactiva y excluida de nuevas operaciones. |
| Excepciones | 3a. Carga inexistente: el sistema informa que la carga no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-43 | Modificar Carga |
| :---- | :---- |
| Actores | Capataz |
| Descripción | Permite actualizar los datos de una carga existente (lote, referencias opcionales, ticket, pesos, destino y fecha). |
| Precondición | Existe una carga registrada. |
| Secuencia Normal | 1- El usuario navega a Gestión de Cargas. 2- El sistema lista las cargas existentes. 3- El usuario selecciona una carga y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida: lote existente, fecha válida (no futura), formatos numéricos (pesos \>= 0\) y existencia de referencias opcionales si se informan. 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Carga actualizada. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 6b. Fecha futura: el sistema informa el error y no guarda cambios. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-44 | Ver Carga |
| :---- | :---- |
| Actores | Capataz |
| Descripción | Permite consultar el listado de cargas y ver sus datos asociados (lote, categoría, chofer, parte diario, destino, fecha y estado). |
| Precondición |  |
| Secuencia Normal | 1- El usuario navega a Gestión de Cargas. 2- El sistema muestra el listado de cargas con paginación y búsqueda. 3- El usuario busca por ticket, destino, pesos, fecha o datos relacionados (lote, categoría o chofer) si lo necesita. 4- El usuario visualiza los datos de la carga seleccionada. |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-45 | Alta Categoría |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite registrar una nueva categoría de madera para su uso en cargas y precios. |
| Precondición |  |
| Secuencia Normal | 1- El usuario navega a Gestion de Categorias de Madera \> Registrar Categoría. 2- El sistema muestra formulario con campos: nombre y descripción (opcional). 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: nombre obligatorio, longitud mínima y que el nombre no exista. 5- El sistema registra la categoría, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Categoría creada y disponible para asociar a cargas y listas de precios. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-46 | Baja Categoría |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite dar de baja una categoría de madera existente. La baja se modela como baja lógica (marcar como inactiva) para mantener historial. |
| Precondición | Existe una categoría registrada. |
| Secuencia Normal | 1- El usuario navega a Gestión de Categorías de Madera. 2- El sistema lista las categorías existentes. 3- El usuario selecciona una categoría y elige la opción Dar de baja. 4- El sistema solicita confirmación. 5- El usuario confirma la baja. 6- El sistema marca la categoría como inactiva, guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Categoría marcada como inactiva y excluida de nuevas operaciones. |
| Excepciones | 3a. Categoría inexistente: el sistema informa que la categoría no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-47 | Modificar Categoría |
| :---- | :---- |
| Actores | Personal Administrativo. |
| Descripción | Permite actualizar los datos de una categoría de madera existente (nombre y descripción). |
| Precondición | Existe una categoría registrada. |
| Secuencia Normal | 1- El usuario navega a Gestión de Categorías de Madera. 2- El sistema lista las categorías existentes. 3- El usuario selecciona una categoría y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida: nombre obligatorio, longitud mínima y unicidad del nombre (si se modifica). 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Categoría actualizada. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-48 | Ver Categoría |
| :---- | :---- |
| Actores | Personal Administrativo. |
| Descripción | Permite consultar el listado de categorías de madera y sus datos. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Categorías de Madera. 2- El sistema muestra el listado de categorías con opción de búsqueda. 3- El usuario busca por nombre o descripción si lo necesita. 4- El usuario visualiza los datos de la categoría. |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-49 | Alta Usuario |
| :---- | :---- |
| Actores | Administrador |
| Descripción | Permite registrar un nuevo usuario del sistema con sus datos básicos, credenciales de acceso y estado activo. |
| Precondición |  |
| Secuencia Normal | 1- El usuario navega a Gestion de Usuarios \> Registrar Usuario. 2- El sistema muestra formulario con campos: nombre, apellido, email, teléfono (opcional), contraseña, confirmar contraseña y estado activo. 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: campos obligatorios, email válido y único, contraseña con longitud mínima y coincidencia con la confirmación, y estado activo booleano. 5- El sistema registra el usuario, guarda la contraseña en forma segura, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Usuario creado y habilitado para iniciar sesión según su estado activo. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-50 | Baja Usuario |
| :---- | :---- |
| Actores | Administrador&nbsp; |
| Descripción | Permite dar de baja un usuario existente. La baja se modela como baja lógica (activo \= false) para mantener trazabilidad. |
| Precondición | Existe un usuario registrado en estado activo. |
| Secuencia Normal | 1- El usuario navega a Gestión de Usuarios. 2- El sistema lista los usuarios existentes con opción de búsqueda. 3- El usuario selecciona un usuario y elige la opción Dar de baja. 4- El sistema solicita confirmación de la acción. 5- El usuario confirma la baja. 6- El sistema marca el usuario como inactivo (activo \= false), guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Usuario marcado como inactivo y no disponible para iniciar sesión. |
| Excepciones | 3a. Usuario inexistente: el sistema informa que el usuario no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-51 | Modificar Usuario |
| :---- | :---- |
| Actores | Administrador&nbsp; |
| Descripción | Permite actualizar los datos de un usuario existente (datos personales, email, teléfono, contraseña opcional y estado activo). |
| Precondición | Existe un usuario registrado. |
| Secuencia Normal | 1- El usuario navega a Gestión de Usuarios. 2- El sistema lista los usuarios existentes. 3- El usuario selecciona un usuario y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios. Si actualiza contraseña, completa contraseña y confirmación. 6- El usuario selecciona Guardar. 7- El sistema valida: campos obligatorios, email válido y único, y si se informa contraseña, longitud mínima y confirmación. 8- El sistema guarda los cambios (incluida contraseña si corresponde), registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | Usuario actualizado. |
| Excepciones | 6a. Cancelación: si el usuario cancela, no se guardan cambios. 7a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 8a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-52 | Ver Usuario |
| :---- | :---- |
| Actores | Administrador&nbsp; |
| Descripción | Permite consultar el listado de usuarios y sus datos principales, con búsqueda por nombre, apellido, email o teléfono. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Usuarios. 2- El sistema muestra el listado de usuarios con opción de búsqueda. 3- El usuario aplica la búsqueda si lo necesita. 4- El usuario visualiza la información del usuario (datos personales, email, teléfono y estado activo). |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-53 | Alta Adelanto |
| :---- | :---- |
| Actores | Personal administrativo |
| Descripción | Permite registrar un adelanto a un empleado, con fecha de emisión y monto. |
| Precondición | Existen empleados registrados. |
| Secuencia Normal | 1- El usuario navega a Gestión de Adelantos \> Registrar Adelanto. 2- El sistema muestra formulario con campos: empleado, monto y fecha de emisión. 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: empleado existente, monto numérico (\>= 0\) y fecha válida. 5- El sistema registra el adelanto con estado pendiente y activo \= true, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Adelanto creado y disponible para ser considerado en liquidaciones. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-54 | Baja Adelanto |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite dar de baja un adelanto registrado. La baja se modela como baja lógica (activo \= false) para mantener historial. |
| Precondición | Existe un adelanto registrado en estado activo. |
| Secuencia Normal | 1- El usuario navega a Gestión de Adelantos. 2- El sistema lista los adelantos existentes con opción de búsqueda. 3- El usuario selecciona un adelanto y elige la opción Dar de baja. 4- El sistema solicita confirmación. 5- El usuario confirma la baja. 6- El sistema marca el adelanto como inactivo (activo \= false), guarda los cambios y registra auditoría. 7- El sistema confirma la baja y actualiza el listado. |
| Postcondición&nbsp; | Adelanto marcado como inactivo y excluido de nuevas operaciones. |
| Excepciones | 3a. Adelanto inexistente: el sistema informa que el adelanto no existe o no está disponible. 5a. Cancelación: si el usuario cancela, no se aplican cambios. 6a. Error de persistencia/BD: el sistema informa el error y no aplica la baja. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-55 | Modificar Adelanto |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite actualizar los datos de un adelanto existente (empleado, monto y fecha de emisión). |
| Precondición | El adelanto debe existir en el sistema y estar en estado pendiente. |
| Secuencia Normal | 1- El usuario navega a Gestión de Adelantos. 2- El sistema lista los adelantos existentes. 3- El usuario selecciona un adelanto y elige la opción Editar. 4- El sistema muestra el formulario con los datos actuales. 5- El usuario modifica los campos necesarios y selecciona Guardar. 6- El sistema valida: empleado existente, monto numérico (\>= 0\) y fecha válida. 7- El sistema guarda los cambios, registra auditoría y actualiza el listado. |
| Postcondición&nbsp; | El adelanto queda actualizado en el sistema con los nuevos valores. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 7a. Error de persistencia/BD: el sistema informa el error y no aplica la modificación. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-56 | Ver Adelanto |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite consultar el listado de adelantos y sus datos, con búsqueda por monto, fecha o empleado. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Gestión de Adelantos. 2- El sistema muestra el listado de adelantos con paginación y opción de búsqueda. 3- El usuario aplica búsqueda por monto, fecha o empleado si lo necesita. 4- El usuario visualiza la información del adelanto (empleado, monto, fecha y estado). |
| Postcondición&nbsp; | \- |
| Excepciones | 4a. Error de carga: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-57 | Informes generales |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite consultar estadísticas generales de producción y costos en un rango de fechas, y exportar reportes en PDF. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega a Reportes \> Estadísticas Forestales. 2- El sistema muestra las estadísticas con un rango por defecto (por ejemplo, últimos 30 días). 3- El usuario ingresa un rango de fechas (desde/hasta) si desea filtrar y confirma la consulta. 4- El sistema valida: fechas válidas y ordena el rango si corresponde. 5- El sistema calcula y muestra indicadores y gráficos basados en cargas, partes diarios y recibos del periodo. 6- El usuario selecciona Exportar a PDF si necesita un comprobante o reporte. 7- El sistema genera el PDF y lo presenta para descarga/visualización. |
| Postcondición&nbsp; | El reporte queda disponible para consulta y exportación. |
| Excepciones | 3a. Cancelación: si el usuario cancela o no aplica filtros, se mantiene el rango actual. 4a. Fechas inválidas: el sistema informa el error y solicita corrección. 7a. Error de generación/PDF o persistencia: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-58 | Generar Recibos |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite generar un recibo de pago para un empleado a partir de una liquidación calculada, registrando monto bruto, descuentos y monto neto, y dejando el comprobante disponible para consulta. |
| Precondición | Existe una liquidación calculada para un empleado en un periodo. |
| Secuencia Normal | 1- El usuario navega a Liquidación de Pagos. 2- El sistema muestra la liquidación calculada con los importes (monto bruto, descuentos por adelantos y monto neto) y un campo de observaciones. 3- El usuario revisa los importes y selecciona Generar Recibo. 4- El sistema valida: montos numéricos (\>= 0\) y longitud de observaciones. 5- El sistema registra el recibo con fecha de emisión, deja el recibo activo, registra auditoría y marca los adelantos pendientes del periodo como pagados. 6- El sistema confirma la generación del recibo y lo deja disponible en Gestión de Recibos. |
| Postcondición&nbsp; | Recibo generado y registrado; adelantos pendientes del periodo marcados como pagados. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se genera el recibo. 4a. Datos inválidos: el sistema informa el error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-59 | Liquidar Pagos |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite calcular la liquidación de pagos de un empleado (o de todos los empleados activos) para un periodo, considerando productividad/días caídos y adelantos pendientes, dejando el resultado listo para generar recibos. |
| Precondición | Existen empleados activos registrados. |
| Secuencia Normal | 1- El usuario navega a Liquidación de Pagos. 2- El sistema muestra el selector de empleado (o la opción de liquidar a todos) y el rango de fechas. 3- El usuario selecciona el empleado y el rango de fechas y confirma Calcular Liquidación. 4- El sistema valida: empleado existente (si aplica), fechas válidas y fecha fin \>= fecha inicio. 5- El sistema calcula la liquidación del periodo, busca adelantos pendientes en el rango y propone descuentos automáticos. 6- El sistema muestra el detalle del cálculo (monto bruto, descuentos y monto neto) y habilita el paso de Generar Recibo. |
| Postcondición&nbsp; | Liquidación calculada y visualizada, lista para generar recibo. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se realiza el cálculo. 4a. Datos inválidos: el sistema informa el error y solicita corrección. 5a. Error de cálculo o persistencia/BD: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-61 | Cargar Parte diario |
| :---- | :---- |
| Actores | Capataz |
| Descripción | Permite registrar un parte diario de un lote en una fecha, indicando la tarea realizada y registrando producción (cargas) o día caído (jornales), junto con consumos de insumos y observaciones. |
| Precondición | Existe un lote registrado y existe al menos una tarea planificada para el lote (o se crea una tarea rápida al momento de cargar el parte). |
| Secuencia Normal | 1- El usuario navega a Partes Diarios \> Cargar Parte Diario. 2- El sistema muestra formulario con campos: lote, tarea del lote, fecha (hoy o hasta 7 días atras), día caído (si/no) y observaciones (opcional). 3- El usuario selecciona lote, tarea y fecha. 4- El sistema valida: lote existente, tarea válida para el lote y fecha válida (no futura y no mayor a 7 días de antigüedad). 5- El sistema consulta el estado operativo por clima para el lote/fecha y muestra el resultado. Si el día resulta no operativo, el sistema solicita confirmación (override) para continuar. 6- El usuario indica si es día caído (si/no). 7- Si no es día caído, el usuario registra una o más cargas (ticket, pesos, categoría, chofer, destino y empleados) y asocia maquinaria a cada carga cuando corresponda. 8- Si es día caído, el usuario registra jornales para empleados asignados y el motivo del día caído. 9- El usuario registra consumos de insumos (insumo, cantidad y motivo) si corresponde. 10- El usuario selecciona Guardar. 11- El sistema registra el parte diario, asocia los recursos y registra los movimientos de stock de salida (FIFO) por consumos cuando existan, y registra auditoría. 12- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Parte diario registrado y disponible para reportes, costos y liquidaciones. |
| Excepciones | 10a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Día no operativo sin confirmación: el sistema informa que requiere confirmación para continuar y no registra el parte. 9a. Stock insuficiente: el sistema informa que no hay stock suficiente para el consumo y no registra el parte/consumo. 11a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

| UC-62 | Cerrar orden de Mantenimiento |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Este caso de uso permite cerrar una orden de mantenimiento programada una vez que la intervención fue realizada. En el cierre se registran detalles de la tarea ejecutada, insumos utilizados, costos y observaciones. El cierre actualiza el historial de la máquina. |
| Precondición | Existe una orden de mantenimiento registrada en estado programado, en curso o vencido. |
| Secuencia Normal | 1- El usuario navega a Mantenimientos. 2- El sistema muestra el listado de órdenes de mantenimiento. 3- El usuario selecciona una orden y elige la opción Completar/Cerrar orden. 4- El sistema muestra un formulario/modal con campos: fecha de finalización, costo adicional (opcional) e insumos utilizados (en preventivos puede cargar el kit automáticamente). 5- El usuario completa los datos y confirma la finalización. 6- El sistema valida: fecha de finalización válida (no anterior a la fecha de inicio), insumos existentes, cantidades \> 0 y stock suficiente. 7- El sistema actualiza la orden (estado \= completado, fecha\_fin, costo\_total), registra consumos de insumos como movimientos de stock de salida (FIFO), registra detalle de insumos utilizados y registra auditoría. 8- El sistema confirma el cierre y actualiza el listado. |
| Postcondición&nbsp; | Orden de mantenimiento completada y costos/consumos registrados. |
| Excepciones | 5a. Cancelación: si el usuario cancela, no se guardan cambios. 6a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 6b. Stock insuficiente: el sistema informa el error y no cierra la orden. 7a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

| UC-63 | Programar mantenimiento |
| :---- | :---- |
| Actores | Primario: Personal Administrativo Secundario: Sistema |
| Descripción | Permite registrar una nueva orden de mantenimiento para una maquinaria, indicando tipo, fechas y estado inicial. |
| Precondición | Existen maquinarias y tipos de mantenimiento registrados. |
| Secuencia Normal | 1- El usuario navega a Mantenimientos \> Programar Mantenimiento. 2- El sistema muestra formularios con campos: maquinaria, tipo de mantenimiento, fecha de inicio, fecha programada (opcional) y estado (programado/en curso). 3- El usuario completa los datos obligatorios y selecciona Guardar. 4- El sistema valida: maquinaria existente, tipo existente, fechas válidas y que la fecha programada (si existe) sea mayor o igual a la fecha de inicio. 5- El sistema registra la orden con estado inicial, asigna id interno, guarda fecha/hora y registra auditoría. 6- El sistema confirma el alta y actualiza el listado. |
| Postcondición&nbsp; | Orden de mantenimiento registrada y disponible para seguimiento y cierre. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos/incompletos: el sistema marca campos con error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-64 | Configurar Permisos |
| :---- | :---- |
| Actores | Administrador del sistema |
| Descripción | Permite administrar roles y permisos del sistema y asignar roles a usuarios. |
| Precondición | Existe al menos un usuario registrado. |
| Secuencia Normal | 1- El usuario navega a Roles y Permisos. 2- El sistema muestra el listado de roles, permisos disponibles y usuarios. 3- El usuario selecciona un rol para ver sus permisos y marca/desmarca los permisos correspondientes. 4- El usuario confirma la actualización de permisos del rol. 5- El sistema valida que exista el rol seleccionado y guarda la nueva asignación de permisos. 6- El usuario selecciona un usuario y marca/desmarca los roles a asignar. 7- El usuario confirma la actualización de roles del usuario. 8- El sistema valida que exista el usuario seleccionado y guarda la nueva asignación de roles. |
| Postcondición&nbsp; | Roles/permisos actualizados y aplicados al control de acceso. |
| Excepciones | 4a. Cancelación: si el usuario no confirma cambios, no se actualizan permisos. 5a. Datos inválidos: el sistema informa el error y no guarda cambios. 7a. Cancelación: si el usuario no confirma cambios, no se actualizan roles. 8a. Error de persistencia/BD: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-65 | Planificación de tareas por lote (ha) |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite planificar tareas para un lote, indicando tipo de tarea y superficie afectada (ha), para soportar recomendaciones y operación. |
| Precondición | Existe un lote registrado. |
| Secuencia Normal | 1- El usuario navega a Lotes \> Planificar Tareas por Lote. 2- El sistema muestra las tareas planificadas actuales del lote (si existen) o una tarea inicial sugerida. 3- El usuario agrega o elimina filas de tareas y completa: tipo de tarea, superficie afectada (ha) y observaciones (opcional). 4- El usuario selecciona Guardar Planificación. 5- El sistema valida: al menos una tarea, tipos válidos, superficies numéricas (si se informan) y que la suma de superficies no supere la superficie total del lote. 6- El sistema guarda la planificación (reemplazando tareas planificadas sin ejecución previa), registra auditoría y solicita generación de recomendaciones automáticas. 7- El sistema confirma y redirige a recomendaciones del lote. |
| Postcondición&nbsp; | Tareas planificadas registradas y disponibles para partes diarios y recomendaciones. |
| Excepciones | 4a. Cancelación: si el usuario cancela, no se guardan cambios. 5a. Datos inválidos/incompletos: el sistema informa el error y solicita corrección. 6a. Error de persistencia/BD: el sistema informa el error, revierte la operación y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-66 | Gestionar asignaciones y propuestas |
| :---- | :---- |
| Actores | Gestionar asignaciones y propuestas |
| Descripción | Permite revisar propuestas automáticas de asignación de recursos por lote y aplicar asignaciones efectivas de empleados y maquinarias, asegurando que los recursos no queden ocupados en más de un lote activo. |
| Precondición | Existen lotes registrados y existen empleados/maquinarias disponibles. |
| Secuencia Normal | 1- El usuario navega a Propuestas Automáticas o a Asignaciones por Lote. 2- El sistema muestra el listado de propuestas (filtrable por lote y estado) o el formulario de asignaciones por lote. 3- Si se trabaja con propuestas: el usuario selecciona un lote/propuesta, revisa recursos sugeridos y marca seleccion (empleados/maquinarias/insumos) y guarda la selección. 4- El usuario confirma la propuesta o la aplica, según corresponda. 5- Si se trabaja con asignaciones directas: el usuario selecciona un lote y selecciona empleados y maquinarias para asignar y confirmar guardar. 6- El sistema valida: lote existente y que los recursos seleccionados no estén asignados a otros lotes activos. 7- El sistema sincroniza las asignaciones del lote, registra auditoría y actualiza el historial/listados. |
| Postcondición&nbsp; | Asignaciones registradas y disponibles para operación (parte diario, cargas y mantenimiento). |
| Excepciones | 4a. Cancelación: si el usuario no confirma/aplica, no se guardan cambios. 6a. Conflicto de recursos: el sistema informa que el recurso ya está asignado a otro lote activo y no guarda. 7a. Error de persistencia/BD: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-67 | Configurar notificaciones de mantenimiento |
| :---- | :---- |
| Actores | Administrador |
| Descripción | Permite configurar qué usuarios recibirán notificaciones de mantenimiento (por umbral, recordatorios y stock) y consultar/gestionar las notificaciones recibidas. |
| Precondición | Existen usuarios registrados. |
| Secuencia Normal | 1- El usuario navega a Configuración de Notificaciones de Mantenimiento. 2- El sistema muestra listados de usuarios y opciones por tipo de notificación (umbral, recordatorio, stock). 3- El usuario selecciona los usuarios para cada tipo y confirma Guardar configuración. 4- El sistema valida y guarda la configuración. 5- El usuario navega a Notificaciones. 6- El sistema lista las notificaciones del usuario con filtros por tipo y estado. 7- El usuario marca notificaciones como leídas/accionadas o navega a la orden relacionada cuando corresponda. |
| Postcondición&nbsp; | Configuración guardada y notificaciones gestionadas por el usuario. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Error de persistencia/BD: el sistema informa el error y sugiere reintentar. 7a. Notificación inexistente: el sistema informa el error y no aplica cambios. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| UC-68 | Gestionar catálogos y listas de precios |
| :---- | :---- |
| Actores | Personal Administrativo |
| Descripción | Permite administrar catálogos auxiliares (unidades de medida, tipos de maquinaria, roles laborales) y la lista de precios por cliente y categoría, para soportar insumos, maquinaria, liquidaciones y ventas. |
| Precondición | \- |
| Secuencia Normal | 1- El usuario navega al catálogo correspondiente (Unidades de Medida, Tipos de Maquinaria, Roles Laborales o Lista de Precios). 2- El sistema muestra el listado con opción de búsqueda y un formulario de alta/modificación. 3- El usuario crea o modifica un registro completando los datos y confirma Guardar. 4- El sistema valida: campos obligatorios, formatos y unicidad de nombres/fechas según corresponda. 5- El sistema registra los cambios, registra auditoría y actualiza el listado. 6- Para bajas, el usuario elige dar de baja un registro. La baja se modela como baja lógica (inactiva) para mantener trazabilidad. |
| Postcondición&nbsp; | Catálogos y lista de precios actualizados y disponibles para su uso en el sistema. |
| Excepciones | 3a. Cancelación: si el usuario cancela, no se guardan cambios. 4a. Datos inválidos o duplicados: el sistema informa el error y solicita corrección. 5a. Error de persistencia/BD: el sistema informa el error y sugiere reintentar. |

&nbsp;

&nbsp;

### **Diagramas de Secuencia de Diseño:**&nbsp; {#diagramas-de-secuencia-de-diseño:}

#### **Figura 8: UC-61: Cargar Parte Diario (Operación Crítica de Producción)** {#figura-8:-uc-61:-cargar-parte-diario-(operación-crítica-de-producción)}

![][image7]

&nbsp;

#### **Figura 9: UC-59: Liquidar Pagos (Complejidad de Reglas Financieras)** {#figura-9:-uc-59:-liquidar-pagos-(complejidad-de-reglas-financieras)}

**![][image8]**

#### **Figura 10: UC-65: Planificación de Tareas por Lote (Soporte Operativo)** {#figura-10:-uc-65:-planificación-de-tareas-por-lote-(soporte-operativo)}

**![][image9]**

#### 

**![][image10]**

&nbsp;

&nbsp;

&nbsp;

&nbsp;

#### **Figura 12: UC-62: Cerrar Orden de Mantenimiento (Cierre Complejo con FIFO)** {#figura-12:-uc-62:-cerrar-orden-de-mantenimiento-(cierre-complejo-con-fifo)}

**![][image11]**

#### **Figura 13: UC-13: Alta Venta** {#figura-13:-uc-13:-alta-venta}

**![][image12]**

#### **Figura 14: UC-41: Alta Carga** {#figura-14:-uc-41:-alta-carga}

**![][image13]**

#### **Figura 15: UC-66: Gestionar Asignaciones y Propuestas** {#figura-15:-uc-66:-gestionar-asignaciones-y-propuestas}

**![][image14]**

#### **Figura 16: UC-01: Alta Lote** {#figura-16:-uc-01:-alta-lote}

**![][image15]**

#### **Figura 17: UC-57: Informes Generales** {#figura-17:-uc-57:-informes-generales}

**![][image16]**

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

### **Requisitos No funcionales** {#requisitos-no-funcionales}

&nbsp;

| NFR–01&nbsp; | Copias de seguridad&nbsp; |
| :---- | :---- |
| **Objetivos asociados**&nbsp; | OBJ-07 Seguridad y auditoria funcional del sistema |
| **Requisitos asociados**&nbsp; | RF-16 Registro de auditoría |
| **Descripción**&nbsp; | El sistema deberá incorporar un mecanismo de copias de seguridad automáticas de la base de datos con capacidad de restauración ante incidentes o desastres. |
|  |  |
| **Comentarios**&nbsp; | La política de retención, ubicación del respaldo y periodicidad de verificación de restauración se definen a nivel operativo. |

&nbsp;

&nbsp;

| NFR–02 | Control de acceso |
| :---- | :---- |
| **Objetivos asociados**&nbsp; | OBJ-07 Seguridad y auditoria funcional del sistema |
| **Requisitos asociados**&nbsp; | RF-14 Gestión de usuarios y roles RF-15 Configuración de permisos |
| **Descripción**&nbsp; | El sistema deberá implementar autenticación de usuarios y autorización basada en roles y permisos por módulo, restringiendo el acceso a funciones según el perfil habilitado. |
|  |  |
| **Comentarios**&nbsp; | El control de acceso aplica a usuarios autenticados y a las sesiones activas del sistema.&nbsp; |

&nbsp;

| NFR–03 | Registros de auditoría |
| :---- | :---- |
| **Objetivos asociados**&nbsp; | OBJ-07 Seguridad y auditoria funcional del sistema |
| **Requisitos asociados**&nbsp; | RF-16 Registro de auditoría |
| **Descripción**&nbsp; | Toda acción de alta, baja y modificación sobre entidades auditables, así como el acceso a información sensible o operaciones críticas, deberá generar un registro de auditoría interno con usuario, fecha, hora, acción y entidad afectada. |
|  |  |
| **Comentarios**&nbsp; | La auditoría debe ser trazable y protegida contra modificaciones por usuarios finales. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

| NFR–04 | Error de Persistencia |
| :---- | :---- |
| **Objetivos asociados**&nbsp; | OBJ-01 Centralización e integración de registros operativos y administrativos |
| **Requisitos asociados**&nbsp; | RF-01, RF-02, RF-03, RF-04, RF-05, RF-06, RF-07, RF-08, RF-09, RF-10, RF-12, RF-13, RF-18, RF-19, RF-20, RF-21&nbsp; |
| **Descripción**&nbsp; | En caso de error de persistencia en la base de datos o fallo de transacción, el sistema debera informar al usuario de forma clara, ejecutar rollback sobre la operación afectada y dejar los datos en un estado consistente. |
|  |  |
| **Comentarios**&nbsp; | La recuperación aplica a operaciones que modifiquen estado y dependen de una transacción atómica. |

&nbsp;

&nbsp;

| NFR–05 | Exportación de archivos |
| :---- | :---- |
| **Objetivos asociados**&nbsp; | OBJ-05 Soporte analitico financiero y de costos |
| **Requisitos asociados**&nbsp; | RF-11 Generación de informes financieros RF-17 Generación de indicadores de gestión |
| **Descripción**&nbsp; | El sistema deberá permitir la exportación de reportes y listados gerenciales en formato PDF. La exportación a Excel podrá habilitarse para reportes tabulares cuando el módulo de reportes lo soporte. |
|  |  |
| **Comentarios**&nbsp; | PDF es el formato base del proyecto; Excel queda condicionado a la implementación del componente de exportación tabular. |

&nbsp;

| NFR–06 | Operación de procesos en segundo plano |
| :---- | :---- |
| **Objetivos asociados**&nbsp; | OBJ-02 Soporte operativo y trazabilidad de la producción forestal; OBJ-03 Gestión del mantenimiento y control de costos de maquinaria |
| **Requisitos asociados**&nbsp; | RF-18 Sincronización y análisis climático automático; RF-19 Propuestas de asignación automática; RF-20 Generación automática de órdenes de mantenimiento; RF-21 Monitoreo de procesos automatizados |
| **Descripción**&nbsp; | El sistema deberá ejecutarse sobre un planificador de tareas del servidor (cron que invoque al scheduler) y un worker de cola activo para el funcionamiento de los procesos automatizados. La ausencia de estos servicios no corrompe datos ni bloquea la operación interactiva, pero detiene los procesos automáticos; su efecto es detectable en la pantalla de monitoreo (RF-21) mediante trabajos pendientes sin procesar. |
|  |  |
| **Comentarios**&nbsp; | La configuración operativa (entrada de crontab por minuto y worker de cola persistente) se detalla en la guía de despliegue del sistema. |

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

&nbsp;

2. 

   3. ### **Matriz de Rastreabilidad Objetivo/Requisitos** {#matriz-de-rastreabilidad-objetivo/requisitos}

&nbsp;

| Objetivo | Requisitos de información (IRQ) | Requisitos funcionales (RF) | Requisitos no funcionales (NFR) |
| :---- | :---- | :---- | :---- |
| OBJ-01 Centralización e integración de registros operativos y administrativos | IRQ-01, IRQ-02, IRQ-03, IRQ-04, IRQ-05, IRQ-06, IRQ-09, IRQ-10 | RF-01..RF-13, RF-18..RF-21 | NFR04, NFR06 |
| OBJ-02 Soporte operativo y trazabilidad de la producción forestal | IRQ-01, IRQ-08, IRQ-09, IRQ-10 | RF-01, RF-02, RF-03, RF-17, RF-18, RF-19 | NFR05, NFR06 |
| OBJ-03 Gestión del mantenimiento y control de costos de maquinaria | IRQ-02, IRQ-04, IRQ-09, IRQ-10 | RF-04, RF-05, RF-06, RF-20 | NFR04, NFR06 |
| OBJ-04 Automatización de la liquidación de nóminas de Recursos Humanos | IRQ-03, IRQ-04 | RF-07, RF-08, RF-09, UC-58, UC-59 | NFR04 |
| OBJ-05 Soporte analitico financiero y de costos | IRQ-04, IRQ-08 | RF-10, RF-11, RF-17 | NFR05 |
| OBJ-06 Gestión administrativa comercial y de abastecimiento | IRQ-05 | RF-12, RF-13 | NFR04 |
| OBJ-07 Seguridad y auditoria funcional del sistema | IRQ-06, IRQ-07, IRQ-10 | RF-14, RF-15, RF-16, RF-20, RF-21 | NFR01, NFR02, NFR03 |

&nbsp;

      1. ### ***Glosario de Términos*** {#glosario-de-términos}

&nbsp;

| Término | Categoría | Comentarios |
| :---- | :---- | :---- |
| Lote | Entidad de dominio | Unidad productiva forestal con superficie, ubicación, especie y estado. |
| LoteTarea | Entidad de dominio | Planificación por lote con tipo de tarea, superficie en ha y estado. |
| Parte Diario | Registro operativo | Registro diario con empleados, maquinaria, cargas e insumos. |
| Carga | Transacción operativa | Transporte de madera desde un lote hacia un destino, con datos de bruto, tara y neto. |
| Categoría de Madera | Catálogo maestro | Clasificación usada en cargas y listas de precios. |
| Maquinaria | Entidad de dominio | Equipo productivo registrado para explotación y mantenimiento. |
| Mantenimiento | Orden operativa | Orden de servicio programada, en curso, vencida o completada. |
| Insumo | Catálogo / inventario | Material consumible cuyo stock se calcula por movimientos. |
| Movimiento de Stock | Transacción de inventario | Entrada o salida de insumos con referencia operativa. |
| Lote Inventario | Lote FIFO de stock | Lote de stock con cantidad disponible y costo. |
| Empleado | Entidad de dominio | Personal de la empresa vinculado a roles laborales y liquidaciones. |
| Chofer | Entidad de dominio | Transportista externo asociado a cargas. |
| Cliente | Entidad comercial | Comprador de productos o servicios. |
| Proveedor | Entidad comercial | Suministrador de insumos o servicios. |
| Venta | Transacción comercial | Operación comercial asociada a cargas y clientes. |
| Usuario | Identidad de acceso | Entidad principal de autenticación del sistema. |
| Rol/Permiso | Seguridad y acceso | Control de acceso por funciones y módulos. |
| KPI | Indicador de gestión | Métrica generada a partir de datos operativos. |
| API Clima | Servicio externo | Fuente externa para análisis y recomendaciones climáticas. |
| Propuesta de Asignación | Recomendación automática | Sugerencia de recursos por lote o tarea. |
| Asignación de recursos | Relación operativa | Vinculación efectiva de empleados, maquinarias e insumos a un lote o tarea. |
| Notificación de mantenimiento | Alerta del sistema | Aviso interno sobre órdenes programadas, vencidas o pendientes. |
| Configuración del sistema | Parámetros globales | Umbrales, horarios y reglas generales del sistema. |
| Catálogo maestro | Tabla de referencia | Catálogo de tipos, unidades y listas de precios. |
| Proceso automatizado | Concepto de proceso | Flujo del sistema que corre solo, disparado por tiempo, evento o cambio de estado (PA-01, PA-02, PA-03). |
| Cola de trabajos | Infraestructura técnica | Ejecución asincrónica de trabajos; requiere worker activo (NFR-06) y su estado es visible en la pantalla de monitoreo. |

&nbsp;

[image1]: <data:image/png;base64,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>

[image2]: <data:image/png;base64,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hHvttdfcu+++67766iv3448/ut9++81/4jfCsR/xEB/pkH7IkCHu/PPPdwcffLA/Ps6D80022WT+/LgOXM9JJ53k0+A6f/3119gEFZRV3MDn/fv3j4MTufjii+OgpinW+hwp2rGEEAK0iJtPP/3UPfDAA+6AAw5wG220kevcubNbeeWVXd++fd1hhx3mzj77bHffffe5V155JU7aUnB+XAeuZ+DAga5Pnz5uxRVXdFNOOaW/blw/hNJnn30WJ21LyipuIEhXWWWVODiRp556Kg5qmmKtz5GiHUsIIaBdxQ1qTE4++WS38847++aFOeec062zzjruzDPPdHfccUccvS3AdeP61157bdelSxe32mqr+SHKN998s+/c2o6UVdygz83CCy8cByfCPjcNULRjCSEEtIO4QbMSmnZWWmkld+CBB/omIYvcdNNNvmYHNT29e/f2zVplp6ziRnjzzTfjoA5effVV9+c//zkOzoRyWJ8DZXEsIcQ2ZRU3999/v6+J6dSpk29WuvPOO+Mophk5cqRv1kI/nh49evgmuTJSdnGz6KKL+pqxJDAMHDU8eVAO63OgLI4lhNimLOLmuOOO84XJWWed5T7//PN4N6kD9DtaYYUV/ORzxx9/fLy7EMoubgT0zVp++eX9/evXr58bPnx4HCVTymV9hpTNsYQQmxQtbs477zzft+TII49s234lZQP9kY444gi3xhpr+FFbRdIu4qbVqLXeumMJIeWgCHGDYdqYQK3StPe1WGCBBfw7FM1W33//fbw7NbfddlvHMTGEu1FwTdhwHIBjnHLKKf54ElYEOPd8883nh7G3GoqbZNRab92xhJBy0Gpxg06wGKadlk8++cQLBoDPLAVOM+IG14JrAxBfOJaIm7KA+X1afT0UN8motd66Ywkh5aBV4mbbbbdtepQTRAfERxLTTjtthziBqMAGkfH000937Ft22WX9bxTwIowQDlGCfUniBsfAfjkmtlhQIR3ixcQ1N7LhtxxXPuU6JF18HIDzSpxmwPG22267ODgXKG6SUWu9dccSQspBK8QNCvAs+tNUEzciOERoiBCRNKG4CdPUEjcCREoocEIkXUySuBFxIjUoOG8sWOLjb7755onX1Awvv/yyW2ihheLgzKG4SUat9dYdSwgpB60QN++//34clIp6xA3ECwRDKCxExEgtjsStR9zgt/Sl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>

[image3]: <data:image/png;base64,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>

[image4]: <data:image/png;base64,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>

[image5]: <data:image/png;base64,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>

[image6]: <data:image/png;base64,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>

[image7]: <data:image/png;base64,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>

[image8]: <data:image/png;base64,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>

[image9]: <data:image/png;base64,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>

[image10]: <data:image/png;base64,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>

[image11]: <data:image/png;base64,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>

[image12]: <data:image/png;base64,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>

[image13]: <data:image/png;base64,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>

[image14]: <data:image/png;base64,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>

[image15]: <data:image/png;base64,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>

[image16]: <data:image/png;base64,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>