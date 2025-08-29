#!/bin/bash

# Script de Validación para AWS Personalize - PC Builder
# Este script valida que la estructura de datos coincida con el esquema requerido

set -e  # Salir si hay algún error

echo "🔍 Validando estructura de datos para AWS Personalize..."
echo "=================================================="

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    print_error "Este script debe ejecutarse desde el directorio raíz de Laravel"
    exit 1
fi

# Crear directorio temporal si no existe
mkdir -p temp_validation

# Exportar datos
print_info "Exportando datos..."
php artisan personalize:export items --output=temp_validation/items.csv
php artisan personalize:export users --output=temp_validation/users.csv
php artisan personalize:export interactions --output=temp_validation/interactions.csv

echo ""
print_info "Verificando estructura de archivos..."

# Verificar que los archivos existen
for file in items.csv users.csv interactions.csv; do
    if [ ! -f "temp_validation/$file" ]; then
        print_error "No se pudo crear el archivo $file"
        exit 1
    fi
done

print_success "Archivos exportados correctamente"

# Verificar headers
echo ""
print_info "Verificando headers de archivos..."

# Headers esperados
EXPECTED_ITEMS_HEADER="ITEM_ID,CATEGORIA,PRECIO,MARCA,RENDIMIENTO"
EXPECTED_USERS_HEADER="USER_ID,PREFERENCIAS"
EXPECTED_INTERACTIONS_HEADER="USER_ID,ITEM_ID,EVENT_TYPE,TIMESTAMP,PRESUPUESTO"

# Verificar headers
ACTUAL_ITEMS_HEADER=$(head -1 temp_validation/items.csv)
ACTUAL_USERS_HEADER=$(head -1 temp_validation/users.csv)
ACTUAL_INTERACTIONS_HEADER=$(head -1 temp_validation/interactions.csv)

if [ "$ACTUAL_ITEMS_HEADER" != "$EXPECTED_ITEMS_HEADER" ]; then
    print_error "Header incorrecto en items.csv"
    echo "Esperado: $EXPECTED_ITEMS_HEADER"
    echo "Actual:   $ACTUAL_ITEMS_HEADER"
    exit 1
fi

if [ "$ACTUAL_USERS_HEADER" != "$EXPECTED_USERS_HEADER" ]; then
    print_error "Header incorrecto en users.csv"
    echo "Esperado: $EXPECTED_USERS_HEADER"
    echo "Actual:   $ACTUAL_USERS_HEADER"
    exit 1
fi

if [ "$ACTUAL_INTERACTIONS_HEADER" != "$EXPECTED_INTERACTIONS_HEADER" ]; then
    print_error "Header incorrecto en interactions.csv"
    echo "Esperado: $EXPECTED_INTERACTIONS_HEADER"
    echo "Actual:   $ACTUAL_INTERACTIONS_HEADER"
    exit 1
fi

print_success "Headers correctos"

# Verificar duplicados
echo ""
print_info "Verificando duplicados..."

# Contar duplicados en items
ITEMS_DUPLICATES=$(tail -n +2 temp_validation/items.csv | cut -d',' -f1 | sort | uniq -d | wc -l)
if [ $ITEMS_DUPLICATES -gt 0 ]; then
    print_error "Se encontraron $ITEMS_DUPLICATES ITEM_IDs duplicados"
    tail -n +2 temp_validation/items.csv | cut -d',' -f1 | sort | uniq -d
    exit 1
fi

# Contar duplicados en users
USERS_DUPLICATES=$(tail -n +2 temp_validation/users.csv | cut -d',' -f1 | sort | uniq -d | wc -l)
if [ $USERS_DUPLICATES -gt 0 ]; then
    print_error "Se encontraron $USERS_DUPLICATES USER_IDs duplicados"
    tail -n +2 temp_validation/users.csv | cut -d',' -f1 | sort | uniq -d
    exit 1
fi

print_success "No hay duplicados"

# Verificar integridad referencial
echo ""
print_info "Verificando integridad referencial..."

# Crear listas de IDs válidos
tail -n +2 temp_validation/items.csv | cut -d',' -f1 | sort > temp_validation/valid_item_ids.txt
tail -n +2 temp_validation/users.csv | cut -d',' -f1 | sort > temp_validation/valid_user_ids.txt

