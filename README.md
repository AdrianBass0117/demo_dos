# 🛡️ Demo: DoS Attack & Rate Limiting con PHP

Este proyecto es una prueba de concepto diseñada para demostrar cómo funciona un ataque de **Denegación de Servicio (DoS)** a nivel de aplicación (Capa 7) y cómo mitigar sus efectos implementando una estrategia de **Rate Limiting** (Limitación de Tasa) utilizando PHP puro.

> ⚠️ **AVISO LEGAL:** Este código ha sido creado únicamente con fines académicos y de aprendizaje. El script de ataque (`ataque.php`) no debe ser utilizado contra servidores, sitios web o infraestructuras sobre las que no se tenga autorización explícita. El autor no se hace responsable del mal uso de esta herramienta.

## 📋 Tabla de Contenidos
- [Requisitos](#-requisitos)
- [Instalación y Configuración](#-instalación-y-configuración)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Cómo Ejecutar la Demostración](#-cómo-ejecutar-la-demostración)
- [Explicación Técnica](#-explicación-técnica)
  - [El Ataque (DoS)](#1-el-ataque-dos-http-flood)
  - [La Defensa (Rate Limiting)](#2-la-defensa-rate-limiting)
- [Configuración Avanzada](#-configuración-avanzada)

---

## 💻 Requisitos
* **XAMPP / WAMP / MAMP** (Servidor Apache + PHP).
* **PHP 7.4 o superior**.
* Acceso a la terminal/consola de comandos.
* Extensión `php-curl` habilitada (generalmente activa por defecto en XAMPP).

---

## ⚙️ Instalación y Configuración

### 1. Despliegue de Archivos
Coloca la carpeta del proyecto dentro del directorio público de tu servidor web:
* **XAMPP:** `C:\xampp\htdocs\demo_dos`

### 2. Ajuste Crítico de Apache (Para Localhost)
Para que la demostración del ataque DoS sea efectiva en un entorno local sin necesitar miles de bots, debemos reducir la capacidad de concurrencia de Apache intencionalmente.

1.  Abre el panel de XAMPP > Config > `Apache (httpd-mpm.conf)`.
2.  Busca el módulo `mpm_winnt_module`.
3.  Cambia `ThreadsPerChild` de 150 a **20**.
4.  Reinicia Apache.

*Esto asegura que con solo 50 peticiones simultáneas saturemos el servidor.*

---

## 📂 Estructura del Proyecto

* `vulnerable.php`: Página objetivo sin protección. Simula un proceso lento (ej. consulta a BD pesada) usando `sleep()`.
* `protegido.php`: Página asegurada. Implementa un algoritmo de Rate Limiting que rechaza IPs que exceden el límite permitido.
* `ataque.php`: Script CLI (Línea de comandos) que actúa como el atacante. Utiliza **Multi-cURL** para enviar ráfagas de peticiones simultáneas.
* `estilos.css`: Hoja de estilos para diferenciar visualmente el estado del servidor (Normal vs Bloqueado).

---

## 🚀 Cómo Ejecutar la Demostración

### Escenario A: Tumbar el Sitio Vulnerable

1.  Abre `ataque.php` y configura el objetivo:
    ```php
    $objetivo = "http://localhost/demo_dos/vulnerable.php";
    ```
2.  Abre una terminal en la carpeta del proyecto y ejecuta:
    ```bash
    php ataque.php
    ```
3.  Intenta acceder a `http://localhost/demo_dos/vulnerable.php` desde tu navegador.
    * **Resultado:** La página cargará infinitamente o dará error de conexión. El servidor está saturado.

### Escenario B: Resiliencia del Sitio Protegido

1.  Detén el ataque (`Ctrl + C`).
2.  Cambia el objetivo en `ataque.php`:
    ```php
    $objetivo = "http://localhost/demo_dos/protegido.php";
    ```
3.  Ejecuta nuevamente: `php ataque.php`.
4.  Accede a `http://localhost/demo_dos/protegido.php` desde tu navegador.
    * **Resultado:** La página carga fluidamente. Es posible que veas la pantalla de bloqueo (Error 429) momentáneamente, pero el servidor web **no se cae**.

---

## 🧠 Explicación Técnica

### 1. El Ataque DoS (HTTP Flood)
El script `ataque.php` no es un bucle simple. Utiliza `curl_multi_exec`, una librería de PHP que permite "Multithreading" (hilos múltiples) simulado.

* **Funcionamiento:** Crea un lote de 50 peticiones HTTP y las dispara **todas al mismo tiempo** contra el servidor.
* **Por qué funciona:** Apache (con nuestra config de 20 hilos) intenta atender las 50 peticiones. Como la página vulnerable tarda en responder (por el `sleep`), todos los "trabajadores" de Apache se quedan ocupados esperando. Cuando tú intentas entrar, no hay trabajadores libres para atenderte.

### 2. La Defensa (Rate Limiting)
El archivo `protegido.php` implementa un algoritmo de **Ventana Fija (Fixed Window)** antes de procesar cualquier lógica pesada.

**Flujo del Algoritmo:**
1.  **Identificación:** Obtiene la IP del cliente (`$_SERVER['REMOTE_ADDR']`).
2.  **Historial:** Busca un archivo JSON temporal asociado a esa IP (ej. `limite_192.168.1.1.json`).
3.  **Validación de Tiempo:**
    * Si el archivo existe, verifica cuándo fue la primera petición.
    * Si pasaron más de 10 segundos (la ventana de tiempo), reinicia el contador a 0.
4.  **Conteo y Bloqueo:**
    * Si el contador es menor al límite (5), permite el paso e incrementa el contador.
    * Si el contador supera el límite, devuelve inmediatamente un **HTTP 429 Too Many Requests** y termina la ejecución (`exit`).
    * *Clave:* Al terminar la ejecución rápido, libera al "trabajador" de Apache casi instantáneamente, permitiendo que entre tráfico legítimo.

---

## 🛠️ Configuración Avanzada

Puedes modificar la intensidad del ataque y la sensibilidad de la defensa editando las variables al inicio de los archivos.

### Aumentar la potencia del Ataque (`ataque.php`)
Para simular una botnet más grande o saturar servidores más potentes:

```php
// Número de peticiones simultáneas por ciclo.
// Precaución: Valores > 100 pueden alentar tu propia PC.
$cantidad_hilos = 50; 

// Velocidad del bucle (en microsegundos).
// Menor número = Ataque más agresivo.
usleep(1000);