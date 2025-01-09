
<?php
session_start();
if (!isset($_SESSION['owner'])) {
    header('Location: login_owner.php');
    exit;
}
// Database connection setup
$host = 'localhost';
$user = 'root';
$password = ''; // Ubah sesuai dengan password database Anda
$dbname = 'minuman2';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions for adding, editing, and deleting menu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_menu'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image = $_POST['image'];
    
        $sql = "INSERT INTO menu_items (name, price, image) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sis', $name, $price, $image);
        $stmt->execute();
    }
    

    if (isset($_POST['delete_menu'])) {
        $menuId = $_POST['menu_id'];
        $sql ="DELETE FROM menu_items WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $menuId);
        $stmt->execute();
    }

    if (isset($_POST['edit_menu'])) {
        $menuId = $_POST['menu_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image = $_POST['image'];
    
        $sql = "UPDATE menu_items SET name = ?, price = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
    
        if ($stmt) {
            $stmt->bind_param('sdsi', $name, $price, $image, $menuId);
            if ($stmt->execute()) {
            } else {
                echo "Kesalahan eksekusi: " . $stmt->error;
            }
        } else {
            echo "Kesalahan query: " . $conn->error;
        }
    }
    
}
$sql = "SELECT * FROM menu_items";
$result = $conn->query($sql);
$menu_items = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
$page = isset($_GET['page']) ? $_GET['page'] : 'add_menu';

// Fetch all bookings
if ($page === 'booking_list') {
    $sql = "SELECT b.*, m.name AS menu_name
    FROM bookings b
    JOIN menu_items m ON b.menu_item_id = m.id
   WHERE TRIM(b.status) != 'complete'";

    
    $result = $conn->query($sql);
    $bookings = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }
 
}

$page = isset($_GET['page']) ? $_GET['page'] : 'add_menu';

// Handle adding a new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Thai Tea Admin baru berhasil ditambahkan!');</script>";
    } else {
        echo "<script>alert('Gagal menambahkan admin!');</script>";
    }
}