# Verificar ITEM_IDs en interactions
INVALID_ITEMS=$(tail -n +2 temp_validation/interactions.csv | cut -d',' -f2 | sort | uniq | while read item_id; do
    if ! grep -q "^$item_id$" temp_validation/valid_item_ids.txt; then
        echo "$item_id"
    fi
done | wc -l)

if [ $INVALID_ITEMS -gt 0 ]; then
    print_error "Se encontraron $INVALID_ITEMS ITEM_IDs inválidos en interactions"
    tail -n +2 temp_validation/interactions.csv | cut -d',' -f2 | sort | uniq | while read item_id; do
        if ! grep -q "^$item_id$" temp_validation/valid_item_ids.txt; then
            echo "  - $item_id"
        fi
    done
    exit 1
fi

# Verificar USER_IDs en interactions
INVALID_USERS=$(tail -n +2 temp_validation/interactions.csv | cut -d',' -f1 | sort | uniq | while read user_id; do
    if ! grep -q "^$user_id$" temp_validation/valid_user_ids.txt; then
        echo "$user_id"
    fi
done | wc -l)

if [ $INVALID_USERS -gt 0 ]; then
    print_error "Se encontraron $INVALID_USERS USER_IDs inválidos en interactions"
    tail -n +2 temp_validation/interactions.csv | cut -d',' -f1 | sort | uniq | while read user_id; do
        if ! grep -q "^$user_id$" temp_validation/valid_user_ids.txt; then
            echo "  - $user_id"
        fi
    done
    exit 1
fi

print_success "Integridad referencial correcta"

# Verificar tipos de datos
echo ""
print_info "Verificando tipos de datos..."

# Verificar que PRECIO es numérico en items
INVALID_PRICES=$(tail -n +2 temp_validation/items.csv | cut -d',' -f3 | while read price; do
    if ! [[ "$price" =~ ^[0-9]+\.?[0-9]*$ ]] || (( $(echo "$price <= 0" | bc -l) )); then
        echo "precio_invalido: $price"
    fi
done | wc -l)

if [ $INVALID_PRICES -gt 0 ]; then
    print_error "Se encontraron $INVALID_PRICES precios inválidos en items"
    exit 1
fi

# Verificar que RENDIMIENTO es numérico y está en rango 1-10 en items
INVALID_PERFORMANCE=$(tail -n +2 temp_validation/items.csv | cut -d',' -f5 | while read perf; do
    if ! [[ "$perf" =~ ^[0-9]+\.?[0-9]*$ ]] || (( $(echo "$perf < 1 || $perf > 10" | bc -l) )); then
        echo "rendimiento_invalido: $perf"
    fi
done | wc -l)

if [ $INVALID_PERFORMANCE -gt 0 ]; then
    print_error "Se encontraron $INVALID_PERFORMANCE valores de rendimiento inválidos en items"
    exit 1
fi

# Verificar que TIMESTAMP es numérico en interactions
INVALID_TIMESTAMPS=$(tail -n +2 temp_validation/interactions.csv | cut -d',' -f4 | while read timestamp; do
    if ! [[ "$timestamp" =~ ^[0-9]+$ ]]; then
        echo "timestamp_invalido: $timestamp"
    fi
done | wc -l)

if [ $INVALID_TIMESTAMPS -gt 0 ]; then
    print_error "Se encontraron $INVALID_TIMESTAMPS timestamps inválidos en interactions"
    exit 1
fi

# Verificar que PRESUPUESTO es numérico en interactions
INVALID_BUDGETS=$(tail -n +2 temp_validation/interactions.csv | cut -d',' -f5 | while read budget; do
    if ! [[ "$budget" =~ ^[0-9]+\.?[0-9]*$ ]] || (( $(echo "$budget <= 0" | bc -l) )); then
        echo "presupuesto_invalido: $budget"
    fi
done | wc -l)

if [ $INVALID_BUDGETS -gt 0 ]; then
    print_error "Se encontraron $INVALID_BUDGETS presupuestos inválidos en interactions"
    exit 1
fi

print_success "Tipos de datos correctos"

# Verificar EVENT_TYPE en interactions
echo ""
print_info "Verificando tipos de eventos..."

INVALID_EVENTS=$(tail -n +2 temp_validation/interactions.csv | cut -d',' -f3 | while read event; do
    if [ "$event" != "PURCHASE" ]; then
        echo "evento_invalido: $event"
    fi
done | wc -l)

