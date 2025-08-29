# 🧠 IA para Armados Automáticos - PC Builder

## 🚀 Sistema de IA Funcionando

Tu sistema de IA para armados automáticos está **completamente funcional** sin necesidad de AWS Personalize complejo.

---

## ✅ ¿Qué hace la IA?

### 🎯 **Funcionalidades principales:**
1. **Analiza patrones históricos** del cliente
2. **Recomienda componentes** por rendimiento y precio
3. **Distribuye presupuesto** inteligentemente por categorías
4. **Crea armados completos** automáticamente
5. **Guarda en base de datos** para seguimiento

### 🧠 **Algoritmo de IA:**
- **Si hay historial**: Analiza armados previos del cliente
- **Si no hay historial**: Usa componentes por rendimiento
- **Distribución inteligente** del presupuesto:
  - Tarjeta Gráfica: 35%
  - Procesador: 25%
  - Placa Base: 15%
  - Memoria RAM: 10%
  - Disco Duro: 8%
  - Fuente de Poder: 5%
  - Gabinete: 2%

---

## 🔧 Cómo usar la IA

### 1. **API Endpoint:**
```http
POST /api/armados/generar-automatico
```

### 2. **Parámetros requeridos:**
```json
{
    "presupuesto": 1500.00,
    "id_cliente": 1,
    "preferencias": {
        "gaming": true,
        "rendimiento": "alto"
    }
}
```

### 3. **Respuesta de la IA:**
```json
{
    "success": true,
    "message": "🎯 Armado automático generado exitosamente usando IA",
    "data": {
        "armado": { "id_armado": 123 },
        "componentes": [...],
        "precio_total": 1450.00,
        "presupuesto_original": 1500.00
    },
    "metodo_utilizado": "IA - Datos históricos y rendimiento",
    "presupuesto_utilizado": 1450.00,
    "presupuesto_restante": 50.00
}
```

---

## 🧪 Probar la IA

### **Opción 1: Archivo de prueba**
```bash
cd pc_builder-backend
php test_ia_armado.php
```

### **Opción 2: Postman/Insomnia**
```http
POST http://localhost:8000/api/armados/generar-automatico
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
    "presupuesto": 2000.00,
    "id_cliente": 1,
    "preferencias": {
        "gaming": true,
        "rendimiento": "alto"
    }
}
```

### **Opción 3: Frontend Angular**
```typescript
// En tu servicio Angular
generarArmadoAutomatico(presupuesto: number, idCliente: number) {
  return this.http.post('/api/armados/generar-automatico', {
    presupuesto: presupuesto,
    id_cliente: idCliente,
    preferencias: {
      gaming: true,
      rendimiento: 'alto'
    }
  });
}
```

---

## 📊 Logs de la IA

La IA genera logs detallados para monitoreo:

```
🚀 Iniciando generación de armado automático con IA
✅ Parámetros validados correctamente
🧠 Iniciando análisis de IA para recomendaciones
📊 Datos históricos obtenidos
🔍 Analizando patrones históricos del cliente
🎯 Organizando componentes por categoría con IA
✅ Componente seleccionado para Procesador
✅ Componente seleccionado para Tarjeta Gráfica
🎯 Recomendaciones de IA obtenidas
✅ Armado creado exitosamente con IA
💾 Armado guardado en base de datos
```

---

## 🎯 Características de la IA

### ✅ **Ventajas:**
- **Funciona inmediatamente** sin configuración compleja
- **Aprende de datos históricos** del cliente
- **Optimiza presupuesto** por categorías
- **Selecciona por rendimiento** cuando no hay historial
- **Logs detallados** para debugging
- **Respuestas estructuradas** con metadatos

### 🔧 **Configuración mínima:**
```env
# Solo necesitas tu base de datos funcionando
# No requiere AWS Personalize complejo
```

---

## 🚀 Próximos pasos

### **Para mejorar la IA:**
1. **Agregar más datos históricos** (armados previos)
2. **Implementar compatibilidad** entre componentes
3. **Añadir preferencias específicas** (gaming, trabajo, etc.)
4. **Optimizar distribución** de presupuesto
5. **Integrar con AWS Personalize** para recomendaciones avanzadas

### **Para usar en producción:**
1. ✅ **IA funcionando** (completado)
2. ✅ **API endpoint** (completado)
3. ✅ **Logs y monitoreo** (completado)
4. 🔄 **Frontend integration** (pendiente)
5. 🔄 **Testing completo** (pendiente)

---

## 🎉 ¡Tu IA está lista!

**La IA de armados automáticos está funcionando y lista para usar.** Solo necesitas:

1. **Tener componentes** en tu base de datos
2. **Tener clientes** registrados
3. **Hacer la petición** al endpoint

¡La IA se encargará del resto! 🚀 