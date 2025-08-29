# PC Builder Backend

Backend para el sistema de armado automático de PCs con recomendaciones personalizadas usando AWS Personalize.

## 🚀 Configuración Inicial

### 1. Instalar Dependencias
```bash
composer install
npm install
```

### 2. Configurar Variables de Entorno
```bash
cp .env.example .env
cp aws-personalize-config.env.example aws-personalize-config.env
```

### 3. Configurar Credenciales AWS
Edita el archivo `aws-personalize-config.env` con tus credenciales reales:

```env
AWS_ACCESS_KEY_ID=tu_access_key_id_real
AWS_SECRET_ACCESS_KEY=tu_secret_access_key_real
AWS_DEFAULT_REGION=us-east-1
AWS_PERSONALIZE_CAMPAIGN_ARN=arn:aws:personalize:us-east-1:tu-account-id:campaign/tu-campaign-name
```

### 4. Generar Clave de Aplicación
```bash
php artisan key:generate
```

### 5. Ejecutar Migraciones
```bash
php artisan migrate
```

### 6. Iniciar Servidor
```bash
php artisan serve
```

## 🔒 Seguridad

### Protección de Credenciales
- **NUNCA** subas credenciales reales a Git
- Usa archivos `.env` para configuraciones locales
- El archivo `aws-personalize-config.env` está en `.gitignore`
- Rota tus claves AWS regularmente

### Archivos Protegidos
Los siguientes archivos están protegidos por `.gitignore`:
- `.env`
- `aws-personalize-config.env`
- `*.pem`
- `*.key`
- `credentials.json`

## 📚 Documentación

- `AWS_SETUP_SUMMARY.md` - Resumen de configuración AWS Personalize
- `setup-personalize-manual.md` - Guía paso a paso para configurar AWS Personalize

## 🛠️ Comandos Útiles

### Exportar Datos para AWS Personalize
```bash
php artisan personalize:export items --output=storage/app/personalize/items.csv
php artisan personalize:export users --output=storage/app/personalize/users.csv
php artisan personalize:export interactions --output=storage/app/personalize/interactions.csv
```

### Limpiar Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🚨 Solución de Problemas

### Error de Push Protection en GitHub
Si GitHub bloquea el push por detectar credenciales:

1. Elimina las credenciales de los archivos
2. Usa `git filter-branch` para limpiar el historial
3. Fuerza el push: `git push --force-with-lease`

### Credenciales AWS Expiradas
1. Genera nuevas credenciales en AWS IAM
2. Actualiza `aws-personalize-config.env`
3. Reinicia el servidor

## 📝 Notas de Desarrollo

- El sistema usa exclusivamente AWS Personalize para recomendaciones
- Los datos se exportan en formato CSV para AWS Personalize
- El entrenamiento del modelo puede tomar 1-2 horas
- Se requieren al menos 1000 interacciones para recomendaciones efectivas
