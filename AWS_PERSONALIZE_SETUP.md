# Configuración de AWS Personalize para PC Builder

Este documento explica cómo configurar AWS Personalize para generar armados automáticos de PC usando inteligencia artificial.

**⚠️ IMPORTANTE**: Este sistema funciona **EXCLUSIVAMENTE** con AWS Personalize. No hay sistema de fallback disponible.

## Prerrequisitos

1. Cuenta de AWS con acceso a Personalize
2. AWS CLI configurado
3. Permisos para crear y gestionar recursos de Personalize

## Configuración Inicial

### 1. Variables de Entorno

Crea un archivo `.env` en la raíz del proyecto con las siguientes variables:

```env
# AWS Configuration
AWS_ACCESS_KEY_ID=tu_access_key_id
AWS_SECRET_ACCESS_KEY=tu_secret_access_key
AWS_DEFAULT_REGION=us-east-1

# AWS Personalize Configuration
AWS_PERSONALIZE_CAMPAIGN_ARN=arn:aws:personalize:region:account:campaign/campaign-name
AWS_PERSONALIZE_LOGGING=true
AWS_PERSONALIZE_LOG_LEVEL=info

# AWS S3 Configuration (opcional, para almacenar datos de Personalize)
AWS_S3_BUCKET=your-personalize-data-bucket
AWS_S3_REGION=us-east-1
```

### 2. Instalar Dependencias

```bash
composer install
```

## Configuración de AWS Personalize

### 1. Crear Dataset Group

```bash
aws personalize create-dataset-group --name "PC-Builder-Dataset-Group"
```

### 2. Crear Schemas

#### Schema para Items (Componentes)

```bash
aws personalize create-schema \
--name "Items" \
--schema '{
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
}'
```

#### Schema para Users (Clientes)

```bash
aws personalize create-schema \
--name "Users" \
--schema '{
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
}'
```

#### Schema para Interactions (Armados)

```bash
aws personalize create-schema \
--name "Interactions" \
--schema '{
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
}'
```

### 3. Crear Datasets

```bash
aws personalize create-dataset \
--dataset-group-arn arn:aws:personalize:region:account:dataset-group/PC-Builder-Dataset-Group \
--dataset-type ITEMS \
--name "Items" \
--schema-arn arn:aws:personalize:region:account:schema/Items

aws personalize create-dataset \
--dataset-group-arn arn:aws:personalize:region:account:dataset-group/PC-Builder-Dataset-Group \
--dataset-type USERS \
--name "Users" \
--schema-arn arn:aws:personalize:region:account:schema/Users

aws personalize create-dataset \
--dataset-group-arn arn:aws:personalize:region:account:dataset-group/PC-Builder-Dataset-Group \
--dataset-type INTERACTIONS \
--name "Interactions" \
--schema-arn arn:aws:personalize:region:account:schema/Interactions
```

### 4. Importar Datos

#### Subir archivos CSV a S3

```bash
aws s3 cp items.csv s3://tu-bucket/personalize/items.csv
aws s3 cp users.csv s3://tu-bucket/personalize/users.csv
aws s3 cp interactions.csv s3://tu-bucket/personalize/interactions.csv
```

#### Crear trabajos de importación

```bash
aws personalize create-dataset-import-job \
--job-name "Import-Items" \
--dataset-arn arn:aws:personalize:region:account:dataset/Items \
--data-source dataLocation=s3://tu-bucket/personalize/items.csv \
--role-arn arn:aws:iam::account:role/PersonalizeRole

aws personalize create-dataset-import-job \
--job-name "Import-Users" \
--dataset-arn arn:aws:personalize:region:account:dataset/Users \
--data-source dataLocation=s3://tu-bucket/personalize/users.csv \
--role-arn arn:aws:iam::account:role/PersonalizeRole

aws personalize create-dataset-import-job \
--job-name "Import-Interactions" \
--dataset-arn arn:aws:personalize:region:account:dataset/Interactions \
--data-source dataLocation=s3://tu-bucket/personalize/interactions.csv \
--role-arn arn:aws:iam::account:role/PersonalizeRole
```

### 5. Crear Solución y Campaña

```bash
aws personalize create-solution \
--name "PC-Builder-Solution" \
--dataset-group-arn arn:aws:personalize:region:account:dataset-group/PC-Builder-Dataset-Group \
--recipe-arn arn:aws:personalize:::recipe/aws-user-personalization

aws personalize create-campaign \
--name "PC-Builder-Campaign" \
--solution-version-arn arn:aws:personalize:region:account:solution/PC-Builder-Solution \
--min-provisioned-tps 1
```

## Exportar Datos

### Usar el comando Artisan

```bash
# Exportar componentes
php artisan personalize:export items --output=storage/app/personalize/items.csv

# Exportar usuarios
php artisan personalize:export users --output=storage/app/personalize/users.csv

# Exportar interacciones
php artisan personalize:export interactions --output=storage/app/personalize/interactions.csv
```

### Usar el script de configuración

```bash
chmod +x setup-aws-personalize.sh
./setup-aws-personalize.sh
```

## Verificar Configuración

### 1. Verificar que la campaña esté activa

```bash
aws personalize describe-campaign --campaign-arn arn:aws:personalize:region:account:campaign/PC-Builder-Campaign
```

### 2. Verificar logs

Los logs de Personalize se pueden monitorear en CloudWatch:

```bash
aws logs describe-log-groups --log-group-name-prefix "/aws/personalize"
```

## Solución de Problemas

### Error: "AWS Personalize no está configurado"

- Verifica que `AWS_PERSONALIZE_CAMPAIGN_ARN` esté configurado en `.env`
- Asegúrate de que el ARN sea válido y la campaña esté activa

### Error: "AWS Personalize no pudo generar recomendaciones"

- Verifica que tu campaña tenga datos suficientes
- Asegúrate de que los datasets estén importados correctamente
- Verifica `AWS_ACCESS_KEY_ID` y `AWS_SECRET_ACCESS_KEY`

### Error: "Campaña no activa"

- Verifica el estado de la campaña con `describe-campaign`
- Espera a que la solución termine de entrenarse

## Notas Importantes

⚠️ **SIN SISTEMA DE FALLBACK**: Este sistema funciona exclusivamente con AWS Personalize. Si AWS Personalize no está disponible o configurado correctamente, la funcionalidad de armado automático no funcionará.

🔧 **Configuración Requerida**: Es obligatorio configurar todas las variables de entorno y recursos de AWS Personalize antes de usar la funcionalidad.

📊 **Datos Mínimos**: Se requieren al menos 1000 interacciones para que AWS Personalize genere recomendaciones efectivas.

💰 **Costos**: AWS Personalize tiene costos asociados. Consulta la documentación oficial de AWS para más detalles. 