# Secretos Marinos

Plataforma web de **alfabetización oceánica** y **acción ambiental** orientada a un entorno formativo SENA. Integra educación marina, fichas de especies y ecosistemas, campañas comunitarias, reportes ambientales y gamificación básica, todo en un stack local sin frameworks ni nube.

> **Estado actual:** Paso 5 completado — campañas ambientales, reportes ciudadanos y motivo obligatorio al cancelar.
> **Siguiente:** Paso 6 — gamificación mínima (puntos e insignias).

---

## 1. Visión del producto

**Secretos Marinos** responde a la falta de una plataforma local, clara y participativa que una:

- educación marina y divulgación científica;
- participación ciudadana (campañas y reportes);
- gamificación para retención y aprendizaje;
- administración de contenidos, usuarios y métricas básicas.

Está inspirada en modelos institucionales de educación oceánica (UNESCO Ocean Literacy, Blue School Global Network, NOAA Ocean Guardian, SeaLifeBase), adaptados a un despliegue **XAMPP local**.

### Público objetivo

- Estudiantes de básica, media y formación técnica  
- Docentes y formadores SENA  
- Instituciones educativas y comunidad general  
- Semilleros / investigadores y aliados ambientales  

### Objetivo general

Desarrollar una plataforma web dinámica para informar, educar, concientizar y promover el cuidado ambiental marino mediante contenidos, interacción, gamificación y participación comunitaria.

---

## 2. Stack tecnológico (V1.0)

| Capa | Tecnología |
|------|------------|
| Frontend | HTML5, CSS3, JavaScript Vanilla |
| Backend | PHP 8+ (MVC propio, sin frameworks) |
| Base de datos | MySQL / MariaDB |
| Entorno | XAMPP (Apache + MySQL + phpMyAdmin) |
| Patrones | MVC, Singleton (PDO), Repository, Service, Middleware |

**No usa:** Laravel, React, Vue, Node, ni servicios en la nube.

---

## 3. Alcance de la versión 1.0

### Incluido en el roadmap V1.0

| Módulo | Descripción | Estado |
|--------|-------------|--------|
| Autenticación y roles | Registro, login, logout, `admin` / `docente` / `estudiante` | Hecho (Paso 2–3) |
| Perfil de usuario | Editar/eliminar cuenta propia (nombre, correo, contraseña) | Hecho (complemento auth) |
| Biblioteca educativa | Contenidos y categorías (CRUD + RBAC) | Hecho (Paso 3) |
| Noticias | Publicación, destacados y CRUD admin | Hecho (Paso 3) |
| Panel admin básico | Dashboard + gestión de contenidos/noticias/categorías/especies/ecosistemas | Hecho (Pasos 3–4) |
| Especies marinas | Fichas científicas, filtros, imágenes y CRUD con autoría | Hecho (Paso 4) |
| Ecosistemas | Fichas, especies relacionadas, imágenes y CRUD admin | Hecho (Paso 4) |
| Campañas ambientales | Objetivos, fechas, estados y cancelación justificada | Hecho (Paso 5) |
| Reportes ambientales | Evidencia ciudadana, cola de revisión y seguimiento | Hecho (Paso 5) |
| Gamificación mínima | Puntos e insignias iniciales | Pendiente (Paso 6) |
| Estadísticas básicas | KPIs simples | Pendiente (Paso 7) |

### Fuera de V1.0 (V1.1+)

Foros, certificados digitales, mapas interactivos, multimedia avanzada, simulaciones / mini-juegos, ranking sofisticado, eventos con asistencia.

---

## 4. Arquitectura

