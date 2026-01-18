<?php
// public/sales_new.php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

include '../includes/header.php';

// Obtener clientes activos
$stmt = $conn->query("SELECT Ipa_client_ID, CONCAT(Ipa_client_firstname, ' ', Ipa_client_lastname) AS nombre FROM ipa_clients WHERE Ipa_client_status = 'A' ORDER BY nombre ASC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos activos con stock disponible
$stmt = $conn->query("SELECT Ipa_stock_ID, Ipa_stock_name, Ipa_stock_price, Ipa_stock_onhand FROM ipa_stock WHERE Ipa_stock_status = 'activo' AND Ipa_stock_onhand > 0 ORDER BY Ipa_stock_name ASC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="h5 mb-3">Registrar nueva venta</h2>
<form id="ventaForm" method="post" action="sales_create.php">
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Cliente</label>
      <select name="Ipa_inv_client_ID" class="form-select" required>
        <option value="">Seleccionar cliente</option>
        <?php foreach ($clientes as $c): ?>
          <option value="<?php echo htmlspecialchars($c['Ipa_client_ID']); ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-8">
      <label class="form-label">Dirección</label>
      <input type="text" name="Ipa_inv_client_address" class="form-control" placeholder="Dirección del cliente" required>
    </div>
  </div>

  <h6 class="mb-2">Productos</h6>
  <table class="table table-bordered table-sm align-middle" id="tablaProductos">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Precio</th>
        <th>Stock</th>
        <th>Cantidad</th>
        <th>Subtotal</th>
        <th></th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
  <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="agregarProducto()">+ Agregar producto</button>

  <div class="text-end mb-3">
    <strong>Total: S/. <span id="totalVenta">0.00</span></strong>
  </div>

  <input type="hidden" name="items_json" id="items_json">
  <button type="submit" class="btn btn-success">Registrar venta</button>
</form>

<div id="mensajeVenta" class="mt-3"></div>

<script>
const productos = <?php echo json_encode($productos); ?>;
let total = 0;

function agregarProducto() {
  const tbody = document.querySelector('#tablaProductos tbody');
  const row = document.createElement('tr');

  const select = document.createElement('select');
  select.className = 'form-select form-select-sm producto';
  productos.forEach(p => {
    const opt = document.createElement('option');
    opt.value = p.Ipa_stock_ID;
    opt.textContent = p.Ipa_stock_name;
    opt.dataset.price = p.Ipa_stock_price;
    opt.dataset.stock = p.Ipa_stock_onhand;
    select.appendChild(opt);
  });

  const precio = document.createElement('input');
  precio.type = 'text';
  precio.className = 'form-control form-control-sm precio';
  precio.readOnly = true;

  const stock = document.createElement('input');
  stock.type = 'text';
  stock.className = 'form-control form-control-sm stock';
  stock.readOnly = true;

  const cantidad = document.createElement('input');
  cantidad.type = 'number';
  cantidad.className = 'form-control form-control-sm cantidad';
  cantidad.min = 1;
  cantidad.value = 1;

  const subtotal = document.createElement('input');
  subtotal.type = 'text';
  subtotal.className = 'form-control form-control-sm subtotal';
  subtotal.readOnly = true;

  const eliminar = document.createElement('button');
  eliminar.type = 'button';
  eliminar.className = 'btn btn-sm btn-danger';
  eliminar.textContent = 'X';
  eliminar.onclick = () => {
    row.remove();
    calcularTotal();
  };

  select.onchange = () => {
    const opt = select.selectedOptions[0];
    precio.value = parseFloat(opt.dataset.price).toFixed(2);
    stock.value = opt.dataset.stock;
    cantidad.max = opt.dataset.stock;
    calcularSubtotal();
  };

  cantidad.oninput = calcularSubtotal;

  function calcularSubtotal() {
    const p = parseFloat(precio.value);
    const q = parseInt(cantidad.value);
    subtotal.value = (p * q).toFixed(2);
    calcularTotal();
  }

  row.appendChild(td(select));
  row.appendChild(td(precio));
  row.appendChild(td(stock));
  row.appendChild(td(cantidad));
  row.appendChild(td(subtotal));
  row.appendChild(td(eliminar));

  tbody.appendChild(row);
  select.dispatchEvent(new Event('change'));
}

function td(el) {
  const td = document.createElement('td');
  td.appendChild(el);
  return td;
}

function calcularTotal() {
  total = 0;
  document.querySelectorAll('.subtotal').forEach(s => {
    total += parseFloat(s.value) || 0;
  });
  document.getElementById('totalVenta').textContent = total.toFixed(2);
}

document.getElementById('ventaForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const form = e.target;
  const items = [];
  document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
    const stockId = row.querySelector('.producto').value;
    const qty = row.querySelector('.cantidad').value;
    items.push({ stock_id: stockId, qty: qty });
  });

  const formData = new FormData(form);
  formData.set('items_json', JSON.stringify(items));

  fetch('sales_create.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msg = document.getElementById('mensajeVenta');
    if (data.ok) {
      msg.innerHTML = `<div class="alert alert-success">Venta registrada correctamente. Redirigiendo...</div>`;
      setTimeout(() => {
        window.location.href = `invoice_detail.php?inv=${data.invoice_id}`;
      }, 1500);
    } else {
      msg.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
    }
  })
  .catch(err => {
    document.getElementById('mensajeVenta').innerHTML = `<div class="alert alert-danger">Error inesperado al registrar la venta.</div>`;
  });
});
</script>


<?php include '../includes/footer.php'; ?>