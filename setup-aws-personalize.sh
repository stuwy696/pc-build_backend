#!/bin/bash

# Script de configuración para AWS Personalize en PC Builder
# Este script ayuda a configurar AWS Personalize para la funcionalidad de armado automático

echo "🚀 Configurando AWS Personalize para PC Builder"
echo "================================================"

# Verificar si AWS CLI está instalado
if ! command -v aws &> /dev/null; then
    echo "❌ AWS CLI no está instalado. Por favor, instálalo primero:"
    echo "   https://docs.aws.amazon.com/cli/latest/userguide/getting-started-install.html"
    exit 1
fi

# Verificar si está configurado AWS CLI
if ! aws sts get-caller-identity &> /dev/null; then
    echo "❌ AWS CLI no está configurado. Ejecuta 'aws configure' primero."
    exit 1
fi

echo "✅ AWS CLI está configurado correctamente"

# Instalar dependencias de PHP
echo "📦 Instalando dependencias de PHP..."
composer require aws/aws-sdk-php

# Crear archivo de configuración si no existe
if [ ! -f "config/aws-personalize.php" ]; then
    echo "📝 Creando archivo de configuración..."
    cp config/aws-personalize.php.example config/aws-personalize.php 2>/dev/null || echo "⚠️  Archivo de configuración ya existe"
fi

# Solicitar variables de entorno
echo ""
echo "🔧 Configuración de variables de entorno"
echo "========================================"

read -p "AWS Access Key ID: " aws_access_key
read -s -p "AWS Secret Access Key: " aws_secret_key
echo ""
read -p "AWS Region (default: us-east-1): " aws_region
aws_region=${aws_region:-us-east-1}

read -p "AWS Personalize Campaign ARN: " campaign_arn

# Crear archivo .env si no existe
if [ ! -f ".env" ]; then
    echo "📝 Creando archivo .env..."
    cp .env.example .env
fi

# Agregar variables de AWS al .env
echo "" >> .env
echo "# AWS Configuration" >> .env
echo "AWS_ACCESS_KEY_ID=$aws_access_key" >> .env
echo "AWS_SECRET_ACCESS_KEY=$aws_secret_key" >> .env
echo "AWS_DEFAULT_REGION=$aws_region" >> .env
echo "" >> .env
echo "# AWS Personalize Configuration" >> .env
echo "AWS_PERSONALIZE_CAMPAIGN_ARN=$campaign_arn" >> .env
echo "AWS_PERSONALIZE_LOGGING=true" >> .env
echo "AWS_PERSONALIZE_LOG_LEVEL=info" >> .env

echo "✅ Variables de entorno configuradas"

# Exportar datos para Personalize
echo ""
echo "📊 Exportando datos para AWS Personalize..."
echo "==========================================="

# Crear directorio para datos exportados
mkdir -p storage/app/personalize

# Exportar componentes
echo "📦 Exportando componentes..."
php artisan personalize:export items --output=storage/app/personalize/items.csv

# Exportar usuarios
echo "👥 Exportando usuarios..."
php artisan personalize:export users --output=storage/app/personalize/users.csv

# Exportar interacciones
echo "🔄 Exportando interacciones..."
php artisan personalize:export interactions --output=storage/app/personalize/interactions.csv

echo "✅ Datos exportados correctamente"

# Instrucciones finales
echo ""
echo "🎉 Configuración completada exitosamente!"
echo "=========================================="
echo ""
echo "📋 Próximos pasos:"
echo "1. Sube los archivos CSV a S3:"
echo "   aws s3 cp storage/app/personalize/ s3://tu-bucket/personalize/ --recursive"
echo ""
echo "2. Crea los datasets en AWS Personalize:"
echo "   - Sigue las instrucciones en AWS_PERSONALIZE_SETUP.md"
echo ""
echo "3. Entrena el modelo y crea la campaña"
echo ""
echo "4. Actualiza el ARN de la campaña en .env"
echo ""
echo "📚 Documentación:"
echo "- AWS_PERSONALIZE_SETUP.md - Configuración detallada"
echo "- AI_BUILD_FEATURE.md - Documentación de la funcionalidad"
echo ""
echo "🔧 Comandos útiles:"
echo "- php artisan personalize:export items - Exportar componentes"
echo "- php artisan personalize:export users - Exportar usuarios"
echo "- php artisan personalize:export interactions - Exportar interacciones"
echo ""
echo "✨ ¡La funcionalidad de armado automático con IA está lista para usar!" 