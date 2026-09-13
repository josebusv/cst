# ADR 0001: Caché de catálogos con versionado e invalidación por observers

- **Estado:** Aceptado
- **Contexto:** `ListaController` sirve catálogos (clientes, sedes, empresas, técnicos, etc.)
  usados al abrir formularios. Sin caché, cada apertura golpeaba la BD; con caché simple,
  los listados quedaban obsoletos tras crear/editar.
- **Decisión:** Cachear las listas en `ListaController` mediante `App\Support\CatalogoCache`,
  que prefija las claves con una versión global (`lista.version`). Un
  `CatalogoObserver` (Empresa, Sede, User, Role, Permission, catálogos) incrementa la
  versión al guardar o eliminar, invalidando todas las claves de una vez. El TTL actúa
  como respaldo.
- **Consecuencias:**
  - Positivas: lecturas de catálogo sin golpear BD; datos frescos tras cualquier escritura.
  - Negativas: cualquier escritura en esas entidades invalida todo el grupo (asumible por
    su baja frecuencia). Con driver `file` no hay tags; el versionado los sustituye.
