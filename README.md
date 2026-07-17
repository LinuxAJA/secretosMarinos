# Secretos Marinos

Plataforma web de **alfabetización oceánica** y **acción ambiental** orientada a un entorno formativo SENA. Integra educación marina, fichas de especies y ecosistemas, campañas comunitarias, reportes ambientales y gamificación básica, todo en un stack local sin frameworks ni nube.

> **Estado actual:** Paso 1 completado — cimientos del proyecto (MVC, front controller, UI base, esquema MySQL y seed).  
> **Siguiente:** Paso 2 — autenticación, registro, sesiones y roles.

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
| Patrones | MVC, Singleton (PDO), Repository (próximos pasos) |

**No usa:** Laravel, React, Vue, Node, ni servicios en la nube.

---

## 3. Alcance de la versión 1.0

### Incluido en el roadmap V1.0

| Módulo | Descripción |
|--------|-------------|
| Autenticación y roles | Registro, login, logout, `admin` / `docente` / `estudiante` |
| Biblioteca educativa | Contenidos y categorías (CRUD) |
| Especies marinas | Fichas científicas (CRUD + filtros) |
| Ecosistemas | Arrecifes, manglares, etc. |
| Noticias | Publicación y destacados |
| Campañas ambientales | Objetivos, fechas, estado |
| Reportes ambientales | Evidencia, ubicación, estados |
| Panel admin básico | Gestión esencial |
| Gamificación mínima | Puntos e insignias iniciales |
| Estadísticas básicas | KPIs simples |

### Fuera de V1.0 (V1.1+)

Foros, certificados digitales, mapas interactivos, multimedia avanzada, simulaciones / mini-juegos, ranking sofisticado, eventos con asistencia.

---

## 4. Arquitectura

```
secretosMarinos/
├── app/
│   ├── controllers/     # Reciben petición y orquestan respuesta
│   ├── core/            # Database, Router, Controller, Autoload
│   ├── helpers/         # url(), e(), csrf, flash, sesión
│   ├── middlewares/     # (Paso 2+) control de acceso
│   ├── models/          # (próximos pasos)
│   ├── repositories/    # (próximos pasos) acceso a datos
│   └── services/        # (próximos pasos) reglas de negocio
├── config/              # constants, database, app, routes
├── views/
│   ├── layouts/         # Layout HTML principal
│   ├── partials/        # Header, footer
│   ├── pages/           # Vistas públicas
│   └── admin/           # (próximos pasos)
├── public/              # Único punto de entrada (index.php)
├── assets/              # CSS, JS, imágenes estáticas
├── uploads/             # Archivos subidos en runtime
├── database/            # schema.sql, seed.sql
├── docs/                # Documentación de proyecto
└── logs/                # Errores / auditoría local
```

### Flujo de una petición

```
Navegador
  → .htaccess (raíz / public)
  → public/index.php          (front controller)
  → config + Autoload + helpers + sesión
  → Router + config/routes.php
  → Controller::método()
  → Vista dentro de layouts/main.php
```

---

## 5. Estado del desarrollo — Paso 1 (completado)

### Qué incluye el Paso 1

- Estructura MVC y carpetas del proyecto  
- Constantes, configuración de app y de base de datos  
- Autoload PSR-4 simplificado (`App\...`)  
- `Database` (Singleton + PDO)  
- `Router` con rutas GET/POST y parámetros `{id}`  
- `Controller` base (`render`, `redirect`, `json`)  
- Helpers (`url`, `asset`, `e`, CSRF, flash, sesión)  
- Front controller `public/index.php` + reescritura Apache  
- Layout, header, footer, página de inicio (hero oceánico)  
- CSS con paleta institucional y JS de menú móvil  
- `database/schema.sql` y `database/seed.sql`  
- Protección `.htaccess` en carpetas sensibles  

### Qué aún no incluye (llega en Paso 2+)

- Login / registro / logout  
- Middleware de roles  
- CRUD de módulos de negocio  
- Subida de archivos con validación completa  

### Cómo probar el Paso 1

1. Activa **Apache** y **MySQL** en XAMPP.  
2. Importa (si aún no lo hiciste):
   - `database/schema.sql`
   - `database/seed.sql`  
   (phpMyAdmin o CLI `mysql`).  
