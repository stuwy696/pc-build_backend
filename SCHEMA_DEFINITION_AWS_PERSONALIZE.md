# Definición del Esquema para AWS Personalize - PC Builder

## Verificación de Estructura de Datos

**⚠️ IMPORTANTE**: Verifica que tu estructura de datos coincida exactamente con los siguientes esquemas antes de importar datos a AWS Personalize.

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

### Estructura de Datos Requerida

Tu archivo CSV de componentes debe tener exactamente estas columnas:

| Campo | Tipo | Descripción | Requerido | Ejemplo |
|-------|------|-------------|-----------|---------|
| `ITEM_ID` | string | ID único del componente | ✅ | "COMP001" |
| `CATEGORIA` | string | Categoría del componente | ✅ | "Procesador" |
| `PRECIO` | double | Precio del componente | ✅ | 299.99 |
| `MARCA` | string | Marca del componente | ❌ | "Intel" |
| `RENDIMIENTO` | double | Puntuación de rendimiento (1-10) | ❌ | 8.5 |

### Ejemplo de CSV de Items
```csv
ITEM_ID,CATEGORIA,PRECIO,MARCA,RENDIMIENTO
COMP001,Procesador,299.99,Intel,8.5
COMP002,Memoria RAM,89.99,Corsair,7.8
COMP003,Tarjeta Gráfica,599.99,NVIDIA,9.2
COMP004,Disco Duro,129.99,Seagate,6.5
COMP005,Placa Base,199.99,ASUS,8.0
```

### Verificación de Datos
- ✅ `ITEM_ID` debe ser único para cada componente
- ✅ `CATEGORIA` debe ser uno de: "Procesador", "Memoria RAM", "Tarjeta Gráfica", "Disco Duro", "Placa Base", "Fuente de Poder", "Gabinete", "Monitor", "Teclado", "Mouse"
- ✅ `PRECIO` debe ser un número decimal positivo
- ✅ `MARCA` puede estar vacío (se usará "Unknown")
- ✅ `RENDIMIENTO` debe estar entre 1.0 y 10.0 (se usará 5.0 por defecto)

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

### Estructura de Datos Requerida

Tu archivo CSV de usuarios debe tener exactamente estas columnas:

| Campo | Tipo | Descripción | Requerido | Ejemplo |
|-------|------|-------------|-----------|---------|
| `USER_ID` | string | ID único del cliente | ✅ | "CLI001" |
| `PREFERENCIAS` | string | JSON con preferencias del usuario | ❌ | `{"presupuesto_promedio": 1500.00, "total_armados": 3}` |

### Ejemplo de CSV de Users
```csv
USER_ID,PREFERENCIAS
CLI001,{"presupuesto_promedio": 1500.00, "total_armados": 3}
CLI002,{"presupuesto_promedio": 800.00, "total_armados": 1}
CLI003,{"presupuesto_promedio": 2500.00, "total_armados": 5}
CLI004,{"presupuesto_promedio": 1200.00, "total_armados": 2}
CLI005,{"presupuesto_promedio": 3000.00, "total_armados": 8}
```

### Verificación de Datos
- ✅ `USER_ID` debe ser único para cada cliente
- ✅ `PREFERENCIAS` debe ser un JSON válido con:
  - `presupuesto_promedio`: número decimal (se calcula automáticamente)
  - `total_armados`: número entero (se calcula automáticamente)
- ✅ Si `PREFERENCIAS` está vacío, se usará: `{"presupuesto_promedio": 1000.00, "total_armados": 0}`

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

### Estructura de Datos Requerida

Tu archivo CSV de interacciones debe tener exactamente estas columnas:

| Campo | Tipo | Descripción | Requerido | Ejemplo |
|-------|------|-------------|-----------|---------|
| `USER_ID` | string | ID del cliente | ✅ | "CLI001" |
| `ITEM_ID` | string | ID del componente | ✅ | "COMP001" |
| `EVENT_TYPE` | string | Tipo de evento | ✅ | "PURCHASE" |
| `TIMESTAMP` | long | Timestamp en segundos | ✅ | 1640995200 |
| `PRESUPUESTO` | double | Presupuesto del armado | ✅ | 1500.00 |

### Ejemplo de CSV de Interactions
```csv
USER_ID,ITEM_ID,EVENT_TYPE,TIMESTAMP,PRESUPUESTO
CLI001,COMP001,PURCHASE,1640995200,1500.00
CLI001,COMP002,PURCHASE,1640995200,1500.00
CLI001,COMP003,PURCHASE,1640995200,1500.00
CLI002,COMP004,PURCHASE,1641081600,800.00
CLI002,COMP005,PURCHASE,1641081600,800.00
CLI003,COMP001,PURCHASE,1641168000,2500.00
CLI003,COMP003,PURCHASE,1641168000,2500.00
```

### Verificación de Datos
- ✅ `USER_ID` debe existir en el dataset de Users
- ✅ `ITEM_ID` debe existir en el dataset de Items
- ✅ `EVENT_TYPE` debe ser "PURCHASE" (para armados completados)
- ✅ `TIMESTAMP` debe ser un timestamp Unix en segundos
- ✅ `PRESUPUESTO` debe ser el presupuesto total del armado (mismo valor para todos los componentes del mismo armado)

---

## 4. Comandos de Verificación

### Verificar Estructura de Datos
```bash
# Verificar componentes
php artisan personalize:export items --output=temp_items.csv
head -5 temp_items.csv

# Verificar usuarios
php artisan personalize:export users --output=temp_users.csv
head -5 temp_users.csv

# Verificar interacciones
php artisan personalize:export interactions --output=temp_interactions.csv
head -5 temp_interactions.csv
```

### Verificar Integridad de Datos
```bash
# Verificar que no hay IDs duplicados en items
cut -d',' -f1 temp_items.csv | sort | uniq -d

# Verificar que no hay IDs duplicados en users
cut -d',' -f1 temp_users.csv | sort | uniq -d

# Verificar que todos los USER_ID en interactions existen en users
cut -d',' -f1 temp_interactions.csv | sort | uniq | while read user_id; do
  if ! grep -q "^$user_id," temp_users.csv; then
    echo "USER_ID no encontrado: $user_id"
  fi
done

# Verificar que todos los ITEM_ID en interactions existen en items
cut -d',' -f2 temp_interactions.csv | sort | uniq | while read item_id; do
  if ! grep -q "^$item_id," temp_items.csv; then
    echo "ITEM_ID no encontrado: $item_id"
  fi
done
```

---

## 5. Requisitos Mínimos de Datos

Para que AWS Personalize funcione correctamente, necesitas:

### Cantidad Mínima de Datos
- **Items**: Al menos 100 componentes diferentes
- **Users**: Al menos 50 clientes diferentes
- **Interactions**: Al menos 1000 interacciones totales

### Distribución Recomendada
- **Interacciones por usuario**: Mínimo 5, recomendado 10-50
- **Interacciones por item**: Mínimo 3, recomendado 10-100
- **Categorías**: Al menos 5 categorías diferentes de componentes

### Ejemplo de Distribución Mínima
```
Items: 100 componentes
Users: 50 clientes
Interactions: 1000 interacciones
- Promedio: 20 interacciones por cliente
- Mínimo: 5 interacciones por cliente
- Máximo: 50 interacciones por cliente
```

---

## 6. Errores Comunes y Soluciones

### Error: "Invalid schema format"
- **Causa**: El JSON del schema no es válido
- **Solución**: Verifica la sintaxis JSON y los tipos de datos

### Error: "Missing required field"
- **Causa**: Falta un campo requerido en el CSV
- **Solución**: Asegúrate de que todas las columnas requeridas estén presentes

### Error: "Invalid data type"
- **Causa**: Los datos no coinciden con el tipo especificado
- **Solución**: Verifica que los números sean decimales y las fechas sean timestamps

### Error: "Duplicate ITEM_ID"
- **Causa**: Hay IDs duplicados en el dataset de items
- **Solución**: Asegúrate de que cada componente tenga un ID único

### Error: "Referential integrity violation"
- **Causa**: USER_ID o ITEM_ID en interactions no existe en sus respectivos datasets
- **Solución**: Verifica que todos los IDs referenciados existan

---

## 7. Script de Validación Automática

Crea un script para validar automáticamente tus datos:

