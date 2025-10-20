# Chat en Tiempo Real - PHP + MySQL

## 🚀 Deployment en Render.com

### Pasos para subir a Render:

1. **Subir código a GitHub**
   - Crear repositorio en GitHub
   - Subir todos los archivos del proyecto

2. **Crear servicio en Render**
   - Ir a [Render.com](https://render.com)
   - Crear nuevo "Web Service"
   - Conectar con tu repositorio de GitHub

3. **Configurar variables de entorno en Render**
   ```
   DB_HOST=tu_host_mysql_render
   DB_NAME=tu_base_datos_render
   DB_USER=tu_usuario_render
   DB_PASS=tu_password_render
   DB_PORT=3306
   PUSHER_APP_ID=tu_pusher_app_id
   PUSHER_KEY=tu_pusher_key
   PUSHER_SECRET=tu_pusher_secret
   PUSHER_CLUSTER=tu_cluster
   ```

4. **Configurar base de datos**
   - Crear servicio PostgreSQL o MySQL en Render
   - Ejecutar el archivo `database.sql` en tu base de datos

### Configuración local (XAMPP):

1. **Crear base de datos**
   ```sql
   -- En phpMyAdmin o MySQL:
   CREATE DATABASE chat_realtime;
   ```

2. **Importar estructura**
   - Ejecutar el archivo `database.sql`

3. **Configurar .env**
   - Las variables ya están configuradas para XAMPP

## 📁 Estructura del proyecto:

```
├── index.php          # Página principal
├── api.php            # API para mensajes (MySQL)
├── config.php         # Configuración y conexión DB
├── database.sql       # Estructura de la base de datos
├── composer.json      # Dependencias PHP
├── .env              # Variables de entorno
├── .gitignore        # Archivos a ignorar
└── static/
    ├── css/style.css  # Estilos
    └── js/app.js      # JavaScript del chat
```

## 🔧 Tecnologías:

- **Backend**: PHP 8.1+
- **Base de datos**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Tiempo real**: Pusher (opcional)
- **Hosting**: Render.com

## ✅ Características:

- ✅ Chat en tiempo real
- ✅ Almacenamiento en MySQL
- ✅ Validación de seguridad
- ✅ Rate limiting
- ✅ Responsive design
- ✅ Listo para producción