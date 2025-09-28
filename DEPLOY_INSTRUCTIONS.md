# Instrucciones de Despliegue en 000webhost

## Pasos para desplegar Dev Rent en 000webhost:

### 1. Crear cuenta en 000webhost
1. Ve a https://www.000webhost.com/
2. Crea una cuenta gratuita
3. Verifica tu email

### 2. Crear un nuevo sitio web
1. En el panel de control, haz clic en "Create New Website"
2. Elige un subdominio (ej: `dev-rent.000webhostapp.com`)
3. Selecciona "PHP" como tecnología
4. Anota los datos de la base de datos que te proporcionen

### 3. Configurar la base de datos
1. Ve a "MySQL Databases" en el panel de control
2. Crea una nueva base de datos
3. Anota:
   - Host: `localhost`
   - Database Name: `id_xxxxx_nombre_bd`
   - Username: `id_xxxxx_usuario`
   - Password: `tu_password`

### 4. Subir archivos
1. Ve a "File Manager" en el panel de control
2. Sube todos los archivos del proyecto a la carpeta `public_html`
3. **IMPORTANTE**: No subas la carpeta `database/` (contiene datos de desarrollo)

### 5. Importar la base de datos
1. Ve a "phpMyAdmin" en el panel de control
2. Selecciona tu base de datos
3. Ve a la pestaña "Import"
4. Sube el archivo `database/carrent_db.sql`
5. Haz clic en "Go" para importar

### 6. Configurar la aplicación
1. Edita el archivo `config/production_config.php`
2. Reemplaza los valores con los datos de tu base de datos de 000webhost:
   ```php
   define('DB_NAME', 'id_tu_database_name');
   define('DB_USER', 'id_tu_username');
   define('DB_PASS', 'tu_password');
   define('APP_URL', 'https://tu-dominio.000webhostapp.com');
   ```

### 7. Renombrar archivo de configuración
1. Renombra `config/production_config.php` a `config/config.php`
2. Esto sobrescribirá la configuración de desarrollo

### 8. Verificar el sitio
1. Visita tu dominio: `https://tu-dominio.000webhostapp.com`
2. Verifica que todas las funcionalidades funcionen correctamente

## Archivos importantes a subir:
- ✅ Todos los archivos PHP
- ✅ Carpeta `public/` (CSS, JS, imágenes)
- ✅ Carpeta `config/` (con la configuración de producción)
- ✅ Archivo `.htaccess`
- ✅ Archivo `index.php`
- ❌ NO subir carpeta `database/` (solo importar el SQL)
- ❌ NO subir archivos de configuración local

## Solución de problemas comunes:

### Error de conexión a base de datos:
- Verifica que los datos de conexión en `config.php` sean correctos
- Asegúrate de que la base de datos esté creada y el usuario tenga permisos

### Error 500:
- Verifica que el archivo `.htaccess` esté subido correctamente
- Revisa los logs de error en el panel de control de 000webhost

### Imágenes no se muestran:
- Verifica que la carpeta `public/images/` esté subida
- Revisa las rutas en el código (deben ser relativas o absolutas con el dominio correcto)

## Notas importantes:
- 000webhost tiene límites en el plan gratuito (ancho de banda, espacio, etc.)
- El sitio puede tardar unos minutos en estar disponible después del despliegue
- Mantén una copia de seguridad de tu base de datos
