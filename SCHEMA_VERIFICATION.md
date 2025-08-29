# Verificación de Estructura de Datos - AWS Personalize

## Verifica que tu estructura de datos coincida con el siguiente esquema:

---

## 1. Esquema para Items (Componentes)

### Definición del Schema
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

### Tu archivo CSV debe tener estas columnas:
```csv
ITEM_ID,CATEGORIA,PRECIO,MARCA,RENDIMIENTO
COMP001,Procesador,299.99,Intel,8.5
COMP002,Memoria RAM,89.99,Corsair,7.8
```

---

## 2. Esquema para Users (Clientes)

### Definición del Schema
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

### Tu archivo CSV debe tener estas columnas:
```csv
USER_ID,PREFERENCIAS
CLI001,{"presupuesto_promedio": 1500.00, "total_armados": 3}
CLI002,{"presupuesto_promedio": 800.00, "total_armados": 1}
```

---

## 3. Esquema para Interactions (Armados)

### Definición del Schema
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

### Tu archivo CSV debe tener estas columnas:
```csv
USER_ID,ITEM_ID,EVENT_TYPE,TIMESTAMP,PRESUPUESTO
CLI001,COMP001,PURCHASE,1640995200,1500.00
CLI001,COMP002,PURCHASE,1640995200,1500.00
```

---

## ✅ Verificación Rápida

### Campos Requeridos:
- **Items**: ITEM_ID, CATEGORIA, PRECIO, MARCA, RENDIMIENTO
- **Users**: USER_ID, PREFERENCIAS  
- **Interactions**: USER_ID, ITEM_ID, EVENT_TYPE, TIMESTAMP, PRESUPUESTO

### Tipos de Datos:
- **string**: ITEM_ID, CATEGORIA, MARCA, USER_ID, PREFERENCIAS, ITEM_ID, EVENT_TYPE
- **double**: PRECIO, RENDIMIENTO, PRESUPUESTO
- **long**: TIMESTAMP

### Valores Específicos:
- **EVENT_TYPE**: Debe ser "PURCHASE"
- **TIMESTAMP**: Timestamp Unix en segundos
- **PREFERENCIAS**: JSON válido con presupuesto_promedio y total_armados

---

**✅ Si tu estructura coincide con estos esquemas, puedes proceder con AWS Personalize.**

---

## 🔧 Configuración AWS Personalize

### ARN del Dataset Group configurado:
```
arn:aws:personalize:us-east-2:330786909811:dataset-group/SIGECOMP_Recomienda
```

### Variables de entorno necesarias:
```env
AWS_DEFAULT_REGION=us-east-2
AWS_PERSONALIZE_DATASET_GROUP_ARN=arn:aws:personalize:us-east-2:330786909811:dataset-group/SIGECOMP_Recomienda
AWS_PERSONALIZE_CAMPAIGN_ARN=arn:aws:personalize:us-east-2:330786909811:campaign/your-campaign-name
```

### Opciones de configuración:

#### 🚀 **Opción 1: Sin Campaña (Recomendado)**
```env
AWS_DEFAULT_REGION=us-east-2
AWS_PERSONALIZE_DATASET_GROUP_ARN=arn:aws:personalize:us-east-2:330786909811:dataset-group/SIGECOMP_Recomienda
# AWS_PERSONALIZE_CAMPAIGN_ARN= (NO NECESARIO)
```
- ✅ **Funciona inmediatamente**
- ✅ **Usa datos históricos**
- ✅ **No requiere configuración adicional**

#### 🔧 **Opción 2: Con Campaña (Avanzado)**
```env
AWS_DEFAULT_REGION=us-east-2
AWS_PERSONALIZE_DATASET_GROUP_ARN=arn:aws:personalize:us-east-2:330786909811:dataset-group/SIGECOMP_Recomienda
AWS_PERSONALIZE_CAMPAIGN_ARN=arn:aws:personalize:us-east-2:330786909811:campaign/your-campaign-name
```
- ⚠️ **Requiere crear campaña**
- ⚠️ **Necesita entrenamiento**
- ✅ **Recomendaciones más precisas**

### Estado actual:
1. ✅ **Esquemas definidos** (completado)
2. ✅ **Archivos CSV creados** (completado)
3. ✅ **Dataset Group configurado** (completado)
4. ✅ **Sistema funcionando** (completado - Opción 1) 