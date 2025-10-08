# Microservicio de Publicaciones y Ventas

Microservicio desarrollado en **Laravel (PHP 8.2.12)** encargado de gestionar las **publicaciones de vehículos** realizadas por los vendedores (sellers) y registrar las **ventas** efectuadas por los clientes (customers).  
Forma parte del ecosistema de microservicios del proyecto de **Ingeniería de Software II**, junto con los microservicios de **Autenticación**, **Catálogo de Vehículos** y **Reportes**.

---

## Tecnologías utilizadas

- **Lenguaje:** PHP 8.2.12  
- **Framework:** Laravel  
- **Base de datos:** MySQL  
- **Tipo de arquitectura:** Microservicios basados en API REST  
- **Pruebas de rendimiento:** Locust (carga y capacidad)

---

## Instalación y configuración

1. Clonar el repositorio:

   ```bash
   git clone https://github.com/Therealdavi836/Microservice_SalesPublications.git
   cd Microservice_SalesPublications
    ```

1. Instalar dependencias:

   ```bash
   composer update
   ```

2. Configurar el archivo `.env`:

   ```env
   APP_NAME=Microservice_SalesPublications
   APP_ENV=local
   APP_KEY=base64:GENERAR_LARAVEL_KEY
   APP_DEBUG=true
   APP_URL=http://localhost:8002

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nombre_de_tu_bd
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. Ejecutar el servidor local:

   ```bash
   php artisan serve --port=8002
   ```

---

## Lógica de negocio

* Una **publicación** pertenece a un **usuario vendedor (seller)**.
* Una publicación hace referencia a un **vehículo existente** en el **microservicio de catálogo**.
* Una **venta** relaciona a un **comprador (customer)**, un **vendedor (seller)** y el **vehículo** vendido.
* Una **oferta** está asociada a una publicación.
* Los **administradores (admins)** pueden acceder a todos los registros de publicaciones y ventas.

Este microservicio:

* **Consume** el microservicio de autenticación para **validar tokens y roles**.
* **Consume** el microservicio de catálogo de vehículos para **verificar la existencia** del vehículo publicado.
* **Es consumido** por el microservicio de reportes para generar reportes de actividad.

---

## Roles y accesos

| Rol          | Acceso principal                                         |
| ------------ | -------------------------------------------------------- |
| **Seller**   | Crear, editar, eliminar y actualizar publicaciones.      |
| **Customer** | Crear ventas (comprar vehiculos).                        |
| **Admin**    | Consultar todos los registros de publicaciones y ventas. |

> No existen *middlewares* dedicados, pero el microservicio **recibe el token** y valida el rol del usuario en los controladores mediante métodos personalizados.

---

## Endpoints disponibles

### Publicaciones (`/api/publications`)

| Método   | Endpoint                        | Descripción                          | Parámetros / Body                                                  |
| -------- | ------------------------------- | ------------------------------------ | ------------------------------------------------------------------ |
| `GET`    | `/api/publications`             | Obtiene todas las publicaciones.     | —                                                                  |
| `GET`    | `/api/publications/{id}`        | Obtiene una publicación específica.  | `id`                                                               |
| `POST`   | `/api/publications`             | Crea una nueva publicación.          | `user_id`, `vehicle_id`, `title`, `description`, `price`, `status` |
| `PUT`    | `/api/publications/{id}`        | Actualiza una publicación existente. | Igual que POST                                                     |
| `DELETE` | `/api/publications/{id}`        | Elimina una publicación.             | `id`                                                               |
| `PATCH`  | `/api/publications/{id}/status` | Cambia el estado de una publicación. | `status`                                                           |

---

### Ventas (`/api/sales`)

| Método | Endpoint          | Descripción                           | Parámetros / Body              |
| ------ | ----------------- | ------------------------------------- | ------------------------------ |
| `GET`  | `/api/sales`      | Obtiene todas las ventas registradas. | —                              |
| `GET`  | `/api/sales/{id}` | Obtiene una venta específica.         | `id`                           |
| `POST` | `/api/sales`      | Registra una nueva venta.             | `publication_id`, `sale_price` |

---

## Pruebas de rendimiento

El microservicio cuenta con una carpeta `Locust_Test` que incluye **cuatro archivos de prueba**, distribuidos así:

| Tipo de prueba      | Controlador  | Archivo                           |
| ------------------- | ------------ | --------------------------------- |
| Prueba de carga     | Publications | `locust_carga_publications.py`     |
| Prueba de capacidad | Publications | `locust_capacidad_publications.py` |
| Prueba de carga     | Sales        | `locust_carga_sales.py`            |
| Prueba de capacidad | Sales        | `locust_capacidad_sales.py`        |

---

## Roadmap / Futuras mejoras

* Integración completa con **Docker** y **Kubernetes**.
* Posible adición de filtros de búsqueda y estadísticas de ventas.
* Mejoras en la comunicación asíncrona entre microservicios.

---

### Licencia

MIT (heredada de la plantilla base de Laravel).

---

### Contacto / Mantenimiento

* **Autor:** Juan David Fajardo Betancourt
* **Email:** [jfajardob@unal.edu.co](mailto:jfajardob@unal.edu.co)

---

### Razón

* Proyecto semestral de **Ingeniería de Software II**, semestre **2025-2**.
* Presentado al docente: **Jose Albeiro Montes Gil**.
* Documento de planeación:
  [https://docs.google.com/document/d/1bnb3KTs5Pmeoy83xN5RjugHqdJ3E_rLXUf8NLsQU5xE/edit?usp=sharing](https://docs.google.com/document/d/1bnb3KTs5Pmeoy83xN5RjugHqdJ3E_rLXUf8NLsQU5xE/edit?usp=sharing)
* Informe de evidencia de ejecución de pruebas de rendimiento: *https://docs.google.com/document/d/1S7h12ZzESNoP5FUDjKQ7n9BWXnBOSWf8oHqbKiwUuSc/edit?usp=sharing*