```
secretosMarinos/
├── app/
│   ├── controllers/       # Home, Auth, Education, News, Panel…
│   │   └── admin/         # CRUD de contenidos, noticias, especies y ecosistemas
│   ├── core/              # Database, Router, Controller, Autoload
│   ├── helpers/           # url(), e(), csrf, flash, políticas RBAC
│   ├── middlewares/       # AuthMiddleware (roles / sesión)
│   ├── models/            # (reserva)
│   ├── repositories/      # Acceso SQL (PDO preparado)
│   └── services/          # Reglas de negocio, validación y carga de imágenes
├── config/                # constants, database, app, routes
├── views/
│   ├── layouts/           # main (público) + admin
│   ├── partials/          # header, footer, admin-sidebar
│   ├── pages/             # home, auth, educación, noticias, especies, ecosistemas
│   └── admin/             # CRUD de módulos administrativos
├── public/                # Único punto de entrada (index.php)
├── assets/                # CSS, JS, imágenes estáticas
├── uploads/               # Imágenes runtime (no versionadas)
├── database/              # schema.sql, seed.sql y migrations/
├── docs/                  # Documentación de proyecto
└── logs/                  # Errores / auditoría local
```

### Flujo de una petición

```
Navegador
  → .htaccess (raíz / public)
  → public/index.php          (front controller)
  → config + Autoload + helpers + sesión
  → Router + config/routes.php
  → Controller::método()
  → (opcional) Middleware de auth/roles + Service + Repository
  → Vista dentro de layouts/main.php o layouts/admin.php
```

---

## 5. Roles y permisos (acumulado Pasos 2–5)

| Capacidad | Admin | Docente | Estudiante |
|-----------|-------|---------|------------|
| Registro público / login | Sí* | Sí* | Sí |
| Panel personal `/panel` | Sí | Sí | Sí |
| Editar propio nombre / correo / contraseña | Sí | Sí | Sí |
| Eliminar la propia cuenta | Sí** | Sí | Sí |
| Cambiar el propio rol | No | No | No |
| Ver biblioteca y noticias públicas | Sí | Sí | Sí |
| Acceso a `/admin` | Sí | Sí | No |
| CRUD contenidos / noticias **propios** | Sí | Sí | No |
| Editar / eliminar contenidos o noticias **de otros** | Sí | No | No |
| Ver listado de categorías | Sí | Sí (solo lectura) | No |
| Crear / editar / eliminar categorías | Sí | No | No |
| Ver especies y ecosistemas públicos | Sí | Sí | Sí |
| CRUD de especies **propias** | Sí | Sí | No |
| Editar / eliminar especies de otros | Sí | No | No |
| Ver ecosistemas en administración | Sí | Sí (solo lectura) | No |
| Crear / editar / eliminar ecosistemas | Sí | No | No |
| Ver campañas públicas (activa/finalizada) | Sí | Sí | Sí |
| CRUD de campañas **propias** (`responsable_id`) | Sí | Sí | No |
| Editar / eliminar campañas de otros | Sí | No | No |
| Cancelar campaña (exige motivo ≥ 15 caracteres) | Sí*** | Sí*** | No |
| Ver reportes **resueltos** en público | Sí | Sí | Sí |
| Crear reporte ambiental | Sí | Sí | Sí**** |
| Editar / eliminar **propio** reporte pendiente | Sí | Sí | Sí |
| Revisar reportes (estado + notas) | Sí | Sí | No |
| Eliminar cualquier reporte | Sí | No | No |

\*Las cuentas admin/docente del entorno demo vienen del `seed.sql`. El registro público crea rol **estudiante**.  
\*\*Un administrador **no** puede eliminarse si es el único admin activo.  
\*\*\*Solo sobre campañas que el rol pueda gestionar.  
\*\*\*\*Requiere sesión iniciada (registro/login).

Políticas en código: las de Pasos 3–4 más `can_manage_campaigns()`, `can_create_report()`, `can_view_report()`, `can_edit_own_report()`, `can_review_reports()` y `can_delete_any_report()` en `app/helpers/helpers.php`. La autorización se valida en servidor (controllers), no solo en la UI.

---

## 6. Roadmap de implementación

