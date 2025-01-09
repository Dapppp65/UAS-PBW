<!-- Theme Color: Orange (Thai Tea), White, Light Brown -->
<?php
// Database connection setup
$host = 'localhost';
$user = 'root';
$password = ''; // Ubah sesuai dengan password database Anda
$dbname = 'minuman2';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch menu items data
$sql = "SELECT * FROM menu_items";
$result = $conn->query($sql);
$menuItems = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menuItems[] = $row;
    }
}
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $menuItemId = $_POST['menu_item'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if (!$menuItemId || !$quantity) {
        die("Menu item atau quantity tidak valid.");
    }

    // Ambil harga menu item
    $menuItemQuery = $conn->prepare("SELECT price FROM menu_items WHERE id = ?");
    if ($menuItemQuery === false) {
        die("Query menu item gagal: " . $conn->error);
    }
    $menuItemQuery->bind_param('i', $menuItemId);
    $menuItemQuery->execute();
    $menuItemResult = $menuItemQuery->get_result();
    $menuItemData = $menuItemResult->fetch_assoc();

    if (!$menuItemData) {
        die("Menu item tidak ditemukan.");
    }
    $itemPrice = $menuItemData['price'];

    // Hitung total harga
    $totalPrice = $itemPrice * $quantity;

    // Simpan pesanan
    $insertSql = "INSERT INTO bookings (menu_item_id, name, email, phone, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    if ($stmt === false) {
        die("Query insert gagal: " . $conn->error);
    }
    $stmt->bind_param('isssii', $menuItemId, $name, $email, $phone, $quantity, $totalPrice);

    if ($stmt->execute()) {
        echo "<script>alert('Pemesanan berhasil! Total Harga: Rp." . number_format($totalPrice, 0, ',', '.') . "');</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memproses pemesanan.');</script>";
    }
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyotnyot </title>
    <style>

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #f8f9fa;
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

        .container {
            text-align: center;
            position: relative;
        }

        .image-container {
            width: 100%;
            height: 400px;
            position: relative;
            overflow: hidden;
            
        }

        .background-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
            filter: brightness(80%);
        }

        .text-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #fff;
        }

        .text-overlay h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .text-overlay p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .search-btn {
            background-color: #ffa726;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .search-btn:hover {
            background-color: #fb8c00;
        }

        .filter, .about-container, .contact-container {
            padding: 20px;
            margin: 20px auto;
            max-width: 1200px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .filter form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            align-items: center;
        }

        .filter label, .filter select, .filter button {
            font-size: 1rem;
        }

        .filter select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .filter button {
            padding: 10px 15px;
            background-color: #263238;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .filter button:hover {
            background-color: #455a64;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 200px;
            margin: 20px 0;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.5s;
            width: 50%;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            width: 10%;
            height: 100px;
            object-fit: cover;
        }

        .card header {
            padding: 15px;
            font-size: 1.25rem;
            font-weight: bold;
            color: #ccc;
        }

        .card .content {
            padding: 20px;
            font-size: 0.95rem;
        }

        .card footer {
            padding: 6px;
            text-align: center;
        }

        .card button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .card button:hover {
            background-color: #218838;
        }

        footer {
            background-color: #263238;
            color: #fff;
            text-align: center;
            padding: 15px 0;
        }

        footer p {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .navbar-menu {
                display: none;
                flex-direction: column;
                width: 100%;
                position: absolute;
                top: 50px;
                left: 0;
                background-color: #263238;
                padding: 10px 0;
            }

            .navbar-menu.active {
                display: flex;
            }

            .navbar-toggle {
                display: flex;
                flex-direction: column;
                cursor: pointer;
            }

            .navbar-toggle .bar {
                width: 25px;
                height: 3px;
                margin: 5px;
                background-color: #fff;
            }
        }
        .form-container {
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h3 {
            margin-bottom: 15px;
            font-size: 1.5rem;
            text-align: center;
            color: #263238;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-container label {
            font-size: 1rem;
            font-weight: bold;
        }

        .form-container input, .form-container select, .form-container textarea {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container button {
            padding: 10px;
            font-size: 1rem;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #218838;
        }
        .conten{
            padding-left: 20px;
            padding-right: 20px;
        }
    </style>
    <script>
        function calculateTotalPrice() {
        


    document.getElementById('total_price').textContent = 'Total Harga: Rp' + totalPrice.toLocaleString('id-ID');
}




        // script.js

window.onload = function() {
    document.querySelector('.text-overlay').style.animation = 'fadeIn 2s ease-out';
};

    document.getElementById('navbar-toggle').addEventListener('click', function() {
        const menu = document.querySelector('.navbar-menu');
        menu.classList.toggle('active');
    });



    </script>
</head>
<body>
    <center>
    <header>
        <nav class="navbar">
            <a href="index.php" class="navbar-logo">nyot nyot thaitea</a>
            <ul class="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="#conten">Cari minuman</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            
        </nav>
    </header>

    <div class="container">
        <div class="image-container">
            <img src="picture/background1.png" alt="Background" class="background-image">
            <div class="text-overlay">
                <h1>Selamat Datang di nyot nyot thaitea</h1>
                <p>memesan thaitea dengan mudah!</p>
                <button class="search-btn" onclick="window.location.href='#conten'">Tampilkan menu</button>
            </div>
        </div>
    </div>

    <div class="filter">
        <h3>menu</h3>
        <form method="GET">
            <button type="submit">Tampilkan pesanan</button>
        </form>
    </div>
<section id="conten">
<div class="conten">
<div class="cards">


<?php
// Ambil data dari tabel menu_items di database minuman2
$sql = "SELECT * FROM menu_items";
$result = $conn->query($sql);
$menu_items = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
?>

<div class="menu-list">
    <?php
    foreach ($menu_items as $menu) {
        echo "<article class='card'>";
        echo "<header><h2>{$menu['name']}</h2></header>";
        echo "<img src='{$menu['image']}' alt='{$menu['name']}'>";
        echo "<div class='content'>";
        echo "<p>Rp." . number_format($menu['price'], 0, ',', '.') . "</p>";
        echo "</div><footer>";

        // Tombol Pesan selalu ditampilkan
        echo "<form method='GET' action='#form-container'><button type='submit' name='menu_id' value='{$menu['id']}'>Pesan</button></form>";

        echo "</footer></article>";
    }
    ?>
</div>

<?php
if (!empty($_GET['menu_id'])) {
    $menu_id = $_GET['menu_id'];
    $selectedMenu = array_filter($menu_items, function($menu) use ($menu_id) {
        return $menu['id'] == $menu_id;
    });
    $selectedMenu = reset($selectedMenu);
    if ($selectedMenu) {
?>
<div id="form-container" class="form-container">
    <h3>Form Pemesanan</h3>
    <form method="POST" action="">
    <input type="hidden" name="menu_item" value="1"> 
    <input type="number" name="quantity" min="1" value="1" required>
    <input type="text" name="name" placeholder="Nama Anda" required>
    <input type="email" name="email" placeholder="Email Anda" required>
    <input type="tel" name="phone" placeholder="Nomor Telepon Anda" required>
    <button type="submit">Pesan</button>
</form>
    </form>
</div>
<?php
    }
}
?>



</section>
<section id="about">
    <div class="about-container">
        <div class="about-content">
            <h1>About Us</h1>
            <p>Nikmati Kelezatan Thai Tea Terbaik! Rasakan kesegaran dan cita rasa autentik Thai Tea yang kaya akan rempah dan kelembutan.</p>
            <p>dari bahan berkualitas, setiap tegukan menghadirkan pengalaman istimewa</p>
            <p> Segera pesan dan temukan kenikmatan Thai Tea favorit Anda!</p>
        </div>
    </div></section>     
    
<section id="contact">
<div class="contact-container">
        <div class="contact-content">
            <h1>Contact Us</h1>
            <p>Jika Anda memiliki pertanyaan atau memerlukan bantuan lebih lanjut, jangan ragu untuk menghubungi kami melalui informasi kontak di bawah ini atau dengan mengisi formulir kontak..</p>

            <h3>Our Contact Information</h3>
            <p><strong>Email:</strong> support@nyotnyotthaitea.com</p>
            <p><strong>Phone:</strong> +62 812 2528 9472</p>

        </div>
    </div>
</section>
    </center>
    <footer>
        <p>&copy; 2024 Kelompok 2. All rights reserved.</p>
    </footer>
</body>
</html>
