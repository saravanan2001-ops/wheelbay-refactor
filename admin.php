<?php
session_start();

// Check if admin is logged in (you should implement proper admin authentication)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'wheelbay';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['edit_car'])) {
            // Update car in database
            $stmt = $conn->prepare("
                UPDATE cars SET 
                make = ?, 
                model = ?, 
                year = ?, 
                mileage = ?, 
                fuel_type = ?, 
                transmission = ?, 
                color = ?, 
                price = ?, 
                description = ? 
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['make'],
                $_POST['model'],
                $_POST['year'],
                $_POST['mileage'],
                $_POST['fuel_type'],
                $_POST['transmission'],
                $_POST['color'],
                $_POST['price'],
                $_POST['description'],
                $_POST['id']
            ]);
            
            $_SESSION['message'] = "Car updated successfully!";
            header("Location: admin.php");
            exit();
        }
        elseif (isset($_POST['update_order_status'])) {
            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET status = ?, notes = ? WHERE id = ?");
            $stmt->execute([$_POST['status'], $_POST['notes'], $_POST['order_id']]);
            
            $_SESSION['message'] = "Order status updated successfully!";
            header("Location: admin.php");
            exit();
        }
        elseif (isset($_POST['delete_car'])) {
            // Delete car
            $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            $_SESSION['message'] = "Car deleted successfully!";
            header("Location: admin.php");
            exit();
        }
        elseif (isset($_POST['delete_order'])) {
            // Delete order
            $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$_POST['order_id']]);
            
            $_SESSION['message'] = "Order deleted successfully!";
            header("Location: admin.php");
            exit();
        }
    }
    
    // Fetch all cars
    $cars = $conn->query("SELECT * FROM cars ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch all orders with car details
    $orders = $conn->query("
        SELECT o.*, c.make, c.model, c.year, c.color 
        FROM orders o
        JOIN cars c ON o.car_id = c.id
        ORDER BY o.order_date DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - WheelBay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Your existing CSS styles here */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #121212;
            color: #ffffff;
            line-height: 1.6;
        }

        .admin-header {
            background-color: #1a1a1a;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .admin-title {
            font-size: 1.8em;
            color: #17fee3;
        }

        .admin-nav {
            display: flex;
            gap: 20px;
        }

        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .admin-nav a:hover, .admin-nav a.active {
            background-color: #17fee3;
            color: #121212;
        }

        .admin-logout {
            color: #ff3333;
            text-decoration: none;
            font-weight: bold;
        }

        .admin-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .status-message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background-color: rgba(23, 254, 227, 0.2);
            color: #17fee3;
            border-left: 4px solid #17fee3;
        }

        .error {
            background-color: rgba(255, 51, 51, 0.2);
            color: #ff3333;
            border-left: 4px solid #ff3333;
        }

        .section-title {
            font-size: 1.5em;
            color: #17fee3;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
        }

        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1em;
            position: relative;
        }

        .tab-btn.active {
            color: #17fee3;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #17fee3;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #1f1f1f;
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table th, .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        .data-table th {
            background-color: #17fee3;
            color: #121212;
            font-weight: bold;
        }

        .data-table tr:hover {
            background-color: #262c37;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .edit-btn {
            background-color: #17fee3;
            color: #121212;
        }

        .delete-btn {
            background-color: #ff3333;
            color: white;
        }

        .view-btn {
            background-color: #4285f4;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            overflow: auto;
        }

        .modal-content {
            background-color: #1f1f1f;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 700px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
        }

        .modal-title {
            font-size: 1.5em;
            color: #17fee3;
        }

        .close-modal {
            color: #aaa;
            font-size: 1.5em;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #fff;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #17fee3;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: none;
            background-color: #262c37;
            color: white;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #17fee3;
            margin-bottom: 5px;
        }

        .info-value {
            padding: 8px;
            background-color: #262c37;
            border-radius: 5px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #17fee3;
            color: #121212;
        }

        .btn-secondary {
            background-color: #666;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .image-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1 class="admin-title">WheelBay Admin Panel</h1>
        <div class="admin-nav">
            <a href="#" class="active">Dashboard</a>
            <a href="registercar.php">New Car</a>
        </div>
        <a href="admin_logout.php" class="admin-logout">Logout <i class="fas fa-sign-out-alt"></i></a>
    </header>

    <div class="admin-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="status-message success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="status-message error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn active" onclick="openTab('cars')">Car Listings</button>
            <button class="tab-btn" onclick="openTab('orders')">Orders</button>
        </div>

        <!-- Cars Tab -->
        <div id="cars-tab" class="tab-content active">
            <h2 class="section-title">Manage Car Listings</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Make</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Price</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($car['id']); ?></td>
                            <td><?php echo htmlspecialchars($car['make']); ?></td>
                            <td><?php echo htmlspecialchars($car['model']); ?></td>
                            <td><?php echo htmlspecialchars($car['year']); ?></td>
                            <td>$<?php echo number_format($car['price'], 2); ?></td>
                            <td><?php echo date('M j, Y', strtotime($car['created_at'])); ?></td>
                            <td>
                                <a href="#" class="action-btn edit-btn" onclick="openEditModal(
                                    '<?php echo $car['id']; ?>',
                                    '<?php echo addslashes($car['make']); ?>',
                                    '<?php echo addslashes($car['model']); ?>',
                                    '<?php echo $car['year']; ?>',
                                    '<?php echo $car['mileage']; ?>',
                                    '<?php echo $car['fuel_type']; ?>',
                                    '<?php echo $car['transmission']; ?>',
                                    '<?php echo addslashes($car['color']); ?>',
                                    '<?php echo $car['price']; ?>',
                                    `<?php echo addslashes($car['description']); ?>`
                                )">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this car?');">
                                    <input type="hidden" name="delete_car" value="1">
                                    <input type="hidden" name="id" value="<?php echo $car['id']; ?>">
                                    <button type="submit" class="action-btn delete-btn" style="border:none;cursor:pointer;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Orders Tab -->
        <div id="orders-tab" class="tab-content">
            <h2 class="section-title">Manage Orders</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Car Details</th>
                        <th>Price</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>ORD-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['make'].' '.$order['model'].' ('.$order['year'].')'); ?>
                                <br><small>Color: <?php echo htmlspecialchars($order['color']); ?></small>
                            </td>
                            <td>$<?php echo number_format($order['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['phone']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td>
                                <span style="
                                    background-color: <?php 
                                        echo $order['status'] === 'processing' ? 'rgba(23, 254, 227, 0.2)' : 
                                              ($order['status'] === 'completed' ? 'rgba(0, 200, 83, 0.2)' : 
                                              ($order['status'] === 'cancelled' ? 'rgba(255, 51, 51, 0.2)' : 'rgba(255, 214, 0, 0.2)')); 
                                    ?>;
                                    color: <?php 
                                        echo $order['status'] === 'processing' ? '#17fee3' : 
                                              ($order['status'] === 'completed' ? '#00c853' : 
                                              ($order['status'] === 'cancelled' ? '#ff3333' : '#ffd600')); 
                                    ?>;
                                    padding: 5px 10px;
                                    border-radius: 20px;
                                    font-size: 0.8em;
                                    text-transform: capitalize;
                                ">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="action-btn view-btn" onclick="openOrderModal(
                                    '<?php echo $order['id']; ?>',
                                    '<?php echo addslashes($order['fullname']); ?>',
                                    '<?php echo addslashes($order['email']); ?>',
                                    '<?php echo addslashes($order['phone']); ?>',
                                    '<?php echo date('F j, Y', strtotime($order['order_date'])); ?>',
                                    `<?php echo addslashes($order['address']); ?>`,
                                    '<?php echo addslashes($order['make'].' '.$order['model'].' ('.$order['year'].')'); ?>',
                                    '<?php echo addslashes($order['color']); ?>',
                                    '$<?php echo number_format($order['amount'], 2); ?>',
                                    '<?php echo $order['status']; ?>',
                                    `<?php echo addslashes($order['notes'] ?? ''); ?>`
                                )">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                    <input type="hidden" name="delete_order" value="1">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="action-btn delete-btn" style="border:none;cursor:pointer;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Car Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Car Listing</h2>
                    <span class="close-modal" onclick="closeModal()">&times;</span>
                </div>
                <form method="POST">
                    <input type="hidden" name="edit_car" value="1">
                    <input type="hidden" name="id" id="editId">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="editMake">Make</label>
                            <input type="text" id="editMake" name="make" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editModel">Model</label>
                            <input type="text" id="editModel" name="model" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editYear">Year</label>
                            <input type="number" id="editYear" name="year" min="1900" max="2024" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editMileage">Mileage</label>
                            <input type="number" id="editMileage" name="mileage" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editFuelType">Fuel Type</label>
                            <select id="editFuelType" name="fuel_type" required>
                                <option value="Petrol">Petrol</option>
                                <option value="Diesel">Diesel</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="editTransmission">Transmission</label>
                            <select id="editTransmission" name="transmission" required>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                                <option value="Semi-Automatic">Semi-Automatic</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="editColor">Color</label>
                            <input type="text" id="editColor" name="color" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editPrice">Price ($)</label>
                            <input type="number" id="editPrice" name="price" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea id="editDescription" name="description"></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Order Modal -->
        <div id="orderModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Order Details</h2>
                    <span class="close-modal" onclick="closeModal()">&times;</span>
                </div>
                <form method="POST">
                    <input type="hidden" name="update_order_status" value="1">
                    <input type="hidden" name="order_id" id="orderIdInput">
                    
                    <div class="info-group">
                        <div class="info-label">Order ID</div>
                        <div class="info-value" id="orderId">ORD-1001</div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="info-group">
                            <div class="info-label">Customer Name</div>
                            <div class="info-value" id="orderCustomer">John Smith</div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Email</div>
                            <div class="info-value" id="orderEmail">john.smith@example.com</div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value" id="orderPhone">+1 (555) 123-4567</div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Order Date</div>
                            <div class="info-value" id="orderDate">June 20, 2023</div>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Shipping Address</div>
                        <div class="info-value" id="orderAddress">123 Main St, Anytown, CA 90210, USA</div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Car Details</div>
                        <div class="info-value" id="orderCar">
                            <strong>Tesla Model S (2022)</strong><br>
                            Color: Midnight Silver
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="info-group">
                            <div class="info-label">Car Price</div>
                            <div class="info-value" id="orderPrice">$89,990.00</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="orderStatus">Order Status</label>
                            <select id="orderStatus" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="orderNotes">Additional Notes</label>
                        <textarea id="orderNotes" name="notes"></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function openTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Deactivate all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Activate selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // Modal functions for cars
        function openEditModal(id, make, model, year, mileage, fuelType, transmission, color, price, description) {
            document.getElementById('editId').value = id;
            document.getElementById('editMake').value = make;
            document.getElementById('editModel').value = model;
            document.getElementById('editYear').value = year;
            document.getElementById('editMileage').value = mileage;
            document.getElementById('editFuelType').value = fuelType;
            document.getElementById('editTransmission').value = transmission;
            document.getElementById('editColor').value = color;
            document.getElementById('editPrice').value = price;
            document.getElementById('editDescription').value = description;
            
            // Show modal
            document.getElementById('editModal').style.display = 'block';
        }

        // Modal functions for orders
        function openOrderModal(id, customer, email, phone, date, address, car, color, price, status, notes) {
            document.getElementById('orderIdInput').value = id;
            document.getElementById('orderId').textContent = 'ORD-' + String(id).padStart(4, '0');
            document.getElementById('orderCustomer').textContent = customer;
            document.getElementById('orderEmail').textContent = email;
            document.getElementById('orderPhone').textContent = phone;
            document.getElementById('orderDate').textContent = date;
            document.getElementById('orderAddress').textContent = address;
            document.getElementById('orderCar').innerHTML = `<strong>${car}</strong><br>Color: ${color}`;
            document.getElementById('orderPrice').textContent = price;
            document.getElementById('orderStatus').value = status;
            document.getElementById('orderNotes').value = notes;
            
            // Show modal
            document.getElementById('orderModal').style.display = 'block';
        }

        function closeModal() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>