| Paso / entrega | Nombre | Estado |
|----------------|--------|--------|
| 1 | Cimientos (MVC, UI, schema, home) | Completado |
| 2 | Autenticación y roles | Completado |
| 3 | Educativo + noticias + RBAC admin | Completado |
| — | Perfil de usuario (complemento de auth/panel) | Completado |
| 4 | Especies y ecosistemas | Completado |
| 5 | Campañas y reportes | Completado |
| 6 | Gamificación mínima | Pendiente |
| 7 | Admin ampliado y estadísticas básicas | Pendiente |
| 8 | Hardening, pruebas y entrega | Pendiente |

---

## 7. Guía acumulada por pasos

Cada paso conserva su alcance y su forma de prueba. Al final de esta sección hay una **checklist completa** del sistema hasta el Paso 5 + perfil.

### 7.1 Paso 1 — Cimientos (completado)

#### Qué incluye

- Estructura MVC y carpetas del proyecto  
- Constantes, configuración de app y de base de datos  
- Autoload PSR-4 simplificado (`App\...`)  
- `Database` (Singleton + PDO)  
- `Router` con rutas GET/POST y parámetros dinámicos  
- `Controller` base (`render`, `redirect`, `json`)  
- Helpers (`url`, `asset`, `e`, CSRF, flash, sesión)  
- Front controller `public/index.php` + reescritura Apache  
- Layout, header, footer, página de inicio (hero oceánico)  
- CSS con paleta institucional y JS de menú móvil  
- `database/schema.sql` y `database/seed.sql`  
- Protección `.htaccess` en carpetas sensibles  

#### Cómo probar el Paso 1

1. Activa **Apache** y **MySQL** en XAMPP.  
2. Importa (si aún no lo hiciste):
   - `database/schema.sql`
   - `database/seed.sql`  
   (phpMyAdmin o CLI `mysql`).  
