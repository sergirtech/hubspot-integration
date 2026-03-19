# Documentación — Integración HubSpot para BMG

## Índice
1. [Visión general](#visión-general)
2. [Arquitectura](#arquitectura)
3. [Lo que está hecho](#lo-que-está-hecho)
4. [Estructura de archivos](#estructura-de-archivos)
5. [Flujo de datos](#flujo-de-datos)
6. [Campos sincronizados a HubSpot](#campos-sincronizados-a-hubspot)
7. [Propiedades a crear en HubSpot](#propiedades-a-crear-en-hubspot)
8. [Cómo probar en local](#cómo-probar-en-local)
9. [Cómo integrar en BMG](#cómo-integrar-en-bmg)
10. [Lo que falta por hacer](#lo-que-falta-por-hacer)
11. [Email marketing](#email-marketing)

---

## Visión general

Este proyecto es un **mock de desarrollo** para construir y testear la integración entre BMG y HubSpot **antes de tener acceso al proyecto BMG real**. Una vez validado aquí, el código se trasladará directamente al proyecto BMG.

### Objetivo
Sincronizar datos de editores desde BMG a HubSpot para uso comercial y de marketing. La comunicación es **unidireccional: BMG → HubSpot**.

### Lo que NO es este proyecto
- No es un servicio independiente en producción.
- No sustituye a BMG ni a HubSpot.
- Es un entorno de pruebas que se descartará cuando el código esté integrado en BMG.

---

## Arquitectura

```
BMG (fuente de verdad)
        │
        │  2 veces por semana (scheduler)
        │  o en tiempo real (observer/comando)
        ▼
 HubspotClient (servicio PHP)
        │
        │  PATCH si existe / POST si no existe
        ▼
HubSpot CRM (uso comercial y marketing)
```

### Principios de diseño
- **BMG → HubSpot únicamente.** HubSpot no escribe en BMG.
- **Upsert real:** si el contacto ya existe en HubSpot se actualiza; si no, se crea. Se identifica por email.
- **Resiliencia:** si un editor falla, el proceso continúa con el siguiente.
- **Sin acoplamiento:** el `HubspotClient` es un servicio independiente que no depende de ninguna otra parte de BMG.

---

## Lo que está hecho

### HubspotClient — `app/Services/Hubspot/HubspotClient.php`
Servicio que gestiona toda la comunicación con la API de HubSpot.

**Método principal: `upsertContact(Editor $editor)`**
- Intenta actualizar el contacto via `PATCH /crm/v3/objects/contacts/{email}?idProperty=email`
- Si HubSpot devuelve 404 (no existe), lo crea via `POST /crm/v3/objects/contacts`
- Cualquier otro error lanza una excepción

---

### Editor — `app/Models/Editor.php`
DTO (Data Transfer Object) que representa un editor de BMG y lo mapea a los campos de HubSpot.

**Campos de contacto:**
- `firstname` — nombre fiscal
- `lastname` — apellidos
- `email` — email principal
- `phone` — teléfono principal
- `address` — calle + número
- `zip` — código postal

**Campos custom de BMG:**
- `codeditorbmg` — código único del editor en BMG
- `filial` — filial a la que pertenece (espana, mexico, colombia...)
- `tipo_editor` — tipo (autonomo, editorial...)

**Campos numéricos del catálogo:**
- `num_titulos` — total de títulos que maneja
- `num_titulos_activos` — títulos actualmente en activo
- `total_ventas_eur` — importe total de ventas acumulado
- `unidades_vendidas` — total de unidades vendidas
- `ultima_fecha_venta` — fecha de la última venta registrada

> **Nota:** Los nombres de los campos del constructor (`nombre_fiscal`, `apellidos`, `domicilios`, etc.) están basados en la estructura de la API de BMG observada. Habrá que confirmarlos cuando se tenga acceso al proyecto BMG real.

---

### MockBmgClient — `app/Services/Bmg/MockBmgClient.php`
Implementa la misma interfaz que el `BmgClient` real pero devuelve datos fake en vez de llamar a la API de BMG. Permite probar toda la integración sin tener acceso a BMG.

**Métodos:**
- `getEditor(string $codEditorBmg): array` — devuelve un editor por código
- `getAllEditors(): Collection` — devuelve todos los editores

---

### MockBmgData — `app/Services/Bmg/MockBmgData.php`
Array estático con 3 editores fake (España, México, Colombia) con todos los campos rellenos, incluyendo los campos numéricos del catálogo.

---

### BmgClient — `app/Services/Bmg/BmgClient.php`
Cliente real para la API de BMG. Gestiona autenticación con caché de token (90 minutos por filial).

**Métodos:**
- `getToken()` — obtiene token cacheado o autentica
- `getEditor(string $codEditorBmg): array` — obtiene un editor por código
- `getAllEditors(): Collection` — obtiene todos los editores con paginación automática

> **Nota:** Este cliente NO se usará en la integración final con BMG, ya que BMG ya tiene acceso directo a su propia base de datos. Se usaba en este proyecto mock para conectar con la API de BMG externamente.

---

### SyncAllFilialesCommand — `app/Console/Commands/SyncAllFilialesCommand.php`
Comando Artisan que sincroniza todos los editores de todas las filiales activas a HubSpot. Es el comando que se programará para ejecutarse periódicamente en BMG.

```bash
php artisan sync:filiales
```

---

### SyncEditorCommand — `app/Console/Commands/SyncEditorCommand.php`
Comando Artisan para forzar manualmente la sincronización de un editor concreto. Útil para depuración o para re-sincronizar un editor que haya fallado.

```bash
php artisan sync:editor {codEditorBmg} {filial}
# Ejemplo:
php artisan sync:editor ED001 espana
```

---

### MockSyncEditorsCommand — `app/Console/Commands/MockSyncEditorsCommand.php`
Comando de pruebas que sincroniza los editores fake del `MockBmgClient`. Tiene modo `--dry-run` para verificar el mapeo de datos sin hacer ninguna llamada a HubSpot.

```bash
# Ver qué se mandaría a HubSpot sin enviar nada:
php artisan mock:sync-editors --dry-run

# Sync real (requiere HUBSPOT_TOKEN en .env):
php artisan mock:sync-editors
```

---

### FilialController — `app/Http/Controllers/FilialController.php`
API REST para gestionar las filiales (credenciales de cada país de BMG). Solo relevante para este proyecto mock; en BMG no será necesario.

```
GET    /api/filiales
POST   /api/filiales
GET    /api/filiales/{id}
PUT    /api/filiales/{id}
DELETE /api/filiales/{id}
```

---

## Estructura de archivos

```
app/
├── Console/Commands/
│   ├── MockSyncEditorsCommand.php   ← pruebas con datos fake
│   ├── SyncAllFilialesCommand.php   ← sync masivo (va a BMG)
│   └── SyncEditorCommand.php        ← sync manual de un editor (va a BMG)
├── Http/Controllers/
│   ├── FilialController.php         ← solo para este mock
│   └── SyncController.php           ← webhook (descartado para BMG)
├── Models/
│   ├── Editor.php                   ← DTO editor (va a BMG)
│   └── Filial.php                   ← solo para este mock
└── Services/
    ├── Bmg/
    │   ├── BmgClient.php            ← solo para este mock
    │   ├── MockBmgClient.php        ← solo para este mock
    │   └── MockBmgData.php          ← solo para este mock
    └── Hubspot/
        └── HubspotClient.php        ← va a BMG
config/
├── hubspot.php                      ← va a BMG
└── bmg.php                          ← solo para este mock
```

---

## Flujo de datos

### Sincronización periódica (principal)
```
Scheduler de Laravel (2x semana)
        ↓
SyncAllFilialesCommand
        ↓
Para cada editor en la BD de BMG:
  - Construir objeto Editor con sus datos
  - HubspotClient::upsertContact(editor)
        ↓
HubSpot actualiza o crea el contacto
```

### Alta de un editor nuevo
```
Editor se da de alta en BMG
        ↓
El scheduler lo recoge en la próxima ejecución
        ↓
HubSpot crea el contacto nuevo
```

> En el futuro se puede hacer inmediato añadiendo un Observer en el modelo Editor de BMG, pero no es necesario para el funcionamiento actual.

---

## Campos sincronizados a HubSpot

| Campo en HubSpot | Tipo | Origen en BMG | Notas |
|---|---|---|---|
| `firstname` | Texto | `nombre_fiscal` | Estándar HubSpot |
| `lastname` | Texto | `apellidos` | Estándar HubSpot |
| `email` | Texto | `contactos[0]` | Estándar HubSpot — identificador único |
| `phone` | Texto | `contactos[1]` | Estándar HubSpot |
| `address` | Texto | `domicilios[0].calle + numero` | Estándar HubSpot |
| `zip` | Texto | `domicilios[0].codigo_postal` | Estándar HubSpot |
| `codeditorbmg` | Texto | `cod_editor_bmg` | **Custom** — crear en HubSpot |
| `filial` | Texto | `filial` | **Custom** — crear en HubSpot |
| `tipo_editor` | Texto | `tipo_editor` | **Custom** — crear en HubSpot |
| `num_titulos` | Número | `num_titulos` | **Custom** — crear en HubSpot |
| `num_titulos_activos` | Número | `num_titulos_activos` | **Custom** — crear en HubSpot |
| `total_ventas_eur` | Número | `total_ventas_eur` | **Custom** — crear en HubSpot |
| `unidades_vendidas` | Número | `unidades_vendidas` | **Custom** — crear en HubSpot |
| `ultima_fecha_venta` | Fecha | `ultima_fecha_venta` | **Custom** — crear en HubSpot |

---

## Propiedades a crear en HubSpot

Ir a **Settings → Properties → Contact properties → Create property** y crear:

| Nombre interno | Etiqueta visible | Tipo | Importante |
|---|---|---|---|
| `codeditorbmg` | Código editor BMG | Single-line text | Marcar como **unique identifier** |
| `filial` | Filial | Dropdown | Valores: espana, mexico, colombia, peru, argentina, brasil, chile, guatemala, uruguay, ecuador |
| `tipo_editor` | Tipo de editor | Dropdown | Valores a confirmar con BMG |
| `num_titulos` | Nº títulos | Number | — |
| `num_titulos_activos` | Nº títulos activos | Number | — |
| `total_ventas_eur` | Total ventas (€) | Number | — |
| `unidades_vendidas` | Unidades vendidas | Number | — |
| `ultima_fecha_venta` | Última fecha de venta | Date | — |

> Las propiedades estándar (`firstname`, `lastname`, `email`, `phone`, `address`, `zip`) ya existen en HubSpot y no hay que crearlas.

---

## Cómo probar en local

### Requisitos previos
- PHP 8.2+ con extensiones: `sqlite3`, `pdo_sqlite`, `fileinfo`, `curl`, `openssl`, `mbstring`
- Composer instalado

### Instalación
```bash
composer install
cp .env.example .env
php artisan key:generate
New-Item database/database.sqlite   # Windows
php artisan migrate
```

### Variables de entorno necesarias (`.env`)
```
HUBSPOT_TOKEN=tu_token_aqui   # necesario para sync real
```

### Probar el mapeo de datos (sin token)
```bash
php artisan mock:sync-editors --dry-run
```

### Probar sync real (con token)
```bash
php artisan mock:sync-editors
```

---

## Cómo integrar en BMG

Cuando se tenga acceso al proyecto BMG, hay que:

### 1. Copiar estos archivos a BMG
- `app/Services/Hubspot/HubspotClient.php`
- `app/Models/Editor.php` (ajustar mapeo de campos)
- `app/Console/Commands/SyncAllFilialesCommand.php` (adaptar para usar BD de BMG directamente)
- `app/Console/Commands/SyncEditorCommand.php`
- `config/hubspot.php`

### 2. Añadir al `.env` de BMG
```
HUBSPOT_TOKEN=tu_token_aqui
```

### 3. Programar la sincronización en `routes/console.php` de BMG
```php
// Lunes y jueves a las 8:00
Schedule::command('sync:filiales')->weeklyOn([1, 4], '08:00');
```

### 4. Asegurarse de que el cron de Laravel está activo en el servidor de BMG
```
* * * * * php /ruta/a/bmg/artisan schedule:run
```

### 5. Ajustar el mapeo de campos en `Editor.php`
Revisar que los nombres de las claves del array (`nombre_fiscal`, `apellidos`, `domicilios`, `contactos`, etc.) coinciden con los nombres reales de las columnas o respuestas de BMG.

### Lo que NO hay que copiar a BMG
- `BmgClient.php` — BMG tiene acceso directo a su BD, no necesita llamar a su propia API
- `MockBmgClient.php` / `MockBmgData.php` — solo son para pruebas
- `MockSyncEditorsCommand.php` — solo para pruebas
- `FilialController.php` / `Filial.php` — BMG ya gestiona sus filiales
- `SyncController.php` — el webhook no aplica en la arquitectura final

---

## Lo que falta por hacer

### Pendiente de credenciales HubSpot
- [ ] Obtener `HUBSPOT_TOKEN` de la cuenta de HubSpot
- [ ] Crear las 8 propiedades custom en HubSpot (ver tabla arriba)
- [ ] Probar sync real con `php artisan mock:sync-editors`
- [ ] Verificar que los contactos llegan correctamente al panel de HubSpot
- [ ] Marcar `codeditorbmg` como unique identifier en HubSpot

### Pendiente de acceso a BMG
- [ ] Revisar estructura real de la tabla `editores` de BMG
- [ ] Ajustar mapeo de campos en `Editor.php` (constructor)
- [ ] Adaptar `SyncAllFilialesCommand` para recorrer la tabla de editores de BMG directamente
- [ ] Confirmar nombres de campos numéricos (`num_titulos`, etc.) en BMG
- [ ] Integrar el código en el proyecto BMG (ver sección anterior)
- [ ] Configurar el scheduler en el servidor de BMG
- [ ] Probar en staging de BMG antes de producción

### Mejoras opcionales futuras
- [ ] Añadir logging detallado de cada sync (qué se creó, qué se actualizó, qué falló)
- [ ] Dashboard o comando de estadísticas (`sync:status`)
- [ ] Reintentos automáticos para editores que fallaron
- [ ] Notificación por email si el sync falla completamente
- [ ] Usar `codeditorbmg` como identificador único en HubSpot en vez del email (una vez configurado como unique en HubSpot)

---

## Email marketing

El email marketing es **100% interno de HubSpot** y no requiere desarrollo adicional. El equipo comercial puede:

1. **Segmentar contactos** por cualquier propiedad sincronizada desde BMG (filial, tipo_editor, num_titulos, total_ventas_eur...)
2. **Crear campañas de email** con el editor visual de HubSpot (Marketing → Email)
3. **Crear formularios** en HubSpot (Marketing → Forms) y enlazarlos desde los emails
4. **Automatizar workflows** (si un editor tiene más de X títulos, enviarle tal email automáticamente)

### Formularios de interés (Venta Directa, etc.)
HubSpot genera un snippet de JavaScript que se puede embeds en cualquier página de BMG. El editor rellena el formulario, HubSpot lo recibe y actualiza automáticamente el contacto. **No requiere código backend.**

### ¿Cuándo sí requeriría código?
Solo si BMG tuviera formularios propios que se quisieran mantener y además registrar en HubSpot. En ese caso se añadiría un método `submitForm()` al `HubspotClient` que haría un POST a la Forms API de HubSpot. Por ahora no es necesario.
