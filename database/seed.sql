-- ============================================================================
-- seed.sql — Datos iniciales de demostración (V1.0)
-- ============================================================================
-- Ejecutar DESPUÉS de schema.sql
-- Las contraseñas de demo usan password_hash de PHP con bcrypt.
-- Clave en texto plano para TODOS los usuarios demo: Password123!
-- Hash generado con: password_hash('Password123!', PASSWORD_DEFAULT)
-- ============================================================================

USE secretos_marinos;

-- Roles
INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'admin', 'Administrador del sistema'),
(2, 'docente', 'Formador / docente SENA'),
(3, 'estudiante', 'Estudiante o ciudadanía participante');

-- Usuarios demo (contraseña de todos: Password123!)
-- Hash generado con password_hash('Password123!', PASSWORD_DEFAULT)
INSERT INTO usuarios (id, rol_id, nombre, correo, password_hash, puntos, activo) VALUES
(1, 1, 'Admin Marino', 'admin@secretosmarinos.local',
 '$2y$10$3FAOKUdmqcn9wpZ3o97QNOoGqM57yamUeGqNyT.4X2urbgwTaOkR6', 0, 1),
(2, 2, 'Docente Coral', 'docente@secretosmarinos.local',
 '$2y$10$3FAOKUdmqcn9wpZ3o97QNOoGqM57yamUeGqNyT.4X2urbgwTaOkR6', 50, 1),
(3, 3, 'Estudiante Marea', 'estudiante@secretosmarinos.local',
 '$2y$10$3FAOKUdmqcn9wpZ3o97QNOoGqM57yamUeGqNyT.4X2urbgwTaOkR6', 20, 1);

-- Categorías educativas
INSERT INTO categorias_contenido (id, nombre, slug, descripcion) VALUES
(1, 'Oceanografía básica', 'oceanografia-basica', 'Conceptos fundamentales del océano'),
(2, 'Biodiversidad marina', 'biodiversidad-marina', 'Fauna, flora y cadenas tróficas'),
(3, 'Conservación', 'conservacion', 'Amenazas y buenas prácticas');

-- Contenidos demo
INSERT INTO contenidos (categoria_id, autor_id, titulo, slug, resumen, cuerpo, nivel, publicado) VALUES
(1, 2, '¿Qué es la alfabetización oceánica?', 'que-es-alfabetizacion-oceanica',
 'Principios para comprender la influencia del océano en nuestras vidas.',
 'La alfabetización oceánica propone comprender la influencia mutua entre el océano y las personas, y actuar de forma responsable. Este contenido introduce los principios clave adaptados al contexto formativo SENA.',
 'basico', 1),
(3, 2, 'Amenazas a los manglares', 'amenazas-a-los-manglares',
 'Cómo la contaminación y la urbanización afectan manglares costeros.',
 'Los manglares protegen costas, filtran agua y son criaderos de especies. Las amenazas incluyen tala, rellenos y residuos. La participación comunitaria es clave para su cuidado.',
 'intermedio', 1);

-- Ecosistemas
INSERT INTO ecosistemas (id, nombre, slug, descripcion, funcion_ecologica, amenazas, buenas_practicas, publicado) VALUES
(1, 'Arrecife de coral', 'arrecife-de-coral',
 'Ecosistema costero formado por corales y biodiversidad asociada.',
 'Protege costas, alberga peces y sostiene pesca artesanal.',
 'Blanqueamiento, turismo irresponsable, contaminación.',
 'No tocar corales, reducir plásticos, apoyar áreas protegidas.', 1),
(2, 'Manglar', 'manglar',
 'Bosque costero adaptado a agua salobre.',
 'Nurseries de peces, captura de carbono, barrera natural.',
 'Tala, rellenos, vertimientos.',
 'Restaurar, no rellenar, gestionar residuos.', 1);

-- Especies
INSERT INTO especies (ecosistema_id, autor_id, nombre_comun, nombre_cientifico, slug, clasificacion, habitat, distribucion, amenazas, estado_conservacion, descripcion, publicado) VALUES
(1, 2, 'Pez loro', 'Scaridae', 'pez-loro',
 'Familia Scaridae', 'Arrecifes coralinos', 'Mares tropicales',
 'Sobrepesca y degradación de arrecifes', 'Preocupación menor',
 'Herbívoro clave que ayuda a controlar algas en arrecifes.', 1),
(2, 2, 'Cangrejo violinista', 'Uca sp.', 'cangrejo-violinista',
 'Género Uca', 'Lodos y manglares', 'Costas tropicales y subtropicales',
 'Pérdida de hábitat', 'No evaluado',
 'Indicador de salud en zonas intermareales de manglar.', 1);

-- Noticias
INSERT INTO noticias (autor_id, titulo, slug, resumen, cuerpo, categoria, destacada, publicada, publicado_en) VALUES
(1, 'Lanzamiento de Secretos Marinos', 'lanzamiento-secretos-marinos',
 'Nace una plataforma local de alfabetización oceánica.',
 'Secretos Marinos integra educación, especies, campañas y reportes ambientales en un entorno XAMPP para formación SENA.',
 'institucional', 1, 1, NOW());

-- Campañas
INSERT INTO campanias (responsable_id, titulo, slug, descripcion, objetivo, fecha_inicio, fecha_fin, estado) VALUES
(2, 'Guardianes del manglar', 'guardianes-del-manglar',
 'Campaña escolar de limpieza y sensibilización en zonas de manglar.',
 'Movilizar 100 participantes y recolectar evidencias fotográficas.',
 CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'activa');

-- Reportes
INSERT INTO reportes_ambientales (usuario_id, titulo, descripcion, ubicacion, tipo, estado) VALUES
(3, 'Residuos en playa cercana',
 'Se observaron plásticos y redes abandonadas en la orilla tras la marea alta.',
 'Playa sector norte', 'residuos', 'pendiente');

-- Insignias
INSERT INTO insignias (codigo, nombre, descripcion, icono, puntos_requeridos) VALUES
('explorador_marino', 'Explorador Marino', 'Completó primeros contenidos educativos.', 'explorador', 20),
('guardian_manglar', 'Guardián del Manglar', 'Participó en campañas de manglar.', 'manglar', 50),
('defensor_arrecife', 'Defensor del Arrecife', 'Reportó o apoyó acciones de arrecife.', 'arrecife', 80);