3. Abre: [http://localhost/secretosMarinos/public/](http://localhost/secretosMarinos/public/)  
4. Verifica la BD `secretos_marinos` en phpMyAdmin.  
5. Confirma que cargan CSS/JS y que el menú móvil funciona.

### 7.2 Paso 2 — Autenticación y roles (completado)

#### Qué incluye

- Registro, login y logout  
- Sesión segura con regeneración de ID al autenticarse  
- CSRF en formularios POST  
- Hash de contraseñas (`password_hash` / `password_verify`)  
- Límite básico de intentos de login  
- `AuthService`, `UserRepository`, `AuthMiddleware`  
- Panel privado `/panel`  
- Logout por POST desde el header  

#### Cómo probar el Paso 2

1. [http://localhost/secretosMarinos/public/registro](http://localhost/secretosMarinos/public/registro) — crea un usuario (rol estudiante).  
2. [http://localhost/secretosMarinos/public/login](http://localhost/secretosMarinos/public/login) — inicia sesión.  
3. Accede a [http://localhost/secretosMarinos/public/panel](http://localhost/secretosMarinos/public/panel).  
4. Sin sesión, `/panel` debe redirigir a login.  
5. Cierra sesión con **Salir** (POST + CSRF).  
6. Prueba usuarios demo del seed (tabla más abajo).

### 7.3 Paso 3 — Educación, noticias y RBAC (completado)

#### Qué incluye

**Público**

- Biblioteca educativa: listado, filtros (categoría / búsqueda), ficha, contador de visitas  
- Noticias: listado, destacadas, filtros, ficha  
- Fix de búsqueda PDO (parámetros `LIKE` únicos con prepares nativos)  

**Administración** (`/admin`, roles admin y docente)

- Dashboard con conteos  
- CRUD de contenidos educativos  
- CRUD de noticias  
- Categorías: CRUD solo **admin**; docente solo lectura  
- RBAC por autoría: el docente edita/elimina solo lo suyo (`autor_id`)  
- Layout admin + sidebar  

**Capas**

- Repositories: `ContentRepository`, `ContentCategoryRepository`, `NewsRepository`  
- Services: `ContentService`, `CategoryService`, `NewsService`  
- Controllers públicos y `app/controllers/admin/*`  

#### Cómo probar el Paso 3

**Público**

1. [http://localhost/secretosMarinos/public/educacion](http://localhost/secretosMarinos/public/educacion)  
2. Filtra por texto (ej. `amenazas`) y por categoría.  
3. Abre un contenido y verifica que suben las visitas.  
4. [http://localhost/secretosMarinos/public/noticias](http://localhost/secretosMarinos/public/noticias)  
5. Abre una noticia destacada / publicada.

**Admin (login como admin)**

1. [http://localhost/secretosMarinos/public/admin](http://localhost/secretosMarinos/public/admin)  
2. Crear / editar / eliminar contenidos, categorías y noticias.  
3. Publicar un ítem y verlo en el sitio público.

**Docente (RBAC)**

1. Login como `docente@secretosmarinos.local`.  
2. Puede crear contenidos/noticias; editar/borrar solo los de su autoría.  
3. Ítems de otro autor aparecen como “Solo lectura”; URL de edición ajena → rechazo.  
4. Categorías: sin botones de mutación; `/admin/categorias/crear` → rechazo.

**Estudiante**

1. Login como estudiante → sin acceso a `/admin` (redirige / sin permiso).

### 7.4 Perfil de usuario — complemento de auth (completado)

> **Contexto:** el Paso 2 dejó `/panel` como vista informativa. Este entregable **no es un “Paso 3.5”**: es un feature aparte (`feature/perfil-usuario`) que completa la autogestión de cuenta sin reabrir el PR de autenticación ni mezclarse con especies (Paso 4).

#### Qué incluye

- Edición de **nombre** y **correo** en `/panel`  
- Cambio de **contraseña** (exige contraseña actual + confirmación)  
- **Eliminar cuenta** (hard delete) con contraseña + checkbox de confirmación  
- Bloqueo si intenta borrarse el **único admin activo**  
- Correo único (no puede chocar con otra cuenta)  
- El **rol no se puede cambiar** desde el perfil  
- Tras borrar: logout + redirect a inicio; contenidos/noticias del autor quedan con `autor_id = NULL` (FK)  
- `ProfileService` + métodos en `UserRepository`  
- CSRF en todos los formularios del panel; regeneración de sesión al cambiar contraseña  

#### Cómo probar el perfil

1. Inicia sesión (cualquier rol) y abre [http://localhost/secretosMarinos/public/panel](http://localhost/secretosMarinos/public/panel).  
2. En **Mis datos**, cambia el nombre y guarda → debe verse en el saludo y en el header.  
3. Intenta un correo ya usado por otra cuenta → error de validación.  
4. En **Cambiar contraseña**, usa la actual incorrecta → rechazo.  
5. Cambia la contraseña correctamente → mensaje de éxito; cierra sesión y entra con la nueva.  
6. Verifica que el campo Rol está deshabilitado / no editable.  
7. Con un usuario de prueba (no el único admin), en **Eliminar cuenta**: sin checkbox o con password mala → rechazo.  
8. Elimina una cuenta de prueba con confirmación correcta → sesión cerrada y redirect a inicio; no debe poder volver a entrar.  
9. Como único admin del seed, intenta eliminarte → debe bloquearse con mensaje de “única cuenta de administrador”.

### 7.5 Paso 4 — Especies y ecosistemas (completado)

#### Qué incluye

**Catálogo público**

- `/ecosistemas`: búsqueda, paginación y fichas de ecosistemas publicados
- Ficha de ecosistema con función ecológica, amenazas, buenas prácticas y especies asociadas
- `/especies`: búsqueda por nombre común/científico y filtros por ecosistema/conservación
- Ficha científica con taxonomía, hábitat, distribución, amenazas, conservación y autor
- Estados vacíos, diseño responsive e imágenes opcionales con texto alternativo

**Administración y RBAC**

- Admin: CRUD completo de ecosistemas y de cualquier especie
- Docente: ecosistemas de solo lectura; CRUD únicamente de sus especies (`autor_id`)
- Estudiante: catálogo público, sin acceso administrativo
- Ecosistemas eliminados dejan sus especies con `ecosistema_id = NULL` (`ON DELETE SET NULL`)

**Imágenes seguras**

- `ImageUploadService`: MIME real con `finfo`, máximo 5 MB y nombres aleatorios
- Formatos admitidos: JPG, PNG, WEBP y GIF
- Reemplazo/eliminación controlada de archivos y bloqueo de ejecución PHP en `uploads/`
- Rutas: `uploads/images/ecosistemas/` y `uploads/images/especies/`

**Base de datos**

- `especies.autor_id` con FK a usuarios (`ON DELETE SET NULL`)
- `ecosistemas.publicado` y `ecosistemas.actualizado_en`
- Migración incremental: `database/migrations/004_species_ecosystems.sql`
- `schema.sql` y `seed.sql` actualizados para instalaciones nuevas

#### Cómo probar el Paso 4

1. En una BD existente, ejecuta una sola vez `database/migrations/004_species_ecosystems.sql`.
2. Abre [http://localhost/secretosMarinos/public/ecosistemas](http://localhost/secretosMarinos/public/ecosistemas), busca `manglar` y entra a su ficha.
3. Comprueba que la ficha del manglar muestra sus especies asociadas.
4. Abre [http://localhost/secretosMarinos/public/especies](http://localhost/secretosMarinos/public/especies) y filtra por texto, ecosistema y conservación.
5. Como admin, prueba CRUD en `/admin/ecosistemas` y `/admin/especies`, incluyendo una imagen válida.
6. Intenta subir un archivo no imagen o mayor de 5 MB → debe rechazarse.
7. Edita sin subir imagen → conserva la actual; reemplázala o marca “Eliminar imagen”.
8. Como docente, crea una especie → puede editarla/borrarla; una especie ajena aparece como solo lectura.
9. Como docente, `/admin/ecosistemas/crear` debe rechazar la operación.
10. Como estudiante, las rutas `/admin/especies` y `/admin/ecosistemas` deben redirigir al panel.
11. Publica/despublica una especie o ecosistema y verifica su aparición en el catálogo público.

### 7.6 Paso 5 — Campañas y reportes (completado)

#### Qué incluye

**Campañas ambientales**

- `/campanias`: listado público de campañas `activa` y `finalizada`, con búsqueda y filtro
- Ficha `/campanias/{slug}` con objetivo, fechas, responsable e imagen
- Admin CRUD en `/admin/campanias`
- RBAC: admin global; docente solo campañas donde es `responsable_id`
- **Regla de cancelación:** al pasar a `cancelada` es obligatorio un `motivo_cancelacion` (≥ 15 caracteres); se guarda `cancelada_en` y el motivo permanece visible en administración (también como historial si se reactiva)

**Reportes ambientales**

- Público: solo reportes `resuelto` en `/reportes`
- Crear reporte: cualquier usuario autenticado (`/reportes/crear`)
- Autor: edita/elimina solo mientras esté `pendiente`
- Staff (admin/docente): cola `/admin/reportes`, cambio de estado + notas de revisión
- Solo admin elimina reportes ajenos
- Evidencia fotográfica opcional vía `ImageUploadService`

**Base de datos**

- `campanias.motivo_cancelacion`, `campanias.cancelada_en`, `campanias.actualizado_en`
- `reportes_ambientales.revisor_id`, `reportes_ambientales.notas_revision`
- Migración: `database/migrations/005_campaigns_reports.sql`

#### Cómo probar el Paso 5

1. Ejecuta una sola vez `database/migrations/005_campaigns_reports.sql` si la BD ya existía.
2. Abre `/campanias` y la ficha `guardianes-del-manglar`.
3. Como docente, crea una campaña, publícala como `activa` y verifica el catálogo.
4. Intenta cancelarla **sin** motivo → debe fallar la validación.
5. Cancélala **con** motivo ≥ 15 caracteres → aparece el motivo en `/admin/campanias`.
6. Como estudiante, crea un reporte en `/reportes/crear` → queda `pendiente` y aparece en “Mis reportes”.
7. Verifica que el público no ve reportes pendientes; solo resueltos.
8. Como docente, revisa el reporte (`en_revision` → `resuelto`) con nota.
9. Como autor, intenta editar un reporte ya en revisión → rechazo.
10. Como estudiante, `/admin/campanias` y `/admin/reportes` deben redirigir sin permiso.

---

## 8. Checklist de prueba completa (hasta Paso 5 + perfil)

Usa esta lista como guía de verificación integral del sistema actual:

- [ ] Apache + MySQL activos; BD `secretos_marinos` importada (`schema` + `seed`)  
- [ ] Home pública carga con estilos: `/public/`  
- [ ] Registro crea estudiante y deja sesión iniciada  
- [ ] Login / logout funcionan (demo o cuenta nueva)  
- [ ] `/panel` exige autenticación  
- [ ] En `/panel` se puede actualizar nombre y correo  
- [ ] Cambio de contraseña exige la actual y actualiza el login  
- [ ] El rol no es editable por el propio usuario  
- [ ] Se puede eliminar la propia cuenta con contraseña + confirmación  
- [ ] El único admin activo no puede autoeliminarse  
- [ ] `/educacion` lista, filtra y muestra fichas  
- [ ] Búsqueda por texto en educación no lanza error PDO  
- [ ] `/noticias` lista, muestra destacadas y fichas  
- [ ] Admin (rol admin): CRUD total contenidos, categorías y noticias  
- [ ] Docente: solo muta lo propio; categorías solo lectura  
- [ ] Estudiante: no entra al panel admin  
- [ ] Formularios admin/auth llevan token CSRF  
- [ ] `/ecosistemas` busca y muestra fichas con especies asociadas
- [ ] `/especies` busca y filtra por ecosistema y conservación
- [ ] Admin: CRUD total de ecosistemas y especies
- [ ] Docente: especies propias; ecosistemas solo lectura
- [ ] Estudiante: sin acceso a administración científica
- [ ] Imágenes válidas se cargan; formatos/tamaños inválidos se rechazan
- [ ] Borrar ecosistema conserva las especies con relación `NULL`
- [ ] `/campanias` lista activas/finalizadas y muestra fichas
- [ ] Cancelar campaña sin motivo falla; con motivo queda registrado en admin
- [ ] Docente solo muta campañas de su responsabilidad
- [ ] Usuario autenticado crea reporte; aparece en Mis reportes
- [ ] Público solo ve reportes resueltos
- [ ] Staff revisa estado + notas; autor no edita tras salir de pendiente
- [ ] Solo admin elimina reportes ajenos

### Usuarios demo (seed)

| Correo | Rol | Contraseña |
|--------|-----|------------|
| `admin@secretosmarinos.local` | admin | `Password123!` |
| `docente@secretosmarinos.local` | docente | `Password123!` |
| `estudiante@secretosmarinos.local` | estudiante | `Password123!` |

> Si el login demo falla tras reinstalar solo parte de la BD, vuelve a importar `seed.sql` o registra un usuario nuevo (el registro genera hash válido al momento).

---

## 9. Base de datos (V1.0)

Tablas principales creadas en `schema.sql`:

`roles`, `usuarios`, `categorias_contenido`, `contenidos`, `ecosistemas`, `especies`, `noticias`, `campanias`, `reportes_ambientales`, `insignias`, `usuario_insignia`, `puntos_usuario`, `auditoria`

Integridad referencial con Foreign Keys y políticas `ON DELETE` / `ON UPDATE` (ej. categoría→contenido `SET NULL`; rol→usuario `RESTRICT`; puentes N:M `CASCADE`).

Para actualizar una instalación anterior:

```powershell
# Paso 4 (si aún no se aplicó)
c:\xampp\mysql\bin\mysql.exe -u root secretos_marinos -e "SOURCE c:/xampp/htdocs/secretosMarinos/database/migrations/004_species_ecosystems.sql"

# Paso 5
c:\xampp\mysql\bin\mysql.exe -u root secretos_marinos -e "SOURCE c:/xampp/htdocs/secretosMarinos/database/migrations/005_campaigns_reports.sql"
```

Cada migración se ejecuta **una sola vez**. Una instalación desde cero usa directamente `schema.sql` + `seed.sql`.

Credenciales locales por defecto (XAMPP) en `config/database.php`:

- Host: `127.0.0.1`
- Usuario: `root`
- Contraseña: *(vacía)*
- BD: `secretos_marinos`

---

## 10. Paleta y UI

| Token | HEX | Uso |
|-------|-----|-----|
| Azul profundo | `#083D77` | Marca, botones primarios |
| Azul océano | `#0B6E99` | Acentos / hover |
| Turquesa | `#2EC4B6` | Vida / innovación |
| Arena clara | `#F4EDE4` | Fondos suaves |
| Texto | `#334155` | Lectura |

Tipografías: **Fraunces** (títulos) + **Source Sans 3** (cuerpo).

Layouts: público (`views/layouts/main.php`) y administración (`views/layouts/admin.php`).

---

## 11. Seguridad (acumulado)

- Front controller único; carpetas `app/`, `config/`, `database/`, `logs/` denegadas por Apache  
- Uploads sin ejecución de PHP  
- CSRF en formularios POST  
- Escape HTML en vistas (`e()`)  
- Contraseñas con `password_hash` / `password_verify`  
- Consultas preparadas PDO (`ATTR_EMULATE_PREPARES = false`)  
- Middleware de roles + políticas RBAC por autoría  
- Regeneración de ID de sesión al login y al cambiar contraseña  
- Límite básico de intentos fallidos de login  
- Autogestión de perfil limitada al propio usuario (sin escalada de rol)  
- Borrado de cuenta con confirmación; protección del último administrador  
- Carga de imágenes mediante MIME real (`finfo`), tamaño máximo y nombre aleatorio
- Imágenes almacenadas fuera del código versionado y sin ejecución PHP
- Cancelación de campañas con motivo obligatorio (trazabilidad en admin)
- Reportes: ownership del autor + revisión staff; pendientes no públicos

---

## 12. Git / GitHub — cómo trabajamos este repo

### Ramas

| Rama | Uso |
|------|-----|
| `main` | Código estable. Solo recibe merges revisados. |
| `develop` | Integración del trabajo en curso (opcional). |
| `feature/...` | Una funcionalidad/paso por rama (ej. `feature/paso-3-educacion-noticias`). |

**Flujo profesional recomendado:**

1. `main` = entrega estable por paso completado.  
2. Crear `feature/paso-N-...` desde `main`.  
3. Incluir en el mismo feature el código **y** la actualización del README de ese paso.  
4. Commit → push → **Pull Request** → merge a `main`.  
5. Evitar commits grandes directos a `main` sin revisión.

### Mensajes de commit (estilo)

```
feat: add authentication with roles and CSRF
fix: correct PDO named parameters in content search
docs: update README for Paso 3 education and news
chore: add gitignore for uploads and logs
```

Prefijos útiles: `feat`, `fix`, `docs`, `refactor`, `chore`, `test`, `style`.

---

## 13. Requisitos e instalación rápida

1. XAMPP con PHP 8+ y MySQL/MariaDB  
2. Clonar este repositorio en `C:\xampp\htdocs\secretosMarinos`  
3. Importar `database/schema.sql` y `database/seed.sql`  
4. Ajustar `config/database.php` si tu MySQL tiene contraseña  
5. Abrir `http://localhost/secretosMarinos/public/`  
6. Si la BD ya existía, aplicar migraciones pendientes (`004` y/o `005`) una sola vez cada una
7. Seguir la **checklist de la sección 8** para validar Pasos 1–5 y el perfil de usuario

---

## 14. Licencia y contexto académico

Proyecto formativo orientado a evidencia de competencias en desarrollo web (HTML, CSS, JS, PHP, MySQL) bajo arquitectura MVC y buenas prácticas de seguridad básica.

---

**Secretos Marinos** — alfabetización oceánica · acción ambiental · formación SENA
