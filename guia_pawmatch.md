# PawMatch V2 - Guia de Instalacion y Uso

**Version:** 1.0  
**Plataforma:** Windows 11 con XAMPP  
**Ultima actualizacion:** Mayo 2026

---

## Tabla de Contenidos

1. [Requisitos del Sistema](#1-requisitos-del-sistema)
2. [Estructura del Proyecto](#2-estructura-del-proyecto)
3. [Instalacion Paso a Paso](#3-instalacion-paso-a-paso)
4. [Configuracion del Backend](#4-configuracion-del-backend)
5. [Verificacion de la Instalacion](#5-verificacion-de-la-instalacion)
6. [Uso del Sistema](#6-uso-del-sistema)
   - 6.1 [Inicio de Sesion y Registro](#61-inicio-de-sesion-y-registro)
   - 6.2 [Buscar Mascotas](#62-buscar-mascotas)
   - 6.3 [Test de Compatibilidad](#63-test-de-compatibilidad)
   - 6.4 [Solicitudes de Adopcion](#64-solicitudes-de-adopcion)
   - 6.5 [Donaciones](#65-donaciones)
   - 6.6 [Reportes de Animales Callejeros](#66-reportes-de-animales-callejeros)
   - 6.7 [Mi Perfil](#67-mi-perfil)
   - 6.8 [Funciones del Administrador](#68-funciones-del-administrador)
7. [Referencia de la API REST](#7-referencia-de-la-api-rest)
8. [Configuracion Avanzada](#8-configuracion-avanzada)
9. [Solucion de Problemas](#9-solucion-de-problemas)
10. [Credenciales de Prueba](#10-credenciales-de-prueba)

---

## 1. Requisitos del Sistema

### Software obligatorio

| Componente | Version minima | Notas |
|---|---|---|
| XAMPP | 7.4 o superior | Incluye Apache, PHP y MySQL |
| PHP | 7.4 o superior | Incluido en XAMPP |
| MySQL | 5.7 o superior | Incluido en XAMPP |
| Navegador web | Cualquier version moderna | Chrome, Firefox, Edge o Safari |

### Conexion a internet

El mapa interactivo de reportes carga sus teselas desde los servidores de OpenStreetMap. Las imagenes de mascotas usan URLs externas de Unsplash. Sin conexion, estas secciones no renderizan correctamente, pero el resto del sistema funciona de forma local.

---

## 2. Estructura del Proyecto

```
PawMatchV2/
|
|-- public/                        Frontend (HTML, CSS, JS)
|   |-- index.html                 Punto de entrada de la SPA
|   |-- css/
|   |   `-- main.css               Estilos globales y responsivos
|   `-- js/
|       |-- app.js                 Logica de la aplicacion (SPA)
|       `-- api.js                 Cliente HTTP hacia el backend
|
|-- backend/                       Backend (API REST en PHP)
|   |-- index.php                  Router principal
|   |-- config/
|   |   `-- config.php             Credenciales de BD y constantes
|   |-- classes/                   Modelos de datos
|   |   |-- Database.php           Conexion PDO (patron Singleton)
|   |   |-- User.php
|   |   |-- Pet.php
|   |   |-- Adoption.php
|   |   |-- Donation.php
|   |   |-- ReportedAnimal.php
|   |   `-- JWT.php
|   |-- controllers/               Controladores MVC
|   |   |-- UsersController.php
|   |   |-- PetsController.php
|   |   |-- AdoptionsController.php
|   |   |-- DonationsController.php
|   |   `-- ReportedAnimalsController.php
|   `-- middleware/
|       `-- AuthMiddleware.php     Validacion de token JWT
|
`-- database/
    |-- schema.sql                 Estructura de tablas (sin datos)
    `-- pawmatch_complete.sql      Estructura mas datos de prueba
```

---

## 3. Instalacion Paso a Paso

### Paso 1: Instalar XAMPP

Si aun no tienes XAMPP instalado, descargalo desde `https://www.apachefriends.org` y ejecuta el instalador. Durante la instalacion asegurate de marcar los componentes **Apache**, **MySQL** y **PHP**. Acepta la ruta de instalacion predeterminada (`C:\xampp`).

### Paso 2: Copiar el proyecto a htdocs

El servidor web de XAMPP sirve archivos desde `C:\xampp\htdocs`. El proyecto debe quedar en la siguiente ruta exacta:

```
C:\xampp\htdocs\PawMatchV2\
```

Si tienes la carpeta en otra ubicacion, copiala desde el Explorador de Windows o ejecuta en el Simbolo del sistema:

```cmd
xcopy "C:\ruta\de\origen\PawMatchV2" "C:\xampp\htdocs\PawMatchV2\" /E /I
```

### Paso 3: Iniciar los servicios de XAMPP

1. Abre **XAMPP Control Panel** (busca el acceso directo en el escritorio o en el menu Inicio).
2. Haz clic en **Start** junto a **Apache**.
3. Haz clic en **Start** junto a **MySQL**.
4. Ambos servicios deben mostrar fondo verde en sus indicadores. Si alguno falla, revisa la seccion [9. Solucion de Problemas](#9-solucion-de-problemas).

### Paso 4: Crear la base de datos

#### Opcion A - phpMyAdmin (recomendado para principiantes)

1. Abre tu navegador y ve a `http://localhost/phpmyadmin`.
2. En el panel izquierdo, haz clic en **Nueva**.
3. En el campo **Nombre de la base de datos**, escribe `pawmatch`.
4. Deja el cotejamiento en `utf8mb4_general_ci` y haz clic en **Crear**.
5. Con la base de datos `pawmatch` seleccionada en el panel izquierdo, haz clic en la pestana **Importar**.
6. Haz clic en **Seleccionar archivo** y navega hasta:
   ```
   C:\xampp\htdocs\PawMatchV2\database\pawmatch_complete.sql
   ```
7. Haz clic en **Ejecutar**.
8. phpMyAdmin mostrara el mensaje **Importacion finalizada correctamente** y veras las tablas creadas en el panel izquierdo.

Usa `pawmatch_complete.sql` si quieres datos de prueba incluidos (usuarios, mascotas y reportes de ejemplo). Usa `schema.sql` si prefieres una instalacion limpia solo con la estructura.

#### Opcion B - Linea de comandos

```cmd
cd C:\xampp\mysql\bin
mysql -u root
```

Dentro del cliente MySQL ejecuta:

```sql
CREATE DATABASE pawmatch CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pawmatch;
SOURCE C:/xampp/htdocs/PawMatchV2/database/pawmatch_complete.sql;
EXIT;
```

> Si tu instalacion de MySQL tiene contrasena de root, usa `mysql -u root -p` e ingrésala cuando se solicite.

### Paso 5: Habilitar mod_rewrite en Apache

El router del backend depende del modulo `mod_rewrite` de Apache para manejar las rutas de la API. Si este modulo no esta activo, todas las llamadas a la API devuelven 404.

1. Abre el archivo `C:\xampp\apache\conf\httpd.conf` con un editor de texto (Notepad, VS Code, etc.).
2. Busca la linea:
   ```
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. Elimina el caracter `#` al inicio para que quede:
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
4. En el mismo archivo, busca el bloque:
   ```
   <Directory "C:/xampp/htdocs">
   ```
5. Dentro de ese bloque, cambia `AllowOverride None` por:
   ```
   AllowOverride All
   ```
6. Guarda el archivo y reinicia Apache desde el XAMPP Control Panel (Stop y luego Start).

### Paso 6: Acceder a la aplicacion

Abre tu navegador y ve a:

```
http://localhost/PawMatchV2/public/
```

Debes ver la pantalla de inicio de sesion de PawMatch.

---

## 4. Configuracion del Backend

El archivo de configuracion principal es `backend/config/config.php`. Sus valores predeterminados funcionan con una instalacion estandar de XAMPP. Solo necesitas editarlo si tu entorno difiere.

```php
// Conexion a la base de datos
define('DB_HOST', 'localhost');   // Host de MySQL
define('DB_USER', 'root');        // Usuario de MySQL
define('DB_PASS', '');            // Contrasena (vacia por defecto en XAMPP)
define('DB_NAME', 'pawmatch');    // Nombre de la base de datos
define('DB_PORT', 3306);          // Puerto de MySQL

// URLs de la aplicacion
define('API_URL', 'http://localhost/PawMatchV2/backend');
define('APP_URL', 'http://localhost/PawMatchV2');

// Seguridad JWT
define('JWT_SECRET', 'your-secret-key-change-in-production-pawmatch-2024');

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Modo depuracion (cambiar a false en produccion)
define('DEBUG', true);
```

**Importante para produccion:** cambia el valor de `JWT_SECRET` por una cadena aleatoria larga y establece `DEBUG` en `false` antes de desplegar en un servidor publico.

---

## 5. Verificacion de la Instalacion

Antes de usar la aplicacion, puedes correr los scripts de diagnostico incluidos.

### Verificar la conexion a la base de datos

Abre en el navegador:

```
http://localhost/PawMatchV2/backend/test-connection.php
```

Debera mostrar un mensaje de conexion exitosa junto con la version de MySQL.

### Verificar la configuracion completa

```
http://localhost/PawMatchV2/backend/verify-setup.php
```

Este script comprueba que todas las tablas existan, que los indices esten creados y que la configuracion de PHP sea compatible.

### Verificar la API directamente

Puedes probar un endpoint de la API desde el navegador o desde una herramienta como Postman o curl:

```
http://localhost/PawMatchV2/backend/pets
```

Si la API responde con JSON, el enrutamiento esta funcionando correctamente.

---

## 6. Uso del Sistema

### 6.1 Inicio de Sesion y Registro

#### Iniciar sesion

1. Ve a `http://localhost/PawMatchV2/public/`.
2. Ingresa tu correo electronico y contrasena.
3. Haz clic en **Inicia Sesion**.
4. El sistema almacena el token JWT en `localStorage` y redirige a la pagina principal.

#### Registrar una cuenta nueva

1. En la pantalla de inicio de sesion, haz clic en el enlace **Registrarse**.
2. Completa el formulario: nombre completo, correo electronico y contrasena.
3. La contrasena debe ser segura (el backend aplica validacion).
4. Haz clic en **Registrarse**. El sistema inicia la sesion automaticamente.

#### Cerrar sesion

Haz clic en el boton **Salir** en la barra de navegacion superior derecha. El token JWT y los datos de sesion se eliminan del `localStorage`.

---

### 6.2 Buscar Mascotas

La seccion **Buscar Mascotas** lista todas las mascotas disponibles para adopcion con paginacion y filtros combinables.

#### Filtros disponibles

| Filtro | Valores |
|---|---|
| Especie | Perro, Gato, Conejo, Ave |
| Tamaño | Pequeño, Mediano, Grande |
| Nivel de energia | Bajo, Moderado, Alto |
| Busqueda de texto | Nombre o raza (campo libre) |

Para aplicar los filtros, selecciona los valores deseados y haz clic en **Aplicar Filtros**. Para buscar por texto tambien puedes presionar Enter dentro del campo de busqueda.

#### Ver el detalle de una mascota

Haz clic sobre cualquier tarjeta de mascota para abrir la vista de detalle. Ahi encontraras: nombre, especie, raza, edad, tamaño, nivel de energia y descripcion completa. Desde esta vista puedes iniciar una solicitud de adopcion.

---

### 6.3 Test de Compatibilidad

El test evalua que tipo de mascota se adapta mejor a tu estilo de vida y muestra recomendaciones personalizadas.

#### Como realizar el test

1. Haz clic en **Test** en la barra de navegacion.
2. Responde las cinco preguntas:

| # | Pregunta | Opciones |
|---|---|---|
| 1 | Donde vives | Departamento / Casa / Finca o terreno |
| 2 | Tiempo disponible para una mascota | Poco (menos de 1 hora) / Moderado (1-3 h) / Mucho (mas de 3 h) |
| 3 | Nivel de actividad fisica | Bajo / Moderado / Alto |
| 4 | Experiencia con animales | No / Algo / Mucha |
| 5 | Tipo de mascota preferida | Perro / Gato / Conejo / Ave |

3. Haz clic en **Ver Recomendaciones**.

#### Interpretar los resultados

El sistema calcula una puntuacion sobre 9 puntos:

- **7 a 9** (verde): Alta compatibilidad, eres un excelente candidato para adoptar cualquier mascota.
- **4 a 6** (amarillo): Compatibilidad media, considera mascotas de cuidado moderado.
- **0 a 3** (rojo): Baja compatibilidad, se recomienda comenzar con mascotas tranquilas y de poco mantenimiento.

Debajo de la puntuacion se muestran sugerencias concretas y un listado de mascotas disponibles que coinciden con las preferencias indicadas.

---

### 6.4 Solicitudes de Adopcion

#### Enviar una solicitud

1. Desde la vista de detalle de la mascota, haz clic en **Adoptar**.
2. Escribe un mensaje opcional explicando por que quieres adoptar a esa mascota.
3. Haz clic en **Enviar Solicitud**.
4. La solicitud queda registrada con estado `pending`.

#### Consultar el historial de solicitudes

Haz clic en **Solicitudes** en la barra de navegacion. Cada tarjeta muestra la fotografia de la mascota, el mensaje enviado, el estado actual y la fecha de solicitud.

#### Estados posibles

| Estado | Descripcion |
|---|---|
| `pending` | Enviada, en espera de revision por el administrador |
| `approved` | Aprobada |
| `rejected` | Rechazada |

#### Cancelar una solicitud

En la tarjeta de la solicitud, haz clic en **Cancelar Solicitud** y confirma en el dialogo. La solicitud se elimina permanentemente.

---

### 6.5 Donaciones

1. Haz clic en **Donar** en la barra de navegacion.
2. Ingresa el monto en pesos mexicanos (MXN) en el campo correspondiente.
3. Opcionalmente agrega un mensaje de apoyo.
4. Haz clic en **Donar**.
5. El sistema confirma la operacion y actualiza el panel de estadisticas de la misma pagina, que muestra el total recaudado, el numero de donaciones y el promedio por donacion.

---

### 6.6 Reportes de Animales Callejeros

Este modulo permite a la comunidad documentar animales callejeros que necesitan atencion o rescate, con ubicacion geografica en un mapa interactivo.

#### Crear un reporte

1. Haz clic en **Reportes** en la barra de navegacion.
2. El mapa carga centrado en la zona de Tijuana.
3. Haz clic sobre el mapa en el punto exacto donde viste al animal. Las coordenadas de latitud y longitud se rellenan automaticamente en el formulario.
4. Completa los campos:
   - **Tipo de animal:** Perro, Gato u Otro.
   - **Descripcion:** estado del animal, color, condicion fisica visible, senias particulares.
   - **Telefono de contacto** (opcional).
   - **URL de imagen** (opcional): enlace a una fotografia del animal.
5. Haz clic en **Reportar Animal**.
6. El marcador aparece de inmediato en el mapa y el reporte se agrega a la lista.

#### Ver reportes existentes

Los marcadores en el mapa indican la ubicacion de reportes activos. Haz clic en un marcador para ver un resumen emergente. En la lista debajo del mapa encontraras las tarjetas con todos los detalles.

#### Cambiar el estado de un reporte

Cualquier usuario autenticado puede actualizar el estado de cualquier reporte para reflejar el progreso real de la situacion.

1. Localiza el reporte en la lista.
2. Haz clic en **Cambiar Estado**.
3. Selecciona el nuevo estado:

| Estado | Descripcion |
|---|---|
| `pending` | Sin atencion todavia |
| `visto` | El reporte ha sido revisado por la comunidad |
| `en proceso` | Se estan tomando acciones para ayudar al animal |
| `rescatado` | El animal fue rescatado exitosamente |
| `cerrado` | Caso cerrado |

4. Guarda el cambio.

#### Editar los detalles de un reporte

Solo el usuario que creo el reporte puede editar sus detalles (descripcion, tipo, imagen). El administrador puede eliminar cualquier reporte.

#### Agregar actualizaciones

1. En la tarjeta del reporte, haz clic en **Ver Detalles** o expande el panel de actualizaciones.
2. Escribe un comentario sobre el estado actual del animal.
3. Haz clic en **Agregar Actualizacion**.
4. El comentario queda registrado con tu nombre y la fecha/hora.

---

### 6.7 Mi Perfil

1. Haz clic en **Perfil** en la barra de navegacion.
2. El sistema carga los datos actuales de tu cuenta.
3. Puedes modificar los siguientes campos:
   - **Nombre completo**
   - **Ubicacion** (ciudad o region)
   - **Telefono de contacto**
   - **Biografia** (descripcion personal opcional)
4. El campo de correo electronico no es editable por razones de seguridad.
5. Haz clic en **Guardar Cambios**. El sistema muestra una confirmacion de exito.

---

### 6.8 Funciones del Administrador

El usuario `admin@pawmatch.com` dispone de funciones adicionales que no estan disponibles para usuarios regulares.

#### Gestionar mascotas

**Agregar una mascota:**
1. Ve a **Buscar Mascotas**.
2. Haz clic en el boton **Agregar Mascota** (visible solo para el administrador).
3. Completa el formulario: nombre, especie, raza, edad, tamaño, nivel de energia, descripcion y URL de imagen.
4. Haz clic en **Guardar**.

**Editar una mascota:**
1. Abre la vista de detalle de la mascota.
2. Haz clic en **Editar**.
3. Modifica los campos necesarios.
4. Haz clic en **Guardar Cambios**.

**Eliminar una mascota:**
1. Abre la vista de detalle de la mascota.
2. Haz clic en **Eliminar** y confirma en el dialogo de advertencia.

#### Gestionar solicitudes de adopcion

El administrador puede cambiar el estado de cualquier solicitud (`approved` o `rejected`) desde el panel de administracion.

#### Eliminar reportes de animales

El boton **Eliminar** en las tarjetas de reportes es visible unicamente para `admin@pawmatch.com`. Los demas usuarios no pueden eliminar reportes.

---

## 7. Referencia de la API REST

El backend expone una API RESTful en `http://localhost/PawMatchV2/backend/`. Todos los endpoints protegidos requieren el encabezado HTTP `Authorization: Bearer <token>`, donde el token se obtiene al iniciar sesion o registrarse.

### Autenticacion

| Metodo | Ruta | Descripcion | Autenticacion |
|---|---|---|---|
| POST | `/users/register` | Registrar nuevo usuario | No |
| POST | `/users/login` | Iniciar sesion | No |
| GET | `/users/profile` | Obtener perfil del usuario activo | Si |
| POST | `/users/profile/update` | Actualizar datos del perfil | Si |
| POST | `/users/change-password` | Cambiar contrasena | Si |

### Mascotas

| Metodo | Ruta | Descripcion | Rol |
|---|---|---|---|
| GET | `/pets` | Listar mascotas (soporta filtros por query string) | Autenticado |
| GET | `/pets/:id` | Obtener mascota por ID | Autenticado |
| POST | `/pets` | Crear mascota | Admin |
| PUT | `/pets/:id` | Actualizar mascota | Admin |
| DELETE | `/pets/:id` | Eliminar mascota | Admin |

Parametros de filtro disponibles para `GET /pets`: `page`, `limit`, `species`, `size`, `energy`, `search`.

### Adopciones

| Metodo | Ruta | Descripcion | Rol |
|---|---|---|---|
| POST | `/adoptions` | Crear solicitud de adopcion | Autenticado |
| GET | `/adoptions` | Listar solicitudes del usuario activo | Autenticado |
| PUT | `/adoptions/:id` | Cambiar estado de solicitud | Admin |
| DELETE | `/adoptions/:id` | Cancelar solicitud | Autenticado |

### Donaciones

| Metodo | Ruta | Descripcion | Rol |
|---|---|---|---|
| POST | `/donations` | Crear donacion | Autenticado |
| GET | `/donations/user/my-donations` | Listar donaciones del usuario activo | Autenticado |
| GET | `/donations/stats` | Estadisticas globales de donaciones | Autenticado |

### Reportes de animales

| Metodo | Ruta | Descripcion | Rol |
|---|---|---|---|
| POST | `/reported-animals` | Crear reporte | Autenticado |
| GET | `/reported-animals` | Listar reportes (soporta `page`, `limit`, `status`) | Autenticado |
| GET | `/reported-animals/:id` | Obtener reporte por ID | Autenticado |
| GET | `/reported-animals/nearby` | Reportes cercanos por coordenadas | Autenticado |
| PUT | `/reported-animals/:id` | Cambiar estado del reporte | Autenticado |
| PUT | `/reported-animals/:id/edit` | Editar detalles del reporte | Creador |
| POST | `/reported-animals/:id/update` | Agregar comentario o actualizacion | Autenticado |
| DELETE | `/reported-animals/:id` | Eliminar reporte | Admin |

### Ejemplo de peticion con curl

```bash
# Iniciar sesion y obtener token
curl -X POST http://localhost/PawMatchV2/backend/users/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pawmatch.com","password":"Admin@123"}'

# Listar mascotas con filtro de especie
curl http://localhost/PawMatchV2/backend/pets?species=Perro \
  -H "Authorization: Bearer <token>"
```

---

## 8. Configuracion Avanzada

### Cambiar el puerto de Apache

Si el puerto 80 esta ocupado por otro servicio:

1. Abre `C:\xampp\apache\conf\httpd.conf`.
2. Cambia `Listen 80` a `Listen 8080` (o cualquier puerto libre).
3. Reinicia Apache.
4. Accede a la aplicacion en `http://localhost:8080/PawMatchV2/public/`.

### Cambiar el puerto de MySQL

1. Abre `C:\xampp\mysql\bin\my.ini`.
2. Cambia `port=3306` al puerto deseado.
3. Actualiza `DB_PORT` en `backend/config/config.php` con el mismo valor.
4. Reinicia MySQL.

### Aumentar el limite de carga de archivos

1. Abre `C:\xampp\php\php.ini`.
2. Localiza y modifica:
   ```ini
   upload_max_filesize = 50M
   post_max_size = 50M
   ```
3. Reinicia Apache.

### Cambiar la zona horaria

En `backend/config/config.php`:

```php
date_default_timezone_set('America/Tijuana');
```

Para consultar los identificadores validos de zona horaria de PHP, visita `https://www.php.net/manual/es/timezones.php`.

### Despliegue en servidor de hosting (cPanel)

1. Sube el contenido completo de `PawMatchV2` al directorio `public_html` via FTP o el administrador de archivos de cPanel.
2. En cPanel, crea una base de datos MySQL y un usuario con todos los privilegios sobre ella.
3. Importa `database/pawmatch_complete.sql` desde phpMyAdmin del hosting.
4. Edita `backend/config/config.php` con el host, usuario, contrasena y nombre de base de datos asignados por el hosting.
5. Actualiza `API_URL` y `APP_URL` con el dominio real.
6. Cambia `JWT_SECRET` por una cadena aleatoria segura.
7. Establece `DEBUG` en `false`.

### Resetear la contrasena del administrador

```cmd
php C:\xampp\htdocs\PawMatchV2\backend\reset-admin.php
```

### Resetear la contrasena de un usuario

```cmd
php C:\xampp\htdocs\PawMatchV2\backend\reset-password.php
```

---

## 9. Solucion de Problemas

### No se puede conectar a la base de datos

**Causa mas frecuente:** MySQL no esta iniciado en XAMPP.

1. Abre XAMPP Control Panel y verifica que MySQL muestre fondo verde.
2. Si falla al iniciar, revisa `C:\xampp\mysql\data\` y busca un archivo con extension `.err` para identificar el error especifico.
3. Verifica que el nombre de la base de datos en `config.php` sea exactamente `pawmatch`.
4. Si usas un puerto distinto al 3306, asegurate de que `DB_PORT` este actualizado.
5. Prueba cambiar `DB_HOST` de `localhost` a `127.0.0.1`:
   ```php
   define('DB_HOST', '127.0.0.1');
   ```

### Error 404 en las llamadas a la API

1. Verifica que `mod_rewrite` este habilitado (ver [Paso 5](#paso-5-habilitar-mod_rewrite-en-apache)).
2. Confirma que los tres archivos `.htaccess` existan en sus rutas correspondientes:
   ```
   C:\xampp\htdocs\PawMatchV2\.htaccess
   C:\xampp\htdocs\PawMatchV2\public\.htaccess
   C:\xampp\htdocs\PawMatchV2\backend\.htaccess
   ```
3. Como prueba alternativa, llama al endpoint con la ruta explicita:
   ```
   http://localhost/PawMatchV2/backend/index.php/pets
   ```
   Si esto funciona, el problema es el archivo `.htaccess`.

### Error CORS en la consola del navegador

El backend ya envía `Access-Control-Allow-Origin: *` en todos los encabezados de respuesta. Si aun aparece un error CORS:

1. Verifica que estas accediendo al frontend desde `http://localhost` y no desde una ruta de archivo local (`file:///...`).
2. Limpia la cache del navegador con `Ctrl+Shift+Delete`.
3. Reinicia Apache.

### La pagina muestra contenido en blanco

1. Abre las herramientas de desarrollador del navegador (`F12`) y revisa la pestana **Console** para ver errores de JavaScript.
2. Revisa tambien la pestana **Network** para identificar peticiones fallidas a la API.
3. Comprueba los logs de Apache:
   ```
   C:\xampp\apache\logs\error.log
   ```
4. Comprueba los logs de PHP:
   ```
   C:\xampp\php\logs\php_error.log
   ```

### El mapa de reportes no carga

1. Verifica tu conexion a internet. El mapa usa tiles de OpenStreetMap via CDN.
2. Limpia la cache del navegador.
3. Si usas un bloqueador de anuncios o firewall, asegurate de que los dominios `*.tile.openstreetmap.org` y el CDN de Leaflet no esten bloqueados.

### Las imagenes de mascotas no se muestran

Las imagenes usan URLs de Unsplash. Si no cargan, verifica la conexion a internet. En produccion se recomienda alojar las imagenes localmente y actualizar las URLs en la base de datos.

### La sesion se cierra inesperadamente

El token JWT tiene una duracion de 24 horas (definida por `SESSION_LIFETIME` en `config.php`). Al expirar, el usuario debe volver a iniciar sesion. Si la sesion se cierra antes de ese tiempo, limpia el `localStorage` del navegador:

1. Abre las herramientas de desarrollador (`F12`).
2. Ve a la pestana **Application** (Chrome) o **Storage** (Firefox).
3. En **Local Storage**, selecciona la entrada de `localhost`.
4. Elimina las entradas `pawmatch_token` y `pawmatch_user`.
5. Recarga la pagina e inicia sesion de nuevo.

### Apache no inicia (conflicto de puerto)

Si otro programa ya usa el puerto 80 (Skype, IIS, otro servidor web):

1. Cambia el puerto de Apache a 8080 (ver [seccion 8](#8-configuracion-avanzada)).
2. O cierra el programa que ocupa el puerto 80 antes de iniciar Apache.

---

## 10. Credenciales de Prueba

La base de datos importada desde `pawmatch_complete.sql` incluye los siguientes usuarios:

| Correo electronico | Contrasena | Rol |
|---|---|---|
| `admin@pawmatch.com` | `Admin@123` | Administrador |
| `juan@pawmatch.com` | `Admin@123` | Usuario |
| `maria@pawmatch.com` | `Admin@123` | Usuario |
| `carlos@pawmatch.com` | `Admin@123` | Usuario |

La base de datos tambien incluye:

- 6 mascotas disponibles para adopcion
- 3 solicitudes de adopcion de ejemplo
- 3 donaciones de ejemplo
- 3 reportes de animales callejeros con actualizaciones

---

*PawMatch V2 - Kenneth Armenta Ibarra | Matricula 1293616 | Grupo 371 | UABC Campus Otay*
