from locust import HttpUser, task, constant
import random

class PublicationCapacityTest(HttpUser):
    """
    Prueba de capacidad para el microservicio de publicaciones.
    Evalúa cada endpoint de forma independiente bajo un incremento progresivo de usuarios.
    """
    wait_time = constant(0.5)  # tráfico constante y agresivo

    def on_start(self):
        # Token de vendedor (válido para los endpoints protegidos)
        self.token = "eyJhbGciOiJIUzI1NiIsInR5cCI..."
        self.headers = {
            "Authorization": f"Bearer {self.token}",
            "Content-Type": "application/json"
        }
        self.created_ids = []  # IDs de publicaciones creadas en la prueba

    # ------------------------- MÉTODOS DE PRUEBA -------------------------

    @task(3)
    def listar_publicaciones(self):
        """GET /publications — Listar todas las publicaciones activas"""
        self.client.get("/publications", headers=self.headers)

    @task(2)
    def crear_publicacion(self):
        """POST /publications — Crear una nueva publicación"""
        data = {
            "vehicle_id": f"CAR{random.randint(100,999)}",
            "title": f"Capacidad Auto {random.randint(1,9999)}",
            "description": "Vehículo sometido a prueba de capacidad",
            "price": random.randint(25000, 150000)
        }
        response = self.client.post("/publications", json=data, headers=self.headers)
        if response.status_code == 201:
            pub_id = response.json().get("id")
            self.created_ids.append(pub_id)

    @task(2)
    def ver_publicacion(self):
        """GET /publications/{id} — Ver detalles de una publicación"""
        if self.created_ids:
            pub_id = random.choice(self.created_ids)
            self.client.get(f"/publications/{pub_id}", headers=self.headers)

    @task(2)
    def actualizar_publicacion(self):
        """PUT /publications/{id} — Editar una publicación existente"""
        if self.created_ids:
            pub_id = random.choice(self.created_ids)
            data = {
                "title": "Actualización de rendimiento",
                "description": "Probando carga de actualización",
                "price": random.randint(30000, 180000)
            }
            self.client.put(f"/publications/{pub_id}", json=data, headers=self.headers)

    @task(1)
    def cambiar_estado(self):
        """PATCH /publications/{id}/status — Cambiar el estado de una publicación"""
        if self.created_ids:
            pub_id = random.choice(self.created_ids)
            new_status = random.choice(["activo", "inactivo", "vendido"])
            self.client.patch(
                f"/publications/{pub_id}/status",
                json={"status": new_status},
                headers=self.headers
            )

    @task(1)
    def eliminar_publicacion(self):
        """DELETE /publications/{id} — Eliminar una publicación existente"""
        if self.created_ids:
            pub_id = self.created_ids.pop(0)
            self.client.delete(f"/publications/{pub_id}", headers=self.headers)
