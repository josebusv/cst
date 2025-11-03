# 📖 Documentación de APIs para Frontend Angular

## 🔧 Configuración Base

### Base URL
```
http://localhost:8000/api
```

### Headers Requeridos
```typescript
const headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': 'Bearer ' + token // Solo para rutas protegidas
}
```

## 🔐 Autenticación

### 1. Login
**Endpoint:** `POST /auth/login`  
**Rate Limit:** 10 intentos por minuto

**Request:**
```typescript
{
  email: string,
  password: string
}
```

**Response Success (200):**
```typescript
{
  access_token: string,
  token_type: "bearer",
  expires_in: number,
  user: {
    id: number,
    name: string,
    email: string,
    empresa_id: number,
    created_at: string,
    updated_at: string,
    empresa: {
      id: number,
      nombre: string,
      nit: string,
      // ... otros campos
    },
    roles: [
      {
        id: number,
        name: string,
        guard_name: string
      }
    ],
    permissions: string[] // Array de nombres de permisos
  }
}
```

**Response Error (422):**
```typescript
{
  message: string,
  errors: {
    [field: string]: string[]
  }
}
```

### 2. Logout
**Endpoint:** `POST /auth/logout`  
**Headers:** Authorization requerido

**Response (200):**
```typescript
{
  message: "Successfully logged out"
}
```

### 3. Refresh Token
**Endpoint:** `POST /auth/refresh`  
**Headers:** Authorization requerido

**Response (200):**
```typescript
{
  access_token: string,
  token_type: "bearer",
  expires_in: number
}
```

### 4. Información del Usuario
**Endpoint:** `POST /auth/me`  
**Headers:** Authorization requerido

**Response (200):**
```typescript
{
  id: number,
  name: string,
  email: string,
  empresa_id: number,
  created_at: string,
  updated_at: string,
  empresa: Object,
  roles: Array,
  permissions: string[]
}
```

## 🔑 Recuperación de Contraseña

### 1. Solicitar Recuperación
**Endpoint:** `POST /auth/forgot-password`  
**Rate Limit:** 5 intentos por minuto

**Request:**
```typescript
{
  email: string
}
```

**Response (200):** *(Siempre igual por seguridad)*
```typescript
{
  message: "Si el correo existe en nuestro sistema, recibirás un enlace de recuperación."
}
```

### 2. Validar Token
**Endpoint:** `POST /auth/validate-token`  
**Rate Limit:** 10 intentos por minuto

**Request:**
```typescript
{
  token: string
}
```

**Response Success (200):**
```typescript
{
  message: "Token válido",
  valid: true
}
```

**Response Error (400):**
```typescript
{
  message: "Token inválido o expirado",
  valid: false
}
```

### 3. Restablecer Contraseña
**Endpoint:** `POST /auth/reset-password`  
**Rate Limit:** 5 intentos por minuto

**Request:**
```typescript
{
  email: string,
  password: string,
  password_confirmation: string,
  token: string
}
```

**Response Success (200):**
```typescript
{
  message: "Contraseña actualizada exitosamente"
}
```

## 👥 Gestión de Usuarios

### Listar Usuarios
**Endpoint:** `GET /auth/users`  
**Permisos:** `Listar Usuarios`

**Response (200):**
```typescript
{
  data: [
    {
      id: number,
      name: string,
      email: string,
      empresa_id: number,
      created_at: string,
      updated_at: string,
      empresa: Object,
      roles: Array
    }
  ]
}
```

### Crear Usuario
**Endpoint:** `POST /auth/users`  
**Permisos:** `Crear Usuarios`

**Request:**
```typescript
{
  name: string,
  email: string,
  password: string,
  password_confirmation: string,
  empresa_id: number,
  roles?: string[] // Array de nombres de roles
}
```

### Usuarios por Empresa
**Endpoint:** `GET /auth/users/empresa/{empresaId}`  
**Permisos:** `Listar Usuarios`

## 🏢 Gestión de Clientes

### Listar Clientes
**Endpoint:** `GET /auth/clientes`  
**Permisos:** `Listar Clientes` o `Ver Clientes`

### Crear Cliente
**Endpoint:** `POST /auth/clientes`  
**Permisos:** `Crear Clientes`

**Request:**
```typescript
{
  nombre: string,
  nit: string,
  telefono?: string,
  email?: string,
  direccion?: string,
  departamento_id: number,
  municipio_id: number
}
```

### Actualizar Cliente
**Endpoint:** `PUT /auth/clientes/{id}`  
**Permisos:** `Editar Clientes`

### Eliminar Cliente
**Endpoint:** `DELETE /auth/clientes/{id}`  
**Permisos:** `Eliminar Clientes`

## 🏛️ Gestión de Sedes

### Listar Sedes
**Endpoint:** `GET /auth/sedes`  
**Permisos:** `Listar Sedes`

### Crear Sede
**Endpoint:** `POST /auth/sedes`  
**Permisos:** `Crear Sedes`

**Request:**
```typescript
{
  nombre: string,
  direccion: string,
  telefono?: string,
  cliente_id: number,
  departamento_id: number,
  municipio_id: number
}
```

## 🔧 Gestión de Equipos

### Listar Equipos
**Endpoint:** `GET /auth/equipos`  
**Permisos:** `Listar Equipos`

### Crear Equipo
**Endpoint:** `POST /auth/equipos`  
**Permisos:** `Crear Equipos`

### Equipos por Empresa
**Endpoint:** `GET /auth/equipos/empresa/{empresaId}`  
**Permisos:** `Listar Equipos`

## 📊 Gestión de Reportes

### Listar Reportes
**Endpoint:** `GET /auth/reportes`  
**Permisos:** `Listar Reportes`

### Crear Reporte
**Endpoint:** `POST /auth/reportes`  
**Permisos:** `Crear Reportes`

### Reportes por Equipo
**Endpoint:** `GET /auth/reportes/equipo/{equipoId}`  
**Permisos:** `Listar Reportes`

## 🎭 Gestión de Roles (Solo Super-Admin)

### Listar Roles
**Endpoint:** `GET /auth/roles`  
**Middleware:** `role:Super-Admin`

### Crear Rol
**Endpoint:** `POST /auth/roles`  
**Middleware:** `role:Super-Admin`

### Registro de Usuarios (Solo Super-Admin)
**Endpoint:** `POST /auth/register`  
**Middleware:** `role:Super-Admin`

## 📋 Listas de Consulta

Todas las listas requieren autenticación pero tienen permisos específicos:

### Departamentos
**Endpoint:** `GET /auth/lista/departamentos`  
**Permisos:** `Listar Departamentos`

### Municipios
**Endpoint:** `GET /auth/lista/municipios/{departamento}`  
**Permisos:** `Listar Municipios`

### Clientes
**Endpoint:** `GET /auth/lista/clientes`  
**Permisos:** `Ver Clientes`

### Sedes por Cliente
**Endpoint:** `GET /auth/lista/sedes/{cliente}`  
**Permisos:** `Listar Sedes`

### Accesorios
**Endpoint:** `GET /auth/lista/accesorios`  
**Permisos:** `Listar Accesorios`

### Tipos de Equipos
**Endpoint:** `GET /auth/lista/tipos-equipos`  
**Permisos:** `Listar Tipos Equipos`

### Roles
**Endpoint:** `GET /auth/lista/roles`  
**Permisos:** `Listar Roles`

### Permisos
**Endpoint:** `GET /auth/lista/permisos`  
**Permisos:** `Listar Permisos`

## 🛡️ Sistema de Permisos

### Roles Disponibles

1. **Super-Admin**
   - Acceso completo al sistema
   - Puede crear otros usuarios y gestionar roles
   - Todos los permisos disponibles

2. **Administrador**
   - Gestión de usuarios, clientes, sedes y equipos
   - Ver reportes y hojas de vida
   - No puede gestionar roles

3. **Operador**
   - Gestión de equipos y reportes
   - Crear y firmar reportes
   - Ver información básica

4. **Cliente**
   - Solo consulta de información
   - Ver equipos y reportes propios
   - Imprimir reportes y hojas de vida

### Lista Completa de Permisos

**Usuarios:**
- Crear Usuarios
- Editar Usuarios
- Listar Usuarios
- Eliminar Usuarios

**Clientes:**
- Crear Clientes
- Editar Clientes
- Listar Clientes
- Eliminar Clientes
- Ver Clientes

**Sedes:**
- Crear Sedes
- Editar Sedes
- Listar Sedes
- Eliminar Sedes

**Equipos:**
- Crear Equipos
- Editar Equipos
- Listar Equipos
- Eliminar Equipos

**Reportes:**
- Crear Reportes
- Firmar Reportes
- Listar Reportes
- Imprimir Reportes

**Hoja de Vida:**
- Crear Hoja De Vida
- Ver Hoja De Vida
- Imprimir Hoja De Vida

**Roles y Permisos:**
- Crear Roles
- Editar Roles
- Listar Roles
- Eliminar Roles
- Listar Permisos
- Asignar Permisos

**Listas:**
- Listar Departamentos
- Listar Municipios
- Listar Accesorios
- Listar Tipos Equipos

## 🔒 Implementación de Seguridad en Angular

### Guard para Autenticación
```typescript
// auth.guard.ts
@Injectable()
export class AuthGuard implements CanActivate {
  constructor(private auth: AuthService, private router: Router) {}

  canActivate(): boolean {
    if (this.auth.isAuthenticated()) {
      return true;
    }
    this.router.navigate(['/login']);
    return false;
  }
}
```