```bash
#!/bin/bash

echo "Validando estructura de datos para AWS Personalize..."

# Exportar datos
php artisan personalize:export items --output=temp_items.csv
php artisan personalize:export users --output=temp_users.csv
php artisan personalize:export interactions --output=temp_interactions.csv

# Verificar headers
echo "Verificando headers..."

# Items
if ! head -1 temp_items.csv | grep -q "ITEM_ID,CATEGORIA,PRECIO,MARCA,RENDIMIENTO"; then
    echo "❌ Error: Headers incorrectos en items.csv"
    exit 1
fi

# Users
if ! head -1 temp_users.csv | grep -q "USER_ID,PREFERENCIAS"; then
    echo "❌ Error: Headers incorrectos en users.csv"
    exit 1
fi

# Interactions
if ! head -1 temp_interactions.csv | grep -q "USER_ID,ITEM_ID,EVENT_TYPE,TIMESTAMP,PRESUPUESTO"; then
    echo "❌ Error: Headers incorrectos en interactions.csv"
    exit 1
fi

echo "✅ Headers correctos"

# Verificar duplicados
echo "Verificando duplicados..."

# Items
duplicates=$(cut -d',' -f1 temp_items.csv | sort | uniq -d | wc -l)
if [ $duplicates -gt 0 ]; then
    echo "❌ Error: $duplicates ITEM_IDs duplicados encontrados"
    exit 1
fi

# Users
duplicates=$(cut -d',' -f1 temp_users.csv | sort | uniq -d | wc -l)
if [ $duplicates -gt 0 ]; then
    echo "❌ Error: $duplicates USER_IDs duplicados encontrados"
    exit 1
fi

echo "✅ No hay duplicados"

# Verificar integridad referencial
echo "Verificando integridad referencial..."

# Crear listas de IDs válidos
cut -d',' -f1 temp_items.csv | sort > valid_item_ids.txt
cut -d',' -f1 temp_users.csv | sort > valid_user_ids.txt

# Verificar ITEM_IDs en interactions
invalid_items=$(cut -d',' -f2 temp_interactions.csv | sort | uniq | while read item_id; do
    if ! grep -q "^$item_id$" valid_item_ids.txt; then
        echo "$item_id"
    fi
done | wc -l)

if [ $invalid_items -gt 0 ]; then
    echo "❌ Error: $invalid_items ITEM_IDs inválidos en interactions"
    exit 1
fi

# Verificar USER_IDs en interactions
invalid_users=$(cut -d',' -f1 temp_interactions.csv | sort | uniq | while read user_id; do
    if ! grep -q "^$user_id$" valid_user_ids.txt; then
        echo "$user_id"
    fi
done | wc -l)

if [ $invalid_users -gt 0 ]; then
    echo "❌ Error: $invalid_users USER_IDs inválidos en interactions"
    exit 1
fi

echo "✅ Integridad referencial correcta"

# Limpiar archivos temporales
rm temp_items.csv temp_users.csv temp_interactions.csv valid_item_ids.txt valid_user_ids.txt

echo "✅ Validación completada exitosamente"
echo "Los datos están listos para importar a AWS Personalize"
```

---

## 8. Notas Importantes

### Orden de Importación
1. **Items** (componentes)
2. **Users** (clientes)
3. **Interactions** (armados)

### Formato de Archivos
- **Encoding**: UTF-8
- **Separador**: Coma (,)
- **Delimitador de texto**: Comillas dobles (") si es necesario
- **Saltos de línea**: Unix (LF)

### Límites de AWS Personalize
- **Tamaño máximo de archivo**: 1GB por archivo
- **Número máximo de campos**: 100 por schema
- **Longitud máxima de campo**: 1000 caracteres
- **Número máximo de items**: 10 millones
- **Número máximo de usuarios**: 10 millones
- **Número máximo de interacciones**: 100 millones

### Mejores Prácticas
- ✅ Usa IDs consistentes y únicos
- ✅ Mantén los datos actualizados regularmente
- ✅ Valida los datos antes de importar
- ✅ Monitorea el rendimiento de las recomendaciones
- ✅ Limpia datos obsoletos periódicamente

---

**✅ Si tu estructura de datos coincide con estos esquemas, estás listo para importar a AWS Personalize.** 