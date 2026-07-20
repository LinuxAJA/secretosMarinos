# Secretos Marinos

Plataforma web de **alfabetización oceánica** y **acción ambiental** orientada a un entorno formativo SENA. Integra educación marina, fichas de especies y ecosistemas, campañas comunitarias, reportes ambientales y gamificación básica, todo en un stack local sin frameworks ni nube.

> **Estado actual:** Paso 3 completado — biblioteca educativa, noticias, panel admin y reglas RBAC (admin / docente / estudiante).  
> **Siguiente:** Paso 4 — especies marinas y ecosistemas.

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
| Biblioteca educativa | Contenidos y categorías (CRUD + RBAC) | Hecho (Paso 3) |
| Noticias | Publicación, destacados y CRUD admin | Hecho (Paso 3) |
| Panel admin básico | Dashboard + gestión de contenidos/noticias/categorías | Hecho (Paso 3) |
| Especies marinas | Fichas científicas (CRUD + filtros) | Pendiente (Paso 4) |
| Ecosistemas | Arrecifes, manglares, etc. | Pendiente (Paso 4) |
| Campañas ambientales | Objetivos, fechas, estado | Pendiente (Paso 5) |
| Reportes ambientales | Evidencia, ubicación, estados | Pendiente (Paso 5) |
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
│   │   └── admin/         # Dashboard, Content, Category, News (Paso 3)
│   ├── core/              # Database, Router, Controller, Autoload
│   ├── helpers/           # url(), e(), csrf, flash, políticas RBAC
│   ├── middlewares/       # AuthMiddleware (roles / sesión)
│   ├── models/            # (reserva)
│   ├── repositories/      # Acceso SQL (PDO preparado)
│   └── services/          # Reglas de negocio / validación
├── config/                # constants, database, app, routes
├── views/
│   ├── layouts/           # main (público) + admin
│   ├── partials/          # header, footer, admin-sidebar
│   ├── pages/             # home, auth, educacion, noticias, panel
│   └── admin/             # dashboard, contenidos, categorias, noticias
├── public/                # Único punto de entrada (index.php)
├── assets/                # CSS, JS, imágenes estáticas
├── uploads/               # Archivos subidos en runtime
├── database/              # schema.sql, seed.sql
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

## 5. Roles y permisos (acumulado Pasos 2–3)

| Capacidad | Admin | Docente | Estudiante |
|-----------|-------|---------|------------|
| Registro público / login | Sí* | Sí* | Sí |
| Panel personal `/panel` | Sí | Sí | Sí |
| Ver biblioteca y noticias públicas | Sí | Sí | Sí |
| Acceso a `/admin` | Sí | Sí | No |
| CRUD contenidos / noticias **propios** | Sí | Sí | No |
| Editar / eliminar contenidos o noticias **de otros** | Sí | No | No |
| Ver listado de categorías | Sí | Sí (solo lectura) | No |
| Crear / editar / eliminar categorías | Sí | No | No |

\*Las cuentas admin/docente del entorno demo vienen del `seed.sql`. El registro público crea rol **estudiante**.

Políticas en código: `is_admin()`, `can_manage_content()`, `can_manage_news()`, `can_manage_categories()` en `app/helpers/helpers.php`. La autorización se valida en servidor (controllers), no solo en la UI.

---

## 6. Roadmap de implementación

| Paso | Nombre | Estado |
|------|--------|--------|
| 1 | Cimientos (MVC, UI, schema, home) | Completado |
| 2 | Autenticación y roles | Completado |
| 3 | Educativo + noticias + RBAC admin | Completado |
| 4 | Especies y ecosistemas | Pendiente |
| 5 | Campañas y reportes | Pendiente |
| 6 | Gamificación mínima | Pendiente |
| 7 | Admin ampliado y estadísticas básicas | Pendiente |
| 8 | Hardening, pruebas y entrega | Pendiente |

---

## 7. Guía acumulada por pasos

Cada paso conserva su alcance y su forma de prueba. Al final de esta sección hay una **checklist completa** del sistema hasta el Paso 3.

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

---

## 8. Checklist de prueba completa (hasta Paso 3)

Usa esta lista como guía de verificación integral del sistema actual:

- [ ] Apache + MySQL activos; BD `secretos_marinos` importada (`schema` + `seed`)  
- [ ] Home pública carga con estilos: `/public/`  
- [ ] Registro crea estudiante y deja sesión iniciada  
- [ ] Login / logout funcionan (demo o cuenta nueva)  
- [ ] `/panel` exige autenticación  
- [ ] `/educacion` lista, filtra y muestra fichas  
- [ ] Búsqueda por texto en educación no lanza error PDO  
- [ ] `/noticias` lista, muestra destacadas y fichas  
- [ ] Admin (rol admin): CRUD total contenidos, categorías y noticias  
- [ ] Docente: solo muta lo propio; categorías solo lectura  
- [ ] Estudiante: no entra al panel admin  
- [ ] Formularios admin/auth llevan token CSRF  

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
- Regeneración de ID de sesión al login  
- Límite básico de intentos fallidos de login  

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
6. Seguir la **checklist de la sección 8** para validar Pasos 1–3  

---

## 14. Licencia y contexto académico

Proyecto formativo orientado a evidencia de competencias en desarrollo web (HTML, CSS, JS, PHP, MySQL) bajo arquitectura MVC y buenas prácticas de seguridad básica.

---

**Secretos Marinos** — alfabetización oceánica · acción ambiental · formación SENA
