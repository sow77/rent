# CarRent - Sistema de Alquiler de Vehículos

## Requisitos Previos

1. Instalar XAMPP
2. Node.js y npm

## Configuración del Entorno Local

1. Iniciar servicios de XAMPP:
   - Apache
   - MySQL

2. Instalar dependencias:
```bash
npm install
```

3. Configurar base de datos:
```bash
npm run db:setup
```

## Credenciales de Prueba

### Administrador
- Email: sow.alpha.m@gmail.com
- Contraseña: Sw.12345

## Pruebas del Sistema

### Como Administrador:
1. Iniciar sesión con las credenciales de administrador
2. Acceder al panel de administración (/admin)
3. Gestionar vehículos:
   - Añadir nuevos vehículos
   - Modificar existentes
   - Eliminar vehículos
4. Gestionar reservas:
   - Ver todas las reservas
   - Aprobar/rechazar reservas
   - Ver estadísticas

### Como Usuario:
1. Registrar nueva cuenta
2. Iniciar sesión
3. Buscar vehículos disponibles
4. Realizar una reserva
5. Ver historial de reservas

## Estructura de la Base de Datos

### Tablas
- users: Usuarios y administradores
- vehicles: Catálogo de vehículos
- reservations: Reservas de vehículos
- locations: Ubicaciones disponibles

## Desarrollo

1. Iniciar el servidor:
```bash
npm run dev
```

2. Acceder a la aplicación:
```
http://localhost:3000
```