if [ $INVALID_EVENTS -gt 0 ]; then
    print_error "Se encontraron $INVALID_EVENTS tipos de evento inválidos en interactions"
    exit 1
fi

print_success "Tipos de eventos correctos"

# Estadísticas de datos
echo ""
print_info "Estadísticas de datos:"

TOTAL_ITEMS=$(tail -n +2 temp_validation/items.csv | wc -l)
TOTAL_USERS=$(tail -n +2 temp_validation/users.csv | wc -l)
TOTAL_INTERACTIONS=$(tail -n +2 temp_validation/interactions.csv | wc -l)

echo "  - Items (componentes): $TOTAL_ITEMS"
echo "  - Users (clientes): $TOTAL_USERS"
echo "  - Interactions (armados): $TOTAL_INTERACTIONS"

# Verificar requisitos mínimos
echo ""
print_info "Verificando requisitos mínimos..."

MIN_ITEMS=100
MIN_USERS=50
MIN_INTERACTIONS=1000

if [ $TOTAL_ITEMS -lt $MIN_ITEMS ]; then
    print_warning "Items insuficientes: $TOTAL_ITEMS (mínimo recomendado: $MIN_ITEMS)"
else
    print_success "Items suficientes: $TOTAL_ITEMS"
fi

if [ $TOTAL_USERS -lt $MIN_USERS ]; then
    print_warning "Usuarios insuficientes: $TOTAL_USERS (mínimo recomendado: $MIN_USERS)"
else
    print_success "Usuarios suficientes: $TOTAL_USERS"
fi

if [ $TOTAL_INTERACTIONS -lt $MIN_INTERACTIONS ]; then
    print_warning "Interacciones insuficientes: $TOTAL_INTERACTIONS (mínimo recomendado: $MIN_INTERACTIONS)"
else
    print_success "Interacciones suficientes: $TOTAL_INTERACTIONS"
fi

# Verificar distribución de datos
echo ""
print_info "Verificando distribución de datos..."

# Interacciones por usuario
AVG_INTERACTIONS_PER_USER=$(echo "scale=2; $TOTAL_INTERACTIONS / $TOTAL_USERS" | bc -l)
echo "  - Promedio de interacciones por usuario: $AVG_INTERACTIONS_PER_USER"

if (( $(echo "$AVG_INTERACTIONS_PER_USER < 5" | bc -l) )); then
    print_warning "Promedio de interacciones por usuario muy bajo (recomendado: 5-50)"
else
    print_success "Promedio de interacciones por usuario adecuado"
fi

# Interacciones por item
AVG_INTERACTIONS_PER_ITEM=$(echo "scale=2; $TOTAL_INTERACTIONS / $TOTAL_ITEMS" | bc -l)
echo "  - Promedio de interacciones por item: $AVG_INTERACTIONS_PER_ITEM"

if (( $(echo "$AVG_INTERACTIONS_PER_ITEM < 3" | bc -l) )); then
    print_warning "Promedio de interacciones por item muy bajo (recomendado: 3-100)"
else
    print_success "Promedio de interacciones por item adecuado"
fi

# Categorías únicas
UNIQUE_CATEGORIES=$(tail -n +2 temp_validation/items.csv | cut -d',' -f2 | sort | uniq | wc -l)
echo "  - Categorías únicas: $UNIQUE_CATEGORIES"

if [ $UNIQUE_CATEGORIES -lt 5 ]; then
    print_warning "Pocas categorías únicas (recomendado: al menos 5)"
else
    print_success "Categorías únicas suficientes"
fi

# Limpiar archivos temporales
echo ""
print_info "Limpiando archivos temporales..."
rm -rf temp_validation

echo ""
echo "=================================================="
print_success "Validación completada exitosamente"
print_success "Los datos están listos para importar a AWS Personalize"
echo ""
print_info "Próximos pasos:"
echo "  1. Subir los archivos CSV a S3"
echo "  2. Crear los trabajos de importación en AWS Personalize"
echo "  3. Esperar a que se complete la importación"
echo "  4. Crear la solución y campaña"
echo ""
print_info "Consulta el archivo SCHEMA_DEFINITION_AWS_PERSONALIZE.md para más detalles" 