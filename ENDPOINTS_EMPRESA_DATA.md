# 🏢 Endpoints de Datos por Empresa

## 📋 Nuevos Endpoints Creados

Se han creado 4 endpoints que filtran automáticamente los datos por la empresa del usuario autenticado:

### 🔐 Autenticación Requerida
Todos los endpoints requieren:
- Header: `Authorization: Bearer {token}`
- Usuario debe tener una sede asociada con empresa

---

## 📍 Endpoints Disponibles

### 1. Usuarios de Mi Empresa
**Endpoint:** `GET /api/auth/mi-empresa/usuarios`  
**Descripción:** Obtiene todos los usuarios que pertenecen a la misma empresa del usuario logueado

**Response:**
```json
{
  "message": "Usuarios de la empresa obtenidos exitosamente",
  "data": [
    {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@empresa.com",
      "telefono": "123456789",
      "sede_id": 1,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z",
      "sede": {
        "id": 1,
        "nombre": "Sede Principal",
        "direccion": "Calle 123",
        "empresa": {
          "id": 1,
          "nombre": "Empresa ABC",
          "nit": "123456789"
        }
      },
      "roles": [
        {
          "id": 2,
          "name": "Administrador",
          "guard_name": "api"
        }
      ]
    }
  ],
  "empresa_id": 1
}
```

### 2. Sedes de Mi Empresa
**Endpoint:** `GET /api/auth/mi-empresa/sedes`  
**Descripción:** Obtiene todas las sedes que pertenecen a la empresa del usuario logueado

**Response:**
```json
{
  "message": "Sedes de la empresa obtenidas exitosamente",
  "data": [
    {
      "id": 1,
      "nombre": "Sede Principal",
      "direccion": "Calle 123 #45-67",
      "telefono": "123456789",
      "email": "sede@empresa.com",
      "empresa_id": 1,
      "departamento_id": 1,
      "municipio_id": 1,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z",
      "empresa": {
        "id": 1,
        "nombre": "Empresa ABC",
        "nit": "123456789"
      },
      "departamento": {
        "id": 1,
        "nombre": "Cundinamarca"
      },
      "municipio": {
        "id": 1,
        "nombre": "Bogotá"
      }
    }
  ],
  "empresa_id": 1
}
```

### 3. Equipos de Mi Empresa
**Endpoint:** `GET /api/auth/mi-empresa/equipos`  
**Descripción:** Obtiene todos los equipos que están en sedes de la empresa del usuario logueado

**Response:**
```json
{
  "message": "Equipos de la empresa obtenidos exitosamente",
  "data": [
    {
      "id": 1,
      "serie": "EQ001",
      "marca": "Siemens",
      "modelo": "ABC123",
      "sede_id": 1,
      "tipo_equipo_id": 1,
      "clasificacion_biomedica_id": 1,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z",
      "sede": {
        "id": 1,
        "nombre": "Sede Principal",
        "empresa": {
          "id": 1,
          "nombre": "Empresa ABC"
        }
      },
      "tipoEquipo": {
        "id": 1,
        "nombre": "Rayos X"
      },
      "clasificacionBiomedica": {
        "id": 1,
        "nombre": "Clase IIa"
      }
    }
  ],
  "empresa_id": 1
}
```

### 4. Información General de Mi Empresa
**Endpoint:** `GET /api/auth/mi-empresa/info`  
**Descripción:** Obtiene información general y estadísticas de la empresa del usuario logueado

**Response:**
```json
{
  "message": "Información de la empresa obtenida exitosamente",
  "data": {
    "empresa": {
      "id": 1,
      "nombre": "Empresa ABC",
      "nit": "123456789",
      "logo": "logos/empresa-logo.png",
      "logo_url": "https://cst.test/storage/logos/empresa-logo.png",
      "tipo": "cliente"
    },
    "estadisticas": {
      "total_usuarios": 5,
      "total_sedes": 3,
      "total_equipos": 15
    }
  }
}
```

---

## 🔍 Casos de Uso

### Para Dashboards
```typescript
// Obtener estadísticas generales
const info = await this.http.get('/api/auth/mi-empresa/info').toPromise();
console.log(`Mi empresa tiene ${info.data.estadisticas.total_equipos} equipos`);
```

### Para Listas Filtradas
```typescript
// Obtener solo usuarios de mi empresa
const usuarios = await this.http.get('/api/auth/mi-empresa/usuarios').toPromise();

// Obtener solo sedes de mi empresa  
const sedes = await this.http.get('/api/auth/mi-empresa/sedes').toPromise();

// Obtener solo equipos de mi empresa
const equipos = await this.http.get('/api/auth/mi-empresa/equipos').toPromise();
```

### Para Selectores
```typescript
// Llenar un select con sedes de mi empresa
async loadSedes() {
  const response = await this.http.get('/api/auth/mi-empresa/sedes').toPromise();
  this.sedesOptions = response.data.map(sede => ({
    value: sede.id,
    label: sede.nombre
  }));
}
```

---

## ⚠️ Consideraciones de Seguridad

### Filtrado Automático
- Los datos se filtran automáticamente por empresa
- No es posible acceder a datos de otras empresas
- La validación se hace server-side basada en el usuario autenticado

### Validaciones
- Usuario debe estar autenticado
- Usuario debe tener sede asociada
- Sede debe tener empresa asociada
- Si no cumple condiciones, retorna array vacío

### Logs de Auditoría
Se recomienda implementar logging para:
- Accesos a datos de empresa
- Intentos de acceso sin empresa asociada
- Estadísticas de uso por empresa

---

## 🔧 Implementación en Frontend

### Service Angular
```typescript
@Injectable()
export class EmpresaDataService {
  private baseUrl = 'http://localhost:8000/api/auth/mi-empresa';

  constructor(private http: HttpClient) {}

  getUsuarios(): Observable<any> {
    return this.http.get(`${this.baseUrl}/usuarios`);
  }

  getSedes(): Observable<any> {
    return this.http.get(`${this.baseUrl}/sedes`);
  }

  getEquipos(): Observable<any> {
    return this.http.get(`${this.baseUrl}/equipos`);
  }

  getInfo(): Observable<any> {
    return this.http.get(`${this.baseUrl}/info`);
  }
}
```

### Componente Dashboard
```typescript
export class DashboardComponent implements OnInit {
  empresaInfo: any;
  usuarios: any[] = [];
  sedes: any[] = [];
  equipos: any[] = [];

  constructor(private empresaService: EmpresaDataService) {}

  async ngOnInit() {
    try {
      // Cargar información general
      const infoResponse = await this.empresaService.getInfo().toPromise();
      this.empresaInfo = infoResponse.data;

      // Cargar datos específicos
      const [usuariosRes, sedesRes, equiposRes] = await Promise.all([
        this.empresaService.getUsuarios().toPromise(),
        this.empresaService.getSedes().toPromise(),
        this.empresaService.getEquipos().toPromise()
      ]);

      this.usuarios = usuariosRes.data;
      this.sedes = sedesRes.data;
      this.equipos = equiposRes.data;

    } catch (error) {
      console.error('Error cargando datos de empresa:', error);
    }
  }
}
```

---

## 🧪 Testing

### Casos de Prueba

1. **Usuario con empresa asociada**
   - Debe retornar datos filtrados por empresa
   - Empresa_id debe coincidir en todos los endpoints

2. **Usuario sin sede asociada**
   - Debe retornar array vacío
   - Mensaje indicando que no tiene empresa asociada

3. **Usuario sin empresa en sede**
   - Debe retornar array vacío
   - Mensaje indicando que no tiene empresa asociada

### Commands de Prueba
```bash
# Probar endpoint de usuarios
curl -H "Authorization: Bearer {token}" \
     https://cst.test/api/auth/mi-empresa/usuarios

# Probar endpoint de sedes  
curl -H "Authorization: Bearer {token}" \
     https://cst.test/api/auth/mi-empresa/sedes

# Probar endpoint de equipos
curl -H "Authorization: Bearer {token}" \
     https://cst.test/api/auth/mi-empresa/equipos

# Probar información general
curl -H "Authorization: Bearer {token}" \
     https://cst.test/api/auth/mi-empresa/info
```

---

**Fecha de creación:** 2 de noviembre de 2025  
**Estado:** ✅ IMPLEMENTADO  
**Archivos creados:**
- `app/Http/Controllers/EmpresaDataController.php`
- Rutas agregadas en `routes/api.php`
