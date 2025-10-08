from locust import HttpUser, task, between
import random

class PublicationLoadTest(HttpUser):
    wait_time = between(1, 3)

    def on_start(self):
        # Token de vendedor válido (puede ser fijo o generado desde Auth MS)
        self.token = "eyJhbGciOiJIUzI1NiIsInR5cCI..."
        self.headers = {"Authorization": f"Bearer {self.token}", "Content-Type": "application/json"}
        self.created_ids = []  # Guardar IDs de publicaciones creadas

    @task(3)
    def listar_publicaciones(self):
        """Listar todas las publicaciones activas"""
        self.client.get("/publications", headers=self.headers)

    @task(2)
    def crear_publicacion(self):
        """Crear una nueva publicación"""
        data = {
            "vehicle_id": f"CAR{random.randint(100,999)}",
            "title": f"Auto de prueba {random.randint(1,9999)}",
            "description": "Vehículo en buen estado",
            "price": random.randint(20000, 100000)
        }
        response = self.client.post("/publications", json=data, headers=self.headers)
        if response.status_code == 201:
            self.created_ids.append(response.json().get("id"))

    @task(1)
    def ver_publicacion(self):
        """Ver detalles de una publicación específica"""
        if self.created_ids:
            pub_id = random.choice(self.created_ids)
            self.client.get(f"/publications/{pub_id}", headers=self.headers)

    @task(1)
    def actualizar_publicacion(self):
        """Editar una publicación existente"""
        if self.created_ids:
            pub_id = random.choice(self.created_ids)
            update_data = {
                "title": "Título actualizado",
                "description": "Descripción modificada",
                "price": random.randint(25000, 120000)
            }
            self.client.put(f"/publications/{pub_id}", json=update_data, headers=self.headers)

    @task(1)
    def cambiar_estado(self):
        """Cambiar el estado de una publicación"""
        if self.created_ids:
            pub_id = random.choice(self.created_ids)
            new_status = random.choice(["activo", "inactivo", "vendido"])
            self.client.patch(f"/publications/{pub_id}/status", json={"status": new_status}, headers=self.headers)

    @task(1)
    def eliminar_publicacion(self):
        """Eliminar una publicación"""
        if self.created_ids:
            pub_id = self.created_ids.pop()
            self.client.delete(f"/publications/{pub_id}", headers=self.headers)
