# Flujo de trabajo para probar la API con Postman

Este documento explica paso a paso cómo probar (en Postman) la autenticación y los endpoints de productos (`/api/products`) que expone `app/Http/Controllers/Api/ProductController.php`.

Resumen rápido
- Autenticación: recomiendo usar Laravel Sanctum (Personal Access Tokens) para API. Incluyo un snippet para crear un endpoint `/api/login` que devuelva un token si aún no lo tienes.
- Endpoints de productos (protegidos por `auth:sanctum`):
  - GET  /api/products
  - POST /api/products
  - GET  /api/products/{id}
  - PUT  /api/products/{id}
  - DELETE /api/products/{id}

Prerrequisitos
- Laravel corriendo en `http://localhost:8000` (ajusta `{{baseUrl}}` en Postman si es distinto).
- Migraciones ejecutadas y (opcional) seeders.
- Si usas Sanctum: instala y configura Sanctum, publica middleware y configura `SANCTUM_STATEFUL_DOMAINS` si usas SPA; para tokens personales no se requiere configuración adicional.

Añadir (si lo necesitas) un endpoint simple para obtener tokens
(Si aún no tienes un endpoint para login via API que devuelva token, añade este snippet a `routes/api.php`):

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if (! Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();
    // generar token personal (Sanctum)
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json(['token' => $token, 'user' => $user]);
});
```

Si no quieres modificar rutas, puedes crear tokens desde tinker (php artisan tinker) con:

```php
$user = \App\Models\User::where('email','tu@correo')->first();
echo $user->createToken('api-token')->plainTextToken;
```

Variables de Postman (recomendado)
- baseUrl = http://localhost:8000
- token = (se rellenará automáticamente al hacer login)

Flujo de pruebas en Postman (paso a paso)

1) Registro (Register)
- Método: POST
- URL: {{baseUrl}}/register
- Headers: Content-Type: application/json
- Body (raw JSON):
  {
    "name": "Test User",
    "email": "test@example.com",
    "password": "secret123",
    "password_confirmation": "secret123"
  }
- Respuesta esperada: 201/redirect/JSON dependiendo de cómo esté configurado Fortify/Jetstream. Si no devuelve token automáticamente, procede al login.

2) Login (obtener token)
- Método: POST
- URL: {{baseUrl}}/api/login
- Headers: Content-Type: application/json
- Body (raw JSON):
  {
    "email": "test@example.com",
    "password": "secret123"
  }
- Respuesta esperada (ejemplo):
  {
    "token": "1|abcde...",
    "user": { /* datos del usuario */ }
  }

Postman: guardar token automáticamente
- En la pestaña Tests de la request `/api/login` pega este script para guardar `token` en una variable de entorno llamada `token`:

```javascript
const body = pm.response.json();
if (body.token) pm.environment.set('token', body.token);
```

3) Listar productos (index)
- Método: GET
- URL: {{baseUrl}}/api/products?per_page=10
- Headers: Authorization: Bearer {{token}}  (o usa la Authorization tab en Postman: Bearer Token)
- Respuesta esperada: 200 OK con paginación. Formato:
  {
    "data": [ { "id":1, "name":"...", "price":12.5, "stock":10, ... }, ... ],
    "current_page": 1,
    "per_page": 10,
    "total": 25,
    ...
  }
- Notas: si el usuario no es admin solo verá sus propios productos; si es admin verá todos.

4) Crear producto (store)
- Método: POST
- URL: {{baseUrl}}/api/products
- Headers:
  - Authorization: Bearer {{token}}
  - Content-Type: application/json
- Body (raw JSON):
  {
    "name": "Zapatos de prueba",
    "price": 49.99,
    "stock": 12
  }
- Respuesta esperada: 201 Created
  {
    "message": "Product created successfully.",
    "product": { "id": 123, "name": "Zapatos de prueba", "price": 49.99, "stock": 12, ... }
  }
- Postman: en la pestaña Tests puedes extraer el id recién creado para usarlo en siguientes requests:

```javascript
const body = pm.response.json();
if (body.product && body.product.id) pm.environment.set('lastProductId', body.product.id);
```

5) Ver producto (show)
- Método: GET
- URL: {{baseUrl}}/api/products/{{lastProductId}}
- Headers: Authorization: Bearer {{token}}
- Respuesta esperada: 200 OK
  {
    "product": { "id": 123, "name": "...", "price": 49.99, "stock": 12, ... }
  }
- Errores comunes: 403 Forbidden (si el policy bloquea ver ese producto), 401 si token inválido.

6) Actualizar producto (update)
- Método: PUT
- URL: {{baseUrl}}/api/products/{{lastProductId}}
- Headers:
  - Authorization: Bearer {{token}}
  - Content-Type: application/json
- Body (raw JSON):
  {
    "name": "Zapato actualizado",
    "price": 59.99,
    "stock": 8
  }
- Respuesta esperada: 200 OK
  {
    "message": "Product updated successfully.",
    "product": { ...datos actualizados... }
  }

7) Eliminar producto (destroy)
- Método: DELETE
- URL: {{baseUrl}}/api/products/{{lastProductId}}
- Headers: Authorization: Bearer {{token}}
- Respuesta esperada: 204 No Content (el controlador actual devuelve un 204 con body, algunos clientes lo toleran). Si Postman muestra body vacío pero 204, considera cambiar el controller para devolver 200 con mensaje si prefieres ver un body.

Checks / edge cases
- 401: token faltante o inválido.
- 403: el policy impide ver/editar/borrar (por ejemplo: un usuario non-admin intentando borrar producto de otro usuario).
- 422: validation errors (campo faltante o formato incorrecto).

Notas sobre roles y admin
- El controlador API asume que el método `$user->isAdmin()` existe en `App\Models\User`.
- Para probar la experiencia admin en Postman, crea un usuario admin (puedes modificar en la DB manualmente o crear un seeder) y obtén su token.

Comandos útiles (terminal)
- Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

- Crear token con tinker (si prefieres no añadir rutas):

```bash
php artisan tinker
>>> $u = App\\Models\\User::where('email','test@example.com')->first();
>>> $u->createToken('api-token')->plainTextToken;
```

Plantillas de tests de Postman (scripts)
- Extraer token tras login (poner en Tests):
```javascript
const body = pm.response.json();
if (body.token) pm.environment.set('token', body.token);
```
- Extraer id tras crear producto (poner en Tests):
```javascript
const body = pm.response.json();
if (body.product && body.product.id) pm.environment.set('lastProductId', body.product.id);
```

Resumen final rápido
1. Asegura que tus rutas API estén registradas y protegidas con `auth:sanctum`.
2. Crea o registra un usuario.
3. Haz login y obtén `token` (o crea token con tinker).
4. Añade header `Authorization: Bearer {{token}}` en Postman.
5. Prueba CRUD sobre `/api/products`.

¿Quieres que yo también:
- registre las rutas en `routes/api.php` automáticamente?
- agregue el endpoint `/api/login` para generar tokens según el snippet anterior?
- añada un seeder para crear un usuario admin y algunos productos de ejemplo? 

Dime cuál de los tres quieres que haga y lo implemento ahora mismo.