// Handle booking actions (confirm and cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Konfirmasi pemesanan
    if (isset($_POST['confirm_booking'])) {
        $bookingId = $_POST['booking_id'];

        // Ambil data pemesanan
        $sql = "SELECT * FROM bookings WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();

        if ($booking) {
            // Pindahkan data ke tabel history
            $sql = "INSERT INTO history (menu_item_id, name, email, phone, total_price)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isssi', $booking['menu_item_id'], $booking['name'], $booking['email'], $booking['phone'], $booking['total_price']);

            if ($stmt->execute()) {
                // Hapus data pemesanan hanya jika data berhasil ditambahkan ke history
                $sql = "DELETE FROM bookings WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $bookingId);
                $stmt->execute();

                // Redirect setelah berhasil
                header("Location: admin.php?page=history_list");
                exit();
            } else {
                die("Error inserting into history: " . $stmt->error);
            }
        } else {
            die("Booking not found.");
        }
    }
}




    // Cancel booking
    if (isset($_POST['cancel_booking'])) {
        $bookingId = $_POST['booking_id'];
        
        // Hapus data pemesanan
        $sql = "DELETE FROM bookings WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();

        // Redirect setelah berhasil
        header("Location: admin.php?page=booking_list");
        exit();
    }




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thai Tea Admin Dashboard - HayCarRent</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        header {
            background-color: #263238;
            color: #fff;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .navbar-logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: #fff;
        }

        .navbar-menu {
            display: flex;
            list-style: none;
        }

        .navbar-menu li {
            margin: 0 15px;
        }

        .navbar-menu a {
            text-decoration: none;
            color: #fff;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .navbar-menu a:hover {
            color: #ffa726;
        }

        .navbar-menu .active {
            color: #ffa726;
        }

        main {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        h1, h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        input, select, button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .img-preview {
            width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar">
        <a class="navbar-logo">nyot nyot thaitea</a>
        <ul class="navbar-menu">
            <li><a href="?page=add_menu" class="<?php echo $page === 'add_menu' ? 'active' : ''; ?>">Tambah Menu</a></li>
            <li><a href="?page=menu_list" class="<?php echo $page === 'menu_list' ? 'active' : ''; ?>">Daftar Menu</a></li>
            <li><a href="?page=booking_list" class="<?php echo $page === 'booking_list' ? 'active' : ''; ?>">Daftar Pesanan</a></li>
            <li><a href="?page=history_list" class="<?php echo $page === 'history_list' ? 'active' : ''; ?>">Riwayat Pesanan</a></li>
            <li><a href="?page=add_admin" class="<?php echo $page === 'add_admin' ? 'active' : ''; ?>">Tambah Thai Tea Admin</a></li>
            <li><a href="logout_owner.php">Logout</a></li>
            
        </ul>
    </nav>
</header>

<main>
    <?php if ($page === 'add_menu'): ?>
        <!-- Tambah Menu Baru -->
        <section id="add-menu">
            <h2>Tambah Menu Baru</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Nama menu" required>
                <input type="number" name="price" placeholder="Harga " required>
                <input type="text" name="image" placeholder="URL Gambar">
                <button type="submit" name="add_menu">Tambah Menu</button>
            </form>
        </section>
    <?php elseif ($page === 'menu_list'): ?>
        <!-- Daftar Menu -->
        <section id="menu-list">
            <h2>Daftar Menu</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_items as $menuId): ?>
                        <tr>
                            <td><?php echo $menuId['id']; ?></td>
                            <td><?php echo $menuId['name']; ?></td>
                            <td>Rp<?php echo number_format($menuId['price'], 0, ',', '.'); ?></td>
                            <td><img src="<?php echo $menuId['image']; ?>" alt="<?php echo $menuId['name']; ?>" class="img-preview"></td>
                            <td>
                                <div class="actions">
                                    <form method="POST">
                                        <input type="hidden" name="menu_id" value="<?php echo $menuId['id']; ?>">
                                        <button type="submit" name="delete_menu" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">Hapus</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="menu_id" value="<?php echo $menuId['id']; ?>">
                                        <input type="text" name="name" value="<?php echo $menuId['name']; ?>">
                                        <input type="number" name="price" value="<?php echo $menuId['price']; ?>">
                                        <input type="text" name="image" value="<?php echo $menuId['image']; ?>">
                                        <button type="submit" name="edit_menu">Edit</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php elseif ($page === 'booking_list'): ?>
        <section id="booking-list">
            <h2>Daftar Pesanan</h2>
            <table>
                <thead>
                    <tr>
                        <th>Antrian</th>
                        <th>Kode Pesanan</th>
                        <th>Nama Pelanggan</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Jumlah</th>
                        <th>Total Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo $booking['menu_item_id']; ?></td>
                                <td><?php echo $booking['name']; ?></td>
                                <td><?php echo $booking['email']; ?></td>
                                <td><?php echo $booking['phone']; ?></td>
                                <td><?php echo $booking['quantity']; ?></td>
                                <td>Rp<?php echo number_format($booking['total_price'], 0, ',', '.'); ?></td>
                                <td>
                                    <div class="actions">
                                        <!-- Konfirmasi Pemesanan -->
                                        <form method="POST">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="confirm_booking">Konfirmasi</button>
                                        </form>
                                        <!-- Batalkan Pemesanan -->
                                        <form method="POST">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="cancel_booking">Batal</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10">Tidak ada pemesanan saat ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    <?php elseif ($page === 'history_list'): ?>
    <section id="history-list">
        <h2>Riwayat Pemesanan</h2>
        <table>
            <thead>
                <tr>
                    <th>Antrian</th>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Email</th>
                    <th>No. Telepon</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query untuk mengambil data riwayat pemesanan
                $sql = "SELECT h.*, m.name AS menu_name 
                        FROM history h 
                        JOIN menu_items m ON h.menu_item_id = m.id 
                        WHERE TRIM(h.status) != 'complete'";
                $result = $conn->query($sql);

                // Debugging jika query gagal
                if (!$result) {
                    die("Query gagal: " . $conn->error);
                }

                // Tampilkan data jika ada
                if ($result->num_rows > 0):
                    while ($entry = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $entry['id']; ?></td>
                            <td><?php echo $entry['menu_item_id']; ?></td>
                            <td><?php echo $entry['name']; ?></td>
                            <td><?php echo $entry['email']; ?></td>
                            <td><?php echo $entry['phone']; ?></td>
                            <td>Rp<?php echo number_format($entry['total_price'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="6">Tidak ada data riwayat pemesanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
<?php elseif ($page === 'add_admin'): ?>
    <section id="add-admin">
        <h2>Tambah Thai Tea Admin Baru</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_admin">Tambah Thai Tea Admin</button>
        </form>
    </section>
<?php endif; ?>



</main>
</body>
</html>
