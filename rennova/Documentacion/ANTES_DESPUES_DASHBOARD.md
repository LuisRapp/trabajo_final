# 🎨 Guía Rápida: Antes vs Después - Dashboard Rennova

## 🔴 1. Alerta Principal

### ❌ ANTES (Bootstrap amateur)
```html
<div class="alert alert-danger mb-4 p-4 rounded-2 border-3" style="border-width: 2px;">
    <h2 class="fw-bold mb-2" style="font-size: 1.5rem;">
        ALERTA: SUSPENDER OPERACIONES
    </h2>
    <p class="mb-0" style="font-size: 1.05rem;">
        Se pronostican lluvias fuertes para el <span class="fw-bold">Miércoles</span>
    </p>
</div>
```

### ✅ DESPUÉS (Tailwind SaaS Profesional)
```html
<div class="rounded-xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex-1 space-y-2">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-100 text-rose-800 text-xl">
                    🛑
                </span>
                <h2 class="text-2xl font-semibold text-rose-900">
                    Suspender Operaciones
                </h2>
            </div>
            <p class="text-sm leading-relaxed text-slate-700">
                Se pronostican lluvias fuertes para el 
                <span class="font-semibold text-rose-900">Miércoles</span>
            </p>
        </div>
    </div>
</div>
```

**Mejoras:**
- Colores suaves (`rose-50`, `rose-100`, `rose-900`)
- Badge con icono separado
- Tipografía más elegante
- Espaciado consistente con `gap-*` y `space-y-*`

---

## 📅 2. Pronóstico de 7 Días

### ❌ ANTES
```html
<div class="col-6 col-md-4 col-lg-1-7">
    <div class="card bg-danger text-white text-center rounded-2 shadow-sm p-2">
        <div class="card-body p-2">
            <small class="fw-bold d-block mb-1">Lun 20</small>
            <div class="mb-2">☀️</div>
            <span class="badge bg-white text-danger">
                Inactivo
            </span>
        </div>
    </div>
</div>
```

### ✅ DESPUÉS
```html
<div class="relative overflow-hidden rounded-xl border border-rose-200 bg-white shadow-sm transition-all hover:shadow-md">
    <div class="p-4 text-center">
        <p class="mb-2 text-xs font-medium text-slate-600">Lun 20</p>
        <div class="mb-3 text-3xl">☀️</div>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-rose-100 text-rose-700">
            Inactivo
        </span>
    </div>
    <!-- Barra de estado inferior -->
    <div class="h-1 w-full bg-rose-500"></div>
</div>
```

**Mejoras:**
- Fondo blanco con borde de color (no todo el card coloreado)
- Barra de estado inferior sutil (`h-1`)
- Hover effect (`hover:shadow-md`)
- Badge redondeado (`rounded-full`)

---

## 📊 3. KPIs (Análisis de Impacto)

### ❌ ANTES
```html
<div class="col-md-4">
    <div class="card rounded-2 shadow-sm border-start border-danger border-5 p-3">
        <h6 class="fw-bold text-muted small mb-3 text-uppercase">
            📅 DÍAS PERDIDOS
        </h6>
        <div class="mb-2">
            <span class="display-4 fw-bold text-danger">3</span>
            <small class="text-muted ms-2">días</small>
        </div>
        <small class="text-muted">Incluye lluvia y barro</small>
    </div>
</div>
```

### ✅ DESPUÉS
```html
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-4">
        <!-- Icono moderno -->
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-rose-100">
            <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <!-- Contenido -->
        <div class="flex-1">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                Días Perdidos
            </p>
            <p class="mt-1 text-3xl font-bold text-slate-900">
                3
            </p>
            <p class="mt-1 text-xs text-slate-600">
                Incluye lluvia y barro post-lluvia
            </p>
        </div>
    </div>
</div>
```

**Mejoras:**
- Icono SVG profesional (no emoji)
- Layout horizontal con `flex`
- Jerarquía visual clara
- Colores neutros (`slate-*`)

---

## 🎯 4. Selector de Lote

### ❌ ANTES
```html
<div class="flex items-center justify-between border border-neutral-200 bg-white p-4">
    <div class="text-sm text-neutral-700">
        <span class="font-medium">Seleccionar lote:</span>
    </div>
    <form class="flex items-center gap-2">
        <select class="rounded-md border px-3 py-2 text-sm">...</select>
        <button class="rounded-md border bg-neutral-50 px-3 py-2 text-sm">Ver</button>
    </form>
</div>
```

