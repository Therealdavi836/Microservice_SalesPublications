from locust import HttpUser, task, constant
import random

class SalesCapacityTest(HttpUser):
    """
    Prueba de CAPACIDAD para el microservicio de Ventas.
    Mide hasta qué punto el sistema puede responder sin degradación.
    """
    wait_time = constant(0.5)  # tráfico continuo y fuerte

    def on_start(self):
        # Token de cliente autenticado (válido)
        self.token = "eyJhbGciOiJIUzI1NiIsInR5cCI..."
        self.headers = {
            "Authorization": f"Bearer {self.token}",
            "Content-Type": "application/json"
        }
        self.sales_ids = []
        self.publication_ids = [1, 2, 3, 4, 5]

    # ---------------- MÉTODOS DE PRUEBA ----------------

    @task(3)
    def listar_ventas(self):
        """
        GET /sales — Listar todas las ventas
        """
        self.client.get("/sales", headers=self.headers)

    @task(2)
    def registrar_venta(self):
        """
        POST /sales — Registrar una venta
        """
        data = {
            "publication_id": random.choice(self.publication_ids),
            "sale_price": random.randint(50000, 300000)
        }
        response = self.client.post("/sales", json=data, headers=self.headers)
        if response.status_code == 201:
            sale_id = response.json().get("id")
            if sale_id:
                self.sales_ids.append(sale_id)

    @task(2)
    def ver_detalle_venta(self):
        """
        GET /sales/{id} — Ver detalle de una venta
        """
        if self.sales_ids:
            sale_id = random.choice(self.sales_ids)
            self.client.get(f"/sales/{sale_id}", headers=self.headers)

    # ---------------------------------------------------
