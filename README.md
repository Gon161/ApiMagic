# 🪄 Api Magic

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="250">
</p>

<p align="center">
  <a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

## 📖 Descripción

**Api Magic** es una API RESTful desarrollada con Laravel que gestiona el sistema de autenticación y registro de usuarios. Incluye flujos completos de registro con verificación por correo electrónico, inicio de sesión con tokens JWT y recuperación de contraseña mediante códigos de verificación.

---

## 🛠️ Tecnologías

| Tecnología | Descripción |
|---|---|
| **Laravel** | Framework principal de PHP |
| **Laravel Sanctum / Passport** | Autenticación por tokens (auth:api) |
| **Laravel Mail** | Envío de correos de verificación |
| **Eloquent ORM** | Interacción con la base de datos |

---

## 📂 Estructura Clave

```
├── app/
│   ├── Http/Controllers/
│   │   └── UsuarioController.php    # Controlador principal de usuarios
│   ├── Mail/
│   │   ├── VerificacionEmail.php    # Mailable de verificación de cuenta
│   │   └── RecuperarMailable.php    # Mailable de recuperación de contraseña
│   └── Models/
│       └── User.php                 # Modelo de usuario
├── routes/
│   └── api.php                      # Rutas de la API
└── .env                             # Variables de entorno y configuración de mail
```

---

## 🚀 Instalación

```bash
# Clonar el repositorio
git clone <url-del-repositorio>
cd api-magic

# Instalar dependencias
composer install

# Copiar archivo de entorno
cp .env.example .env

# Generar clave de la aplicación
php artisan key:generate

# Configurar la base de datos y variables de correo en .env
# Ejecutar migraciones
php artisan migrate

# Instalar el paquete de autenticación (si no está presente)
php artisan passport:install
# o
php artisan sanctum:publish --force

# Iniciar servidor
php artisan serve
```

---

## 🔗 Endpoints de la API

Todas las rutas están agrupadas bajo el prefijo `/api/auth`.

### 1. Registro de usuario
| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/auth/registroForm` | Registra un nuevo usuario y envía correo de verificación |

**Body (JSON):**
```json
{
  "nombre": "Juan",
  "apellidoPaterno": "García",
  "apellidoMaterno": "López",
  "correo": "juan@correo.com",
  "fechaNacimiento": "2000-01-15",
  "contrasenia": "micontrasenia123",
  "contrasenia2": "micontrasenia123"
}
```

**Respuesta exitosa:**
```json
{
  "estatus": "sucess",
  "mensaje": "Cuenta creada"
}
```

---

### 2. Verificación de correo
| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/auth/verificacionMail/{codigo}` | Verifica la cuenta del usuario mediante el código enviado al correo |

**Ejemplo:**
```
GET /api/auth/verificacionMail/483921
```

**Respuesta exitosa:** `Cuenta verificada, vuelve a la aplicacion` (HTTP 201)

---

### 3. Inicio de sesión
| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/auth/verificarCredenciales` | Autentica al usuario y devuelve un token |

**Body (JSON):**
```json
{
  "correo": "juan@correo.com",
  "contrasenia": "micontrasenia123"
}
```

**Respuesta exitosa:**
```json
{
  "nombre": "Juan",
  "apellidoPaterno": "García",
  "apellidoMaterno": "López",
  "correo": "juan@correo.com",
  "tokenRecovery": "eyJ0eXAiOiJKV1QiLCJ..."
}
```

> ⚠️ La cuenta debe estar verificada (`status = 1`) para poder iniciar sesión.

---

### 4. Recuperación de contraseña
| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/auth/recuperarContrasenia` | Envía un código de recuperación al correo del usuario |

**Body (JSON):**
```json
{
  "correo": "juan@correo.com"
}
```

**Respuesta exitosa:**
```json
{
  "estatus": "success",
  "mensaje": "¡El correo se a enviado"
}
```

---

### 5. Validar código de recuperación
| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/auth/codigo` | Valida que el código de recuperación sea correcto |

**Body (JSON):**
```json
{
  "codigo": "483921"
}
```

**Respuesta exitosa:**
```json
{
  "estatus": "success",
  "codigo": "483921"
}
```

---

### 6. Cambiar contraseña
| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/auth/cambio/codigo` | Cambia la contraseña del usuario usando el código de recuperación |

**Body (JSON):**
```json
{
  "codigo": "483921",
  "contrasenia": "nuevacontrasenia123",
  "contrasenia2": "nuevacontrasenia123"
}
```

**Respuesta exitosa:**
```json
{
  "estatus": "success",
  "mensaje": "¡Contraseña cambiada!"
}
```

---

## 🔐 Flujo de Autenticación

```
[Cliente]
    │
    ├── POST /registroForm ──────► Crea usuario (status=0) ──► Envía correo con código
    │                                                                │
    ├── GET  /verificacionMail/{codigo} ◄────────────────────────────┘
    │        └── Activa cuenta (status=1)
    │
    ├── POST /verificarCredenciales ────► Retorna token de autenticación
    │
    └── Recuperación de contraseña:
         ├── POST /recuperarContrasenia ─► Envía código al correo
         ├── POST /codigo               ─► Valida el código
         └── POST /cambio/codigo        ─► Actualiza la contraseña
```

---

## ⚠️ Notas Importantes

- Los códigos de verificación y recuperación son numéricos de **6 dígitos** generados aleatoriamente.
- Las rutas de autenticación (`registroForm`, `verificarCredenciales`, `verificacionMail`, `recuperarContrasenia`, `codigo`, `cambio`) están **exentas de middleware de autenticación**.
- Las demás rutas requieren un token válido de autenticación (`auth:api`).
- Las contraseñas se almacenan hasheadas con `password_hash` usando `PASSWORD_DEFAULT`.

---

## 📧 Configuración de Correo (`.env`)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=tu.correo@gmail.com
MAIL_PASSWORD=tu_contrasena_de_aplicacion
MAIL_FROM_ADDRESS="tu.correo@gmail.com"
MAIL_FROM_NAME="Api Magic"
```

---

## 📜 Licencia

Este proyecto está bajo la licencia [MIT](https://opensource.org/licenses/MIT).