### ✅ DESPUÉS
```html
<div class="flex flex-col items-start gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center">
    <!-- Header con icono -->
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
            <svg class="h-5 w-5 text-emerald-600">...</svg>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Dashboard de Operaciones</h2>
            <p class="text-sm text-slate-500">Monitoreo climático y gestión de lotes</p>
        </div>
    </div>
    <!-- Form mejorado -->
    <form class="flex items-center gap-3">
        <label class="text-sm font-medium text-slate-700">Seleccionar lote:</label>
        <select class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm shadow-sm 
                       focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
            ...
        </select>
        <button class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-medium text-white shadow-sm 
                       hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500/50">
            Ver Pronóstico
        </button>
    </form>
</div>
```

**Mejoras:**
- Header descriptivo con icono
- Botón con color de marca (`emerald-600`)
- Estados de focus accesibles
- Responsive con `sm:flex-row`

---

## 🚫 5. Estado Vacío

### ❌ ANTES
```html
<div class="rounded-xl border border-neutral-200 p-6 text-center text-neutral-600">
    No hay lotes configurados aún. Crea un lote para ver el pronóstico.
</div>
```

### ✅ DESPUÉS
```html
<div class="flex flex-col items-center justify-center gap-4 
            rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
    <!-- Icono grande -->
    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-200">
        <svg class="h-8 w-8 text-slate-400">...</svg>
    </div>
    <!-- Texto -->
    <div>
        <h3 class="text-lg font-semibold text-slate-900">No hay lotes configurados</h3>
        <p class="mt-1 text-sm text-slate-600">
            Crea un lote para comenzar a ver pronósticos climáticos y análisis operativos.
        </p>
    </div>
    <!-- CTA -->
    <a href="..." class="mt-2 inline-flex items-center gap-2 rounded-lg 
                         bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm 
                         hover:bg-emerald-700">
        <svg class="h-4 w-4">+</svg>
        Crear Primer Lote
    </a>
</div>
```

**Mejoras:**
- Borde dashed (`border-dashed`)
- Icono grande centrado
- Call-to-action visible
- Jerarquía de información clara

---

## 🎨 Paleta de Colores Profesional

### Marca Principal
```css
emerald-50   /* Fondos suaves */
emerald-100  /* Badges/iconos */
emerald-600  /* Botones primarios */
emerald-700  /* Hover states */
emerald-900  /* Textos destacados */
```

### Estados de Alerta
```css
/* Suspender (Danger) */
rose-50/100/200/500/700/900

/* Acelerar (Warning) */
amber-50/100/200/600/800/900

/* Normal (Success) */
emerald-50/100/200/500/700/900
```

### Neutros
```css
slate-50   /* Fondos secundarios */
slate-200  /* Bordes */
slate-500  /* Textos secundarios */
slate-700  /* Textos principales */
slate-900  /* Títulos */
```

---

## 📐 Sistema de Espaciado

```css
gap-2   /* 0.5rem - elementos muy juntos */
gap-3   /* 0.75rem - elementos cercanos */
gap-4   /* 1rem - separación normal */
gap-6   /* 1.5rem - secciones */

p-4     /* 1rem - padding pequeño */
p-5     /* 1.25rem - padding medio */
p-6     /* 1.5rem - padding grande */
p-12    /* 3rem - padding extra grande */
```

---

## 🔤 Tipografía

```css
text-xs     /* 0.75rem - etiquetas */
text-sm     /* 0.875rem - cuerpo */
text-base   /* 1rem - subtítulos */
text-lg     /* 1.125rem - títulos secundarios */
text-2xl    /* 1.5rem - títulos principales */
text-3xl    /* 1.875rem - números grandes (KPIs) */

font-medium   /* 500 - labels */
font-semibold /* 600 - títulos */
font-bold     /* 700 - énfasis */
```

---

## ✨ Transiciones y Efectos

```css
/* Hover effects en cards */
transition-all hover:shadow-md

/* Focus states en inputs */
focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20

/* Hover en botones */
hover:bg-emerald-700
```

---

**Compilar cambios:**
```bash
cd rennova
npm run build
```

**Ver resultado:**
```bash
php artisan serve
# Visitar: http://127.0.0.1:8000/dashboard
```
