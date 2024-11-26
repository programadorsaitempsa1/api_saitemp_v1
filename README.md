# laravel-jwt-auth

[Laravel 7|8 JWT Authentication Tutorial: User Login & Signup API](https://www.positronx.io/laravel-jwt-authentication-tutorial-user-login-signup-api/)

# Notas del proyecto: Por favor tener en cuenta
# para el manejo de permisos y menús de forma individual por usuario se insertaron las respectivas tablas directo en sql, estas tablas no tienen migracion
<!-- CREATE TABLE usr_app_usuarios_menus (
    id BIGINT PRIMARY KEY IDENTITY(1,1),
    usuario_id BIGINT,
	menu_id BIGINT,
	descripcion NVARCHAR(500) NULL,
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
    FOREIGN KEY (usuario_id) REFERENCES usr_app_usuarios(id),
	FOREIGN KEY (menu_id) REFERENCES usr_app_menus(id)
); -->

<!-- CREATE TABLE usr_app_permisos_roles (
    id BIGINT PRIMARY KEY IDENTITY(1,1),
    rol_id BIGINT,
	permiso_id BIGINT,
	descripcion NVARCHAR(300) NULL,
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
    FOREIGN KEY (rol_id) REFERENCES usr_app_roles(id),
	FOREIGN KEY (permiso_id) REFERENCES usr_app_permisos(id)
); -->


<!-- CREATE TABLE usr_app_permisos_usuarios (
    id BIGINT PRIMARY KEY IDENTITY(1,1),
    usuario_id BIGINT,
	permiso_id BIGINT,
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
    FOREIGN KEY (usuario_id) REFERENCES usr_app_usuarios(id),
	FOREIGN KEY (permiso_id) REFERENCES usr_app_permisos(id)
); -->

