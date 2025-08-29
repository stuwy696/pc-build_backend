# Configuración Manual de AWS Personalize

## Datos Exportados ✅

Los siguientes archivos CSV han sido exportados exitosamente:

- `storage/app/personalize/items.csv` - Componentes disponibles
- `storage/app/personalize/users.csv` - Usuarios/clientes
- `storage/app/personalize/interactions.csv` - Historial de armados

## Pasos para Configurar AWS Personalize

### 1. Acceder a AWS Console

1. Ve a [AWS Console](https://console.aws.amazon.com/)
2. Inicia sesión con las credenciales:
   - **Access Key ID**: `[TU_ACCESS_KEY_ID]`
   - **Secret Access Key**: `[TU_SECRET_ACCESS_KEY]`

### 2. Navegar a AWS Personalize

1. En la consola de AWS, busca "Personalize"
2. Haz clic en "Amazon Personalize"
3. Asegúrate de estar en la región **us-east-1** (N. Virginia)

### 3. Crear Dataset Group

1. Haz clic en "Create dataset group"
2. Nombre: `PC-Builder-Dataset-Group`
3. Haz clic en "Next"

### 4. Crear Schemas

#### Schema para Items (Componentes)
1. Haz clic en "Create schema"
2. Nombre: `Items`
3. Schema JSON:
```json
{
  "type": "record",
  "name": "Items",
  "namespace": "com.amazonaws.personalize.schema",
  "fields": [
    {
      "name": "ITEM_ID",
      "type": "string"
    },
    {
      "name": "CATEGORIA",
      "type": "string"
    },
    {
      "name": "PRECIO",
      "type": "double"
    },
    {
      "name": "MARCA",
      "type": "string"
    },
    {
      "name": "RENDIMIENTO",
      "type": "double"
    }
  ],
  "version": "1.0"
}
```

#### Schema para Users (Clientes)
1. Haz clic en "Create schema"
2. Nombre: `Users`
3. Schema JSON:
```json
{
  "type": "record",
  "name": "Users",
  "namespace": "com.amazonaws.personalize.schema",
  "fields": [
    {
      "name": "USER_ID",
      "type": "string"
    },
    {
      "name": "PREFERENCIAS",
      "type": "string"
    }
  ],
  "version": "1.0"
}
```

#### Schema para Interactions (Armados)
1. Haz clic en "Create schema"
2. Nombre: `Interactions`
3. Schema JSON:
```json
{
  "type": "record",
  "name": "Interactions",
  "namespace": "com.amazonaws.personalize.schema",
  "fields": [
    {
      "name": "USER_ID",
      "type": "string"
    },
    {
      "name": "ITEM_ID",
      "type": "string"
    },
    {
      "name": "EVENT_TYPE",
      "type": "string"
    },
    {
      "name": "TIMESTAMP",
      "type": "long"
    },
    {
      "name": "PRESUPUESTO",
      "type": "double"
    }
  ],
  "version": "1.0"
}
```

### 5. Crear Datasets

#### Dataset de Items
1. Haz clic en "Create dataset"
2. Tipo: `Items`
3. Nombre: `Items`
4. Selecciona el schema `Items`
5. Haz clic en "Create"

#### Dataset de Users
1. Haz clic en "Create dataset"
2. Tipo: `Users`
3. Nombre: `Users`
4. Selecciona el schema `Users`
5. Haz clic en "Create"

#### Dataset de Interactions
1. Haz clic en "Create dataset"
2. Tipo: `Interactions`
3. Nombre: `Interactions`
4. Selecciona el schema `Interactions`
5. Haz clic en "Create"

### 6. Subir Datos a S3

1. Ve a [AWS S3 Console](https://console.aws.amazon.com/s3/)
2. Crea un bucket nuevo (ej: `pc-builder-personalize-data`)
3. Sube los archivos CSV:
   - `items.csv` → `s3://tu-bucket/items.csv`
   - `users.csv` → `s3://tu-bucket/users.csv`
   - `interactions.csv` → `s3://tu-bucket/interactions.csv`

### 7. Importar Datos

#### Importar Items
1. En Personalize, ve al dataset `Items`
2. Haz clic en "Import data"
3. Nombre del trabajo: `Import-Items`
4. Ubicación de datos: `s3://tu-bucket/items.csv`
5. Haz clic en "Create"

#### Importar Users
1. En Personalize, ve al dataset `Users`
2. Haz clic en "Import data"
3. Nombre del trabajo: `Import-Users`
4. Ubicación de datos: `s3://tu-bucket/users.csv`
5. Haz clic en "Create"

#### Importar Interactions
1. En Personalize, ve al dataset `Interactions`
2. Haz clic en "Import data"
3. Nombre del trabajo: `Import-Interactions`
4. Ubicación de datos: `s3://tu-bucket/interactions.csv`
5. Haz clic en "Create"

### 8. Crear Solución

1. Ve al dataset group
2. Haz clic en "Create solution"
3. Nombre: `PC-Builder-Solution`
4. Recipe: `aws-user-personalization`
5. Haz clic en "Create"

### 9. Crear Campaña

1. Una vez que la solución esté entrenada, haz clic en "Create campaign"
2. Nombre: `PC-Builder-Campaign`
3. Min TPS: `1`
4. Haz clic en "Create"

### 10. Obtener ARN de Campaña

1. Ve a la campaña creada
2. Copia el ARN de la campaña (ej: `arn:aws:personalize:us-east-1:123456789012:campaign/pc-builder-campaign`)

### 11. Actualizar Variables de Entorno

Actualiza el archivo `.env` con el ARN de la campaña:

```env
AWS_PERSONALIZE_CAMPAIGN_ARN=arn:aws:personalize:us-east-1:123456789012:campaign/pc-builder-campaign
```

### 12. Probar la Funcionalidad

1. Inicia el servidor Laravel: `php artisan serve`
2. Ve al frontend y prueba la funcionalidad de "Generar Armado Automático"

## Notas Importantes

- **Tiempo de Entrenamiento**: El entrenamiento puede tomar 1-2 horas
- **Datos Mínimos**: Se requieren al menos 1000 interacciones para recomendaciones efectivas
- **Costos**: AWS Personalize tiene costos asociados por uso
- **Región**: Asegúrate de usar `us-east-1` para consistencia

## Verificación

Para verificar que todo funciona:

1. Ve a la campaña en AWS Personalize
2. Estado debe ser "Active"
3. Prueba la funcionalidad en el frontend
4. Revisa los logs en CloudWatch si hay errores
