from locust import HttpUser, task, between
import random

class SalesLoadTest(HttpUser):
    """
    Prueba de CARGA para el microservicio de Ventas.
    Simula el comportamiento normal de usuarios concurrentes (clientes y administradores).
    """
    wait_time = between(1, 3)  # comportamiento humano (descansos entre tareas)

    def on_start(self):
        # Token de usuario autenticado (simula cliente)
        self.token = "eyJhbGciOiJIUzI1NiIsInR5cCI..."
        self.headers = {
            "Authorization": f"Bearer {self.token}",
            "Content-Type": "application/json"
        }
        self.sales_ids = []
        self.publication_ids = [1, 2, 3, 4, 5]  # publicaciones activas de ejemplo

    # -------------------- MÉTODOS DE PRUEBA --------------------

    @task(4)
    def listar_ventas(self):
        """
        GET /sales — Ver lista de ventas (cliente o admin)
        """
        self.client.get("/sales", headers=self.headers)

    @task(2)
    def registrar_venta(self):
        """
        POST /sales — Registrar una nueva venta (solo clientes)
        """
        data = {
            "publication_id": random.choice(self.publication_ids),
            "sale_price": random.randint(50000, 250000)
        }
        response = self.client.post("/sales", json=data, headers=self.headers)
        if response.status_code == 201:
            sale_id = response.json().get("id")
            if sale_id:
                self.sales_ids.append(sale_id)

    @task(2)
    def ver_detalle_venta(self):
        """
        GET /sales/{id} — Consultar detalle de una venta específica
        """
        if self.sales_ids:
            sale_id = random.choice(self.sales_ids)
            self.client.get(f"/sales/{sale_id}", headers=self.headers)