### Guard para Permisos
```typescript
// permission.guard.ts
@Injectable()
export class PermissionGuard implements CanActivate {
  constructor(private auth: AuthService, private router: Router) {}

  canActivate(route: ActivatedRouteSnapshot): boolean {
    const requiredPermission = route.data['permission'];
    
    if (this.auth.hasPermission(requiredPermission)) {
      return true;
    }
    
    this.router.navigate(['/unauthorized']);
    return false;
  }
}
```

### Service de Autenticación
```typescript
// auth.service.ts
@Injectable()
export class AuthService {
  private baseUrl = 'http://localhost:8000/api';
  private tokenKey = 'auth_token';
  
  constructor(private http: HttpClient) {}

  login(credentials: LoginCredentials): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.baseUrl}/auth/login`, credentials)
      .pipe(
        tap(response => {
          localStorage.setItem(this.tokenKey, response.access_token);
          localStorage.setItem('user', JSON.stringify(response.user));
        })
      );
  }

  logout(): Observable<any> {
    return this.http.post(`${this.baseUrl}/auth/logout`, {})
      .pipe(
        tap(() => {
          localStorage.removeItem(this.tokenKey);
          localStorage.removeItem('user');
        })
      );
  }

  getToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  getUser(): User | null {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }

  hasPermission(permission: string): boolean {
    const user = this.getUser();
    return user?.permissions?.includes(permission) || false;
  }

  hasRole(role: string): boolean {
    const user = this.getUser();
    return user?.roles?.some(r => r.name === role) || false;
  }
}
```

### Interceptor para Headers
```typescript
// auth.interceptor.ts
@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private auth: AuthService) {}

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = this.auth.getToken();
    
    if (token) {
      const authReq = req.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });
      return next.handle(authReq);
    }
    
    return next.handle(req);
  }
}
```

### Configuración de Rutas
```typescript
// app-routing.module.ts
const routes: Routes = [
  {
    path: 'auth/reset-password',
    component: ResetPasswordComponent
  },
  {
    path: 'users',
    component: UsersComponent,
    canActivate: [AuthGuard, PermissionGuard],
    data: { permission: 'Listar Usuarios' }
  },
  {
    path: 'clientes',
    component: ClientesComponent,
    canActivate: [AuthGuard, PermissionGuard],
    data: { permission: 'Ver Clientes' }
  },
  // ... más rutas
];
```

## ⚠️ Consideraciones Importantes

### Rate Limiting
- **Login:** 10 intentos por minuto
- **Password Reset:** 5 intentos por minuto
- **Token Validation:** 10 intentos por minuto

### Manejo de Errores
```typescript
// Estructura estándar de errores
{
  message: string,
  errors?: {
    [field: string]: string[]
  }
}
```

### Headers de Respuesta
Todas las respuestas incluyen:
```
Content-Type: application/json
Access-Control-Allow-Origin: *
```

### Tokens JWT
- **Expiración:** Configurada en el backend
- **Refresh:** Usar endpoint `/auth/refresh`
- **Storage:** Recomendado localStorage para desarrollo

## 🚀 Ejemplo de Implementación

### Componente de Login
```typescript
// login.component.ts
export class LoginComponent {
  loginForm: FormGroup;

  constructor(
    private fb: FormBuilder,
    private auth: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onSubmit() {
    if (this.loginForm.valid) {
      this.auth.login(this.loginForm.value).subscribe({
        next: (response) => {
          console.log('Login exitoso:', response);
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          console.error('Error en login:', error);
          // Manejar errores
        }
      });
    }
  }
}
```

### Componente de Reset Password
```typescript
// reset-password.component.ts
export class ResetPasswordComponent implements OnInit {
  resetForm: FormGroup;
  token: string;
  email: string;

  constructor(
    private route: ActivatedRoute,
    private fb: FormBuilder,
    private http: HttpClient,
    private router: Router
  ) {
    this.resetForm = this.fb.group({
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', [Validators.required]]
    });
  }

  ngOnInit() {
    this.token = this.route.snapshot.queryParams['token'];
    this.email = this.route.snapshot.queryParams['email'];
    
    if (!this.token || !this.email) {
      this.router.navigate(['/login']);
    }
  }

  onSubmit() {
    if (this.resetForm.valid) {
      const data = {
        ...this.resetForm.value,
        token: this.token,
        email: this.email
      };

      this.http.post('http://localhost:8000/api/auth/reset-password', data)
        .subscribe({
          next: (response) => {
            console.log('Contraseña actualizada');
            this.router.navigate(['/login']);
          },
          error: (error) => {
            console.error('Error:', error);
          }
        });
    }
  }
}
```

---

## 📞 Contacto y Soporte

Para dudas sobre la implementación o problemas con las APIs, contacta al equipo de desarrollo backend.

**Última actualización:** 2 de noviembre de 2025