3. Abre: [http://localhost/secretosMarinos/public/](http://localhost/secretosMarinos/public/)  
4. Verifica la BD `secretos_marinos` en phpMyAdmin.

### Usuarios demo (seed)

| Correo | Rol | Contraseña |
|--------|-----|------------|
| `admin@secretosmarinos.local` | admin | `Password123!` |
| `docente@secretosmarinos.local` | docente | `Password123!` |
| `estudiante@secretosmarinos.local` | estudiante | `Password123!` |

> El login funcional se implementa en el **Paso 2**. Los hashes del seed ya están preparados.

---

## 6. Base de datos (V1.0)

Tablas principales creadas en `schema.sql`:

`roles`, `usuarios`, `categorias_contenido`, `contenidos`, `ecosistemas`, `especies`, `noticias`, `campanias`, `reportes_ambientales`, `insignias`, `usuario_insignia`, `puntos_usuario`, `auditoria`

Credenciales locales por defecto (XAMPP) en `config/database.php`:

- Host: `127.0.0.1`
- Usuario: `root`
- Contraseña: *(vacía)*
- BD: `secretos_marinos`

---

## 7. Paleta y UI

| Token | HEX | Uso |
|-------|-----|-----|
| Azul profundo | `#083D77` | Marca, botones primarios |
| Azul océano | `#0B6E99` | Acentos / hover |
| Turquesa | `#2EC4B6` | Vida / innovación |
| Arena clara | `#F4EDE4` | Fondos suaves |
| Texto | `#334155` | Lectura |

Tipografías: **Fraunces** (títulos) + **Source Sans 3** (cuerpo).

---

## 8. Roadmap de implementación

| Paso | Nombre | Estado |
|------|--------|--------|
| 1 | Cimientos (MVC, UI, schema, home) | Completado |
| 2 | Autenticación y roles | Pendiente |
| 3 | Educativo + noticias | Pendiente |
| 4 | Especies y ecosistemas | Pendiente |
| 5 | Campañas y reportes | Pendiente |
| 6 | Gamificación mínima | Pendiente |
| 7 | Admin y estadísticas básicas | Pendiente |
| 8 | Hardening, pruebas y entrega | Pendiente |

---

## 9. Seguridad (base ya prevista)

- Front controller único; carpetas `app/`, `config/`, `database/`, `logs/` denegadas por Apache  
- Uploads sin ejecución de PHP  
- Helpers CSRF y escape HTML (`e()`) listos para formularios  
- Contraseñas con `password_hash` / `password_verify` (Paso 2)  
- Consultas preparadas PDO (a partir de repositorios)  

---

## 10. Git / GitHub — cómo trabajamos este repo

### Ramas

| Rama | Uso |
|------|-----|
| `main` | Código estable. Solo recibe merges revisados. |
| `develop` | Integración del trabajo en curso (opcional pero recomendada). |
| `feature/...` | Una funcionalidad por rama (ej. `feature/auth-paso-2`). |

**Flujo profesional recomendado para este proyecto SENA:**

1. `main` = entrega estable (Paso 1, Paso 2 terminado, etc.).  
2. Para cada paso/módulo: crear `feature/paso-2-auth` desde `main` (o desde `develop`).  
3. Commits pequeños y claros en la feature.  
4. Merge a `main` (o PR) cuando el paso esté completo y probado.  
5. Evitar commits directos grandes en `main` sin revisión.

### Mensajes de commit (estilo)

```
feat: add authentication with roles and CSRF
fix: correct public .htaccess rewrite for assets
docs: document Paso 1 foundation and setup
chore: add gitignore for uploads and logs
```

Prefijos útiles: `feat`, `fix`, `docs`, `refactor`, `chore`, `test`, `style`.

---

## 11. Requisitos e instalación rápida

1. XAMPP con PHP 8+ y MySQL/MariaDB  
2. Clonar este repositorio en `C:\xampp\htdocs\secretosMarinos`  
3. Importar `database/schema.sql` y `database/seed.sql`  
4. Ajustar `config/database.php` si tu MySQL tiene contraseña  
5. Abrir `http://localhost/secretosMarinos/public/`  

---

## 12. Licencia y contexto académico

Proyecto formativo orientado a evidencia de competencias en desarrollo web (HTML, CSS, JS, PHP, MySQL) bajo arquitectura MVC y buenas prácticas de seguridad básica.

---

**Secretos Marinos** — alfabetización oceánica · acción ambiental · formación SENA